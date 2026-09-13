<?php

namespace App\Http\Controllers\Admin;

use App\Models\Server;
use App\Models\GithubSsh;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class AdminServerTaskController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
        ];
    }

    public function index()
    {
        $servers = Server::where('type', 'ssh')->where('status', true)->get();
        $githubSsh = GithubSsh::where('status', true)->get();

        return view('admin.server-task.index', compact('servers', 'githubSsh'));
    }

    public function execute(Request $request)
    {
        $request->validate([
            'server_id' => 'required|exists:servers,id',
            'task_type' => 'required|in:clone,install,chmod,chown,custom',
        ]);

        $server = Server::findOrFail($request->server_id);
        $log = [];

        try {
            $ssh = $this->createSshConnection($server);
            $log[] = "[SSH] Connected to {$server->username}@{$server->host}:{$server->port}";

            $taskType = $request->task_type;

            if ($taskType === 'clone') {
                $repoUrl = $request->input('repo_url');
                $destination = $request->input('destination', '/var/www');
                $githubSshId = $request->input('github_ssh_id');

                if (empty($repoUrl)) {
                    return back()->with('error', 'Repository URL is required!');
                }

                // If GitHub SSH is selected, modify URL to use PAT
                if ($githubSshId) {
                    $githubSsh = GithubSsh::findOrFail($githubSshId);
                    $repoUrl = $this->injectPatToUrl($repoUrl, $githubSsh->username, $githubSsh->pat);
                    $log[] = "[SSH] Using GitHub PAT for user: {$githubSsh->username}";
                }

                $cmd = "cd {$destination} && git clone {$repoUrl}";
                $log[] = "[SSH] Running: {$cmd}";
                $result = $ssh->exec($cmd);
                $log[] = "[SSH] Output: " . trim($result);

            } elseif ($taskType === 'install') {
                $packageManager = $request->input('package_manager');
                $packages = $request->input('packages');
                $workingDir = $request->input('working_dir', '/var/www');

                if (empty($packages)) {
                    return back()->with('error', 'Packages/library is required!');
                }

                $commands = [
                    'apt' => "cd {$workingDir} && sudo apt update && sudo apt install -y {$packages}",
                    'npm' => "cd {$workingDir} && npm install {$packages}",
                    'composer' => "cd {$workingDir} && composer require {$packages}",
                    'pip' => "cd {$workingDir} && pip install {$packages}",
                    'yarn' => "cd {$workingDir} && yarn add {$packages}",
                    'pnpm' => "cd {$workingDir} && pnpm add {$packages}",
                ];

                $cmd = $commands[$packageManager] ?? null;
                if (!$cmd) {
                    return back()->with('error', 'Invalid package manager!');
                }

                $log[] = "[SSH] Running: {$cmd}";
                $result = $ssh->exec($cmd);
                $log[] = "[SSH] Output: " . trim($result);

            } elseif ($taskType === 'chmod') {
                $path = $request->input('chmod_path');
                $permissions = $request->input('chmod_permissions');
                $recursive = $request->input('chmod_recursive', '0');

                if (empty($path) || empty($permissions)) {
                    return back()->with('error', 'Path and permissions are required!');
                }

                $recursiveFlag = $recursive ? '-R ' : '';
                $cmd = "sudo chmod {$recursiveFlag}{$permissions} {$path}";
                $log[] = "[SSH] Running: {$cmd}";
                $result = $ssh->exec($cmd);
                $log[] = "[SSH] Output: " . trim($result);

            } elseif ($taskType === 'chown') {
                $path = $request->input('chown_path');
                $owner = $request->input('chown_owner');
                $group = $request->input('chown_group');
                $recursive = $request->input('chown_recursive', '0');

                if (empty($path) || empty($owner)) {
                    return back()->with('error', 'Path and owner are required!');
                }

                $ownerStr = $group ? "{$owner}:{$group}" : $owner;
                $recursiveFlag = $recursive ? '-R ' : '';
                $cmd = "sudo chown {$recursiveFlag}{$ownerStr} {$path}";
                $log[] = "[SSH] Running: {$cmd}";
                $result = $ssh->exec($cmd);
                $log[] = "[SSH] Output: " . trim($result);

            } elseif ($taskType === 'custom') {
                $command = $request->input('command');
                $workingDir = $request->input('working_dir', '/var/www');

                if (empty($command)) {
                    return back()->with('error', 'Command is required!');
                }

                $cmd = "cd {$workingDir} && {$command}";
                $log[] = "[SSH] Running: {$cmd}";
                $result = $ssh->exec($cmd);
                $log[] = "[SSH] Output: " . trim($result);
            }

            $log[] = "[SSH] Task completed successfully!";
            return back()->with('success', implode('<br>', $log));

        } catch (\Throwable $e) {
            $log[] = "[Error] " . $e->getMessage();
            return back()->with('error', implode('<br>', $log));
        }
    }

    protected function injectPatToUrl(string $url, string $username, string $pat): string
    {
        // Convert https://github.com/user/repo.git to https://username:pat@github.com/user/repo.git
        $url = preg_replace('/^https:\/\/github\.com\//', "https://{$username}:{$pat}@github.com/", $url);
        $url = preg_replace('/^https:\/\/github\.com\//', "https://{$username}:{$pat}@github.com/", $url);
        return $url;
    }

    protected function createSshConnection(Server $server)
    {
        $host = $server->host;
        $port = $server->port ?: 22;
        $username = $server->username ?: 'root';

        if (empty($host)) {
            throw new \Exception("SSH Host IP/Hostname is required.");
        }

        $ssh = new \phpseclib3\Net\SSH2($host, $port);
        $ssh->setTimeout(60);

        if ($server->auth_type === 'key') {
            $privateKey = $server->private_key;
            if (empty($privateKey)) {
                throw new \Exception("SSH Private Key is required for key authentication.");
            }
            $key = \phpseclib3\Crypt\PublicKeyLoader::load($privateKey, $server->password ?: false);
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
}
