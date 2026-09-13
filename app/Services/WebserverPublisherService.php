<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\Subdomain;
use App\Models\Server;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;

class WebserverPublisherService
{
    protected WebserverGeneratorService $generator;

    public function __construct(WebserverGeneratorService $generator)
    {
        $this->generator = $generator;
    }

    public function getGenerator(): WebserverGeneratorService
    {
        return $this->generator;
    }

    /**
     * Publish webserver configuration for Domain or Subdomain.
     */
    public function publish(Domain|Subdomain $item, ?Server $server = null): array
    {
        $targetServer = $server ?? $item->server;

        // Fallback to default local server if not specified
        if (!$targetServer) {
            $targetServer = Server::where('type', 'local')->first() ?? new Server([
                'name' => 'Local Server Default',
                'type' => 'local',
            ]);
        }

        if ($targetServer->type === 'ssh') {
            $result = $this->publishRemoteSSH($item, $targetServer);
        } else {
            $result = $this->publishLocal($item, $targetServer);
        }

        // Update publish status on model
        $item->update([
            'server_id' => $targetServer->id ?? $item->server_id,
            'published_at' => now(),
            'publish_status' => $result['success'] ? 'published' : 'failed',
            'publish_log' => $result['log'],
        ]);

        return $result;
    }

    /**
     * Publish locally on this server.
     */
    public function publishLocal(Domain|Subdomain $item, Server $server): array
    {
        $filename = $this->generator->getFilename($item);
        $configContent = $this->generator->generate($item, $item->webserver_type);

        $log = [];
        $log[] = "[Local Publish] Starting configuration publishing for file: {$filename}";

        $configPath = $this->getConfigPath($server, $item->webserver_type);
        $symlinkPath = $this->getSymlinkPath($server, $item->webserver_type);
        $storageBackupPath = storage_path('app/webservers');
        if (!File::exists($storageBackupPath)) {
            File::makeDirectory($storageBackupPath, 0755, true);
        }

        // Always save a copy to storage/app/webservers/
        $storageFile = $storageBackupPath . '/' . $filename;
        File::put($storageFile, $configContent);
        $log[] = "[Local Publish] Saved copy to storage: {$storageFile}";

        $targetFile = $configPath . '/' . $filename;
        $writtenToSystem = false;

        try {
            if (!File::exists($configPath)) {
                @File::makeDirectory($configPath, 0755, true);
            }
            File::put($targetFile, $configContent);
            $log[] = "[Local Publish] Successfully wrote config to {$targetFile}";
            $writtenToSystem = true;
        } catch (\Throwable $e) {
            $log[] = "[Local Publish Notice] Could not write directly to {$targetFile}: " . $e->getMessage();
            $log[] = "[Local Publish] Attempting sudo tee to system path...";

            $process = Process::run("echo " . escapeshellarg($configContent) . " | sudo tee " . escapeshellarg($targetFile));
            if ($process->successful()) {
                $log[] = "[Local Publish] Successfully wrote via sudo tee to {$targetFile}";
                $writtenToSystem = true;
            } else {
                $log[] = "[Local Publish Error] Failed to write file: " . $process->errorOutput();
            }
        }

        // Symlink creation if Nginx/Apache sites-enabled is used
        if ($symlinkPath && $writtenToSystem) {
            $symlinkTarget = $symlinkPath . '/' . $filename;
            if (!File::exists($symlinkPath)) {
                @File::makeDirectory($symlinkPath, 0755, true);
            }
            try {
                if (File::exists($symlinkTarget) || is_link($symlinkTarget)) {
                    @unlink($symlinkTarget);
                }
                @symlink($targetFile, $symlinkTarget);
                $log[] = "[Local Publish] Created symlink from {$targetFile} to {$symlinkTarget}";
            } catch (\Throwable $e) {
                $symProcess = Process::run("sudo ln -sf " . escapeshellarg($targetFile) . " " . escapeshellarg($symlinkTarget));
                if ($symProcess->successful()) {
                    $log[] = "[Local Publish] Created symlink via sudo ln to {$symlinkTarget}";
                }
            }
        }

        // Run config test and reload
        $webserverType = $item->webserver_type ?? 'nginx';
        $testCmd = ($webserverType === 'apache') ? 'sudo apache2ctl configtest' : 'sudo nginx -t';
        $reloadCmd = ($webserverType === 'apache') ? 'sudo systemctl reload apache2' : 'sudo systemctl reload nginx';

        $log[] = "[Local Publish] Testing webserver configuration: {$testCmd}";
        $testProc = Process::run($testCmd);
        if ($testProc->successful()) {
            $log[] = "[Local Publish] Config test OK: " . trim($testProc->output() . " " . $testProc->errorOutput());

            $log[] = "[Local Publish] Reloading webserver: {$reloadCmd}";
            $reloadProc = Process::run($reloadCmd);
            if ($reloadProc->successful()) {
                $log[] = "[Local Publish] Webserver reloaded successfully.";
                return [
                    'success' => true,
                    'message' => 'Configuration generated and published successfully on local server!',
                    'log' => implode("\n", $log),
                ];
            } else {
                $log[] = "[Local Publish Warning] Reload failed: " . $reloadProc->errorOutput();
            }
        } else {
            $log[] = "[Local Publish Warning] Config test failed or skipped: " . $testProc->errorOutput();
        }

        return [
            'success' => $writtenToSystem,
            'message' => $writtenToSystem
                ? 'Configuration file generated and written to system path (Reload manually if needed).'
                : 'Configuration generated and stored in storage/app/webservers/' . $filename,
            'log' => implode("\n", $log),
        ];
    }

    /**
     * Publish remotely via SSH connection to target server.
     */
    public function publishRemoteSSH(Domain|Subdomain $item, Server $server): array
    {
        $log = [];
        $log[] = "[SSH Publish] Initiating SSH connection to {$server->username}@{$server->host}:{$server->port}";

        try {
            $ssh = $this->createSshConnection($server);
            if (!$ssh) {
                throw new \Exception("Could not establish SSH connection to {$server->host}:{$server->port}");
            }

            $log[] = "[SSH Publish] Connected to SSH server successfully.";

            $filename = $this->generator->getFilename($item);
            $configContent = $this->generator->generate($item, $item->webserver_type);

            $configPath = $this->getConfigPath($server, $item->webserver_type);
            $symlinkPath = $this->getSymlinkPath($server, $item->webserver_type);

            $remoteTarget = "{$configPath}/{$filename}";
            $remoteSymlink = $symlinkPath ? "{$symlinkPath}/{$filename}" : '';

            // Ensure directory exists
            $log[] = "[SSH Publish] Ensuring remote directory exists: {$configPath}";
            $ssh->exec("sudo mkdir -p " . escapeshellarg($configPath));

            // Write remote file
            $log[] = "[SSH Publish] Writing configuration to remote file: {$remoteTarget}";
            $writeCmd = "echo " . escapeshellarg($configContent) . " | sudo tee " . escapeshellarg($remoteTarget);
            $writeOutput = $ssh->exec($writeCmd);
            $log[] = "[SSH Publish] Write result: " . trim($writeOutput);

            // Create symlink if needed
            if (!empty($remoteSymlink)) {
                $log[] = "[SSH Publish] Creating remote symlink to: {$remoteSymlink}";
                $ssh->exec("sudo mkdir -p " . escapeshellarg($symlinkPath));
                $symCmd = "sudo ln -sf " . escapeshellarg($remoteTarget) . " " . escapeshellarg($remoteSymlink);
                $symOutput = $ssh->exec($symCmd);
                if (!empty($symOutput)) {
                    $log[] = "[SSH Publish] Symlink result: " . trim($symOutput);
                }
            }

            // Test configuration
            $webserverType = $item->webserver_type ?? 'nginx';
            $testCmd = ($webserverType === 'apache') ? 'sudo apache2ctl configtest' : 'sudo nginx -t';
            $log[] = "[SSH Publish] Running remote config test: {$testCmd}";
            $testResult = $ssh->exec($testCmd);
            $log[] = "[SSH Publish] Test output: " . trim($testResult);

            // Reload command
            $reloadCmd = ($webserverType === 'apache') ? 'sudo systemctl reload apache2' : 'sudo systemctl reload nginx';
            $log[] = "[SSH Publish] Executing remote webserver reload: {$reloadCmd}";
            $reloadResult = $ssh->exec($reloadCmd);
            $log[] = "[SSH Publish] Reload output: " . trim($reloadResult);

            return [
                'success' => true,
                'message' => "Successfully published wildcard/domain configuration to remote SSH server ({$server->name})!",
                'log' => implode("\n", $log),
            ];

        } catch (\Throwable $e) {
            $log[] = "[SSH Publish Error] " . $e->getMessage();
            return [
                'success' => false,
                'message' => 'SSH Publication failed: ' . $e->getMessage(),
                'log' => implode("\n", $log),
            ];
        }
    }

    /**
     * Test SSH Connection to server.
     */
    public function testSshConnection(Server $server): array
    {
        if ($server->type !== 'ssh') {
            return [
                'success' => true,
                'message' => 'Server is configured as local server.',
                'log' => 'Local server connection OK.',
            ];
        }

        try {
            $ssh = $this->createSshConnection($server);
            if (!$ssh) {
                return [
                    'success' => false,
                    'message' => 'Failed to connect to SSH server.',
                    'log' => 'SSH auth or connection error.',
                ];
            }

            $output = $ssh->exec('uname -a && uptime');
            return [
                'success' => true,
                'message' => "SSH Connection successful to {$server->host}!",
                'log' => "Connected to {$server->username}@{$server->host}:{$server->port}\n\nServer Info:\n" . trim($output),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'SSH Connection failed: ' . $e->getMessage(),
                'log' => $e->getTraceAsString(),
            ];
        }
    }

    /**
     * Helper to instantiate and authenticate phpseclib3 SSH2 object.
     */
    protected function createSshConnection(Server $server): ?SSH2
    {
        $host = $server->host;
        $port = $server->port ?: 22;
        $username = $server->username ?: 'root';

        if (empty($host)) {
            throw new \Exception("SSH Host IP/Hostname is required.");
        }

        $ssh = new SSH2($host, $port);
        $ssh->setTimeout(15);

        if ($server->auth_type === 'key') {
            $privateKey = $server->private_key;
            if (empty($privateKey)) {
                throw new \Exception("SSH Private Key is required for key authentication.");
            }

            $key = PublicKeyLoader::load($privateKey, $server->password ?: false);
            if (!$ssh->login($username, $key)) {
                throw new \Exception("SSH key authentication failed for {$username}@{$host}");
            }
        } else {
            $password = $server->password;
            if (!$ssh->login($username, $password)) {
                throw new \Exception("SSH password authentication failed for {$username}@{$host}");
            }
        }

        return $ssh;
    }

    /**
     * Unpublish - remove symlink from sites-enabled (disable config).
     */
    public function unpublish(Domain|Subdomain $item, ?Server $server = null): array
    {
        $targetServer = $server ?? $item->server;

        if (!$targetServer) {
            $targetServer = Server::where('type', 'local')->first();
        }

        if (!$targetServer) {
            return ['success' => false, 'message' => 'No server configured.', 'log' => ''];
        }

        $filename = $this->generator->getFilename($item);
        $log = [];

        if ($targetServer->type === 'ssh') {
            $log[] = "[SSH Unpublish] Connecting to {$targetServer->username}@{$targetServer->host}";
            try {
                $ssh = $this->createSshConnection($targetServer);
                $log[] = "[SSH Unpublish] Connected.";

                $symlinkPath = $this->getSymlinkPath($targetServer, $item->webserver_type);
                $remoteSymlink = "{$symlinkPath}/{$filename}";

                $log[] = "[SSH Unpublish] Removing symlink: {$remoteSymlink}";
                $ssh->exec("sudo rm -f " . escapeshellarg($remoteSymlink));

                $webserverType = $item->webserver_type ?? 'nginx';
                $reloadCmd = ($webserverType === 'apache') ? 'sudo systemctl reload apache2' : 'sudo systemctl reload nginx';
                $log[] = "[SSH Unpublish] Reloading: {$reloadCmd}";
                $ssh->exec($reloadCmd);

                $item->update(['publish_status' => 'pending', 'publish_log' => implode("\n", $log)]);

                return ['success' => true, 'message' => "Unpublished on {$targetServer->name}!", 'log' => implode("\n", $log)];
            } catch (\Throwable $e) {
                $log[] = "[SSH Unpublish Error] " . $e->getMessage();
                return ['success' => false, 'message' => 'Unpublish failed: ' . $e->getMessage(), 'log' => implode("\n", $log)];
            }
        } else {
            $log[] = "[Local Unpublish] Removing symlink for: {$filename}";
            $symlinkPath = $this->getSymlinkPath($targetServer, $item->webserver_type);
            $symlinkTarget = "{$symlinkPath}/{$filename}";

            if (!empty($symlinkTarget)) {
                try {
                    if (is_link($symlinkTarget)) {
                        @unlink($symlinkTarget);
                        $log[] = "[Local Unpublish] Removed symlink: {$symlinkTarget}";
                    }
                } catch (\Throwable $e) {
                    Process::run("sudo rm -f " . escapeshellarg($symlinkTarget));
                    $log[] = "[Local Unpublish] Removed symlink via sudo.";
                }
            }

            $webserverType = $item->webserver_type ?? 'nginx';
            $reloadCmd = ($webserverType === 'apache') ? 'sudo systemctl reload apache2' : 'sudo systemctl reload nginx';
            $log[] = "[Local Unpublish] Reloading: {$reloadCmd}";
            Process::run($reloadCmd);

            $item->update(['publish_status' => 'pending', 'publish_log' => implode("\n", $log)]);

            return ['success' => true, 'message' => 'Unpublished successfully!', 'log' => implode("\n", $log)];
        }
    }

    /**
     * Delete config file from server (available + enabled + storage).
     */
    public function deleteConfig(Domain|Subdomain $item, ?Server $server = null): array
    {
        $targetServer = $server ?? $item->server;

        if (!$targetServer) {
            $targetServer = Server::where('type', 'local')->first();
        }

        if (!$targetServer) {
            return ['success' => true, 'message' => 'No server configured, skipped.', 'log' => ''];
        }

        $filename = $this->generator->getFilename($item);
        $log = [];

        $webserverType = $item->webserver_type ?? 'nginx';

        if ($targetServer->type === 'ssh') {
            $log[] = "[SSH Delete] Connecting to {$targetServer->username}@{$targetServer->host}";
            try {
                $ssh = $this->createSshConnection($targetServer);
                $log[] = "[SSH Delete] Connected.";

                $configPath = $this->getConfigPath($targetServer, $webserverType);
                $symlinkPath = $this->getSymlinkPath($targetServer, $webserverType);

                $remoteConfig = "{$configPath}/{$filename}";
                $remoteSymlink = $symlinkPath ? "{$symlinkPath}/{$filename}" : '';

                $log[] = "[SSH Delete] Removing symlink: {$remoteSymlink}";
                $ssh->exec("sudo rm -f " . escapeshellarg($remoteSymlink));

                $log[] = "[SSH Delete] Removing config: {$remoteConfig}";
                $ssh->exec("sudo rm -f " . escapeshellarg($remoteConfig));

                $storageFile = storage_path('app/webservers/' . $filename);
                if (file_exists($storageFile)) {
                    @unlink($storageFile);
                    $log[] = "[SSH Delete] Removed storage copy.";
                }

                $reloadCmd = ($webserverType === 'apache') ? 'sudo systemctl reload apache2' : 'sudo systemctl reload nginx';
                $log[] = "[SSH Delete] Reloading: {$reloadCmd}";
                $ssh->exec($reloadCmd);

                return ['success' => true, 'message' => "Config deleted on {$targetServer->name}!", 'log' => implode("\n", $log)];
            } catch (\Throwable $e) {
                $log[] = "[SSH Delete Error] " . $e->getMessage();
                return ['success' => false, 'message' => 'Delete failed: ' . $e->getMessage(), 'log' => implode("\n", $log)];
            }
        } else {
            $configPath = $this->getConfigPath($targetServer, $webserverType);
            $symlinkPath = $this->getSymlinkPath($targetServer, $webserverType);

            $localConfig = $configPath . '/' . $filename;
            $localSymlink = $symlinkPath ? $symlinkPath . '/' . $filename : '';

            try {
                if (file_exists($localConfig)) {
                    @unlink($localConfig);
                    $log[] = "[Local Delete] Removed config: {$localConfig}";
                }
            } catch (\Throwable $e) {
                Process::run("sudo rm -f " . escapeshellarg($localConfig));
            }

            try {
                if (!empty($localSymlink) && (is_link($localSymlink) || file_exists($localSymlink))) {
                    @unlink($localSymlink);
                    $log[] = "[Local Delete] Removed symlink: {$localSymlink}";
                }
            } catch (\Throwable $e) {
                Process::run("sudo rm -f " . escapeshellarg($localSymlink));
            }

            $storageFile = storage_path('app/webservers/' . $filename);
            if (file_exists($storageFile)) {
                @unlink($storageFile);
                $log[] = "[Local Delete] Removed storage copy.";
            }

            $reloadCmd = ($webserverType === 'apache') ? 'sudo systemctl reload apache2' : 'sudo systemctl reload nginx';
            $log[] = "[Local Delete] Reloading: {$reloadCmd}";
            Process::run($reloadCmd);

            return ['success' => true, 'message' => 'Config deleted successfully!', 'log' => implode("\n", $log)];
        }
    }

    /**
     * Cleanup old config files when domain/subdomain name changes (rename logic).
     * Removes old config + symlink if filename differs from current.
     */
    public function cleanupOldConfig(Domain|Subdomain $item, string $oldFilename, ?Server $server = null, ?string $oldWebserverType = null): array
    {
        $newFilename = $this->generator->getFilename($item);
        $newWebserverType = $item->webserver_type ?? 'nginx';

        // If nothing changed, nothing to cleanup
        if ($oldFilename === $newFilename && $oldWebserverType === $newWebserverType) {
            return ['success' => true, 'message' => 'Filename and webserver type unchanged, no cleanup needed.', 'log' => ''];
        }

        $targetServer = $server ?? $item->server;

        if (!$targetServer) {
            $targetServer = Server::where('type', 'local')->first();
        }

        if (!$targetServer) {
            return ['success' => true, 'message' => 'No server configured, skipped.', 'log' => ''];
        }

        $log = [];

        // Determine old config path based on old webserver type
        $oldConfigPath = $this->getConfigPath($targetServer, $oldWebserverType);
        $oldSymlinkPath = $this->getSymlinkPath($targetServer, $oldWebserverType);

        if ($oldFilename !== $newFilename) {
            $log[] = "[Cleanup] Filename changed: {$oldFilename} -> {$newFilename}";
        }
        if ($oldWebserverType && $oldWebserverType !== $newWebserverType) {
            $log[] = "[Cleanup] Webserver type changed: {$oldWebserverType} -> {$newWebserverType}";
            $log[] = "[Cleanup] Old config path: {$oldConfigPath}";
        }

        if ($targetServer->type === 'ssh') {
            try {
                $ssh = $this->createSshConnection($targetServer);

                $oldConfig = "{$oldConfigPath}/{$oldFilename}";
                $oldSymlink = $oldSymlinkPath ? "{$oldSymlinkPath}/{$oldFilename}" : '';

                if (!empty($oldSymlink)) {
                    $ssh->exec("sudo rm -f " . escapeshellarg($oldSymlink));
                    $log[] = "[Cleanup] Removed old symlink: {$oldSymlink}";
                }

                $ssh->exec("sudo rm -f " . escapeshellarg($oldConfig));
                $log[] = "[Cleanup] Removed old config: {$oldConfig}";

                $oldStorage = storage_path('app/webservers/' . $oldFilename);
                if (file_exists($oldStorage)) {
                    @unlink($oldStorage);
                    $log[] = "[Cleanup] Removed old storage copy.";
                }

                return ['success' => true, 'message' => 'Old config cleaned up.', 'log' => implode("\n", $log)];
            } catch (\Throwable $e) {
                $log[] = "[Cleanup Error] " . $e->getMessage();
                return ['success' => false, 'message' => 'Cleanup failed: ' . $e->getMessage(), 'log' => implode("\n", $log)];
            }
        } else {
            $oldConfig = $oldConfigPath . '/' . $oldFilename;
            $oldSymlink = $oldSymlinkPath ? $oldSymlinkPath . '/' . $oldFilename : '';

            try {
                if (!empty($oldSymlink) && (is_link($oldSymlink) || file_exists($oldSymlink))) {
                    @unlink($oldSymlink);
                    $log[] = "[Cleanup] Removed old symlink: {$oldSymlink}";
                }
            } catch (\Throwable $e) {
                Process::run("sudo rm -f " . escapeshellarg($oldSymlink));
            }

            try {
                if (file_exists($oldConfig)) {
                    @unlink($oldConfig);
                    $log[] = "[Cleanup] Removed old config: {$oldConfig}";
                }
            } catch (\Throwable $e) {
                Process::run("sudo rm -f " . escapeshellarg($oldConfig));
            }

            $oldStorage = storage_path('app/webservers/' . $oldFilename);
            if (file_exists($oldStorage)) {
                @unlink($oldStorage);
                $log[] = "[Cleanup] Removed old storage copy.";
            }

            return ['success' => true, 'message' => 'Old config cleaned up.', 'log' => implode("\n", $log)];
        }
    }

    /**
     * Get config path based on webserver type.
     */
    protected function getConfigPath(Server $server, ?string $webserverType): string
    {
        $webserverType = $webserverType ?? 'nginx';

        if ($webserverType === 'apache') {
            return '/etc/apache2/sites-available';
        }

        return '/etc/nginx/sites-available';
    }

    /**
     * Get symlink path based on webserver type.
     */
    protected function getSymlinkPath(Server $server, ?string $webserverType): string
    {
        $webserverType = $webserverType ?? 'nginx';

        if ($webserverType === 'apache') {
            return '/etc/apache2/sites-enabled';
        }

        return '/etc/nginx/sites-enabled';
    }
        }

        // If server path is for apache, use nginx default
        if ($serverPath && str_contains($serverPath, 'apache')) {
            return '/etc/nginx/sites-enabled';
        }

        return rtrim($serverPath ?: '/etc/nginx/sites-enabled', '/\\');
    }
}
