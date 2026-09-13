<?php

namespace App\Http\Controllers\Admin;

use App\Models\Server;
use App\Http\Controllers\Controller;
use App\Http\Requests\TableRequest;
use App\Http\Requests\ServerRequest;
use App\Services\WebserverPublisherService;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Process;

class AdminServerController extends Controller implements HasMiddleware
{
    protected $title = "Server";
    protected $permissionName = 'server';

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view:server', only: ['index']),
            new Middleware('permission:create:server', only: ['store']),
            new Middleware('permission:edit:server', only: ['update', 'toggleStatus', 'testConnection', 'reloadNginx', 'restartNginx']),
            new Middleware('permission:delete:server', only: ['destroy']),
        ];
    }

    public function index(TableRequest $request)
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('perPage', 10);
        $validPerPage = in_array($perPage, [10, 50, 100]) ? $perPage : 10;

        if ($search) {
            $servers = Server::withTrashed()
                ->where('name', 'like', "%{$search}%")
                ->orWhere('host', 'like', "%{$search}%")
                ->orWhere('type', 'like', "%{$search}%")
                ->paginate($validPerPage);
        } else {
            $servers = Server::withTrashed()->paginate($validPerPage);
        }

        $permission = $this->permissionName;

        return view('admin.server.index', compact('servers', 'search', 'perPage', 'permission'));
    }

    public function store(ServerRequest $request)
    {
        $data = $request->validated();
        if (empty($data['password'])) {
            unset($data['password']);
        }
        if (empty($data['private_key'])) {
            unset($data['private_key']);
        }

        $server = Server::create($data);

        if ($server) {
            return back()->with('success', 'Successfully created server!');
        }

        return back()->with('error', 'Failed to create server!');
    }

    public function update(ServerRequest $request, string $id)
    {
        $server = Server::findOrFail($id);
        $data = $request->validated();
        
        // Retain existing password/key if left blank in form
        if (empty($data['password'])) {
            unset($data['password']);
        }
        if (empty($data['private_key'])) {
            unset($data['private_key']);
        }

        $server->update($data);

        return back()->with('success', 'Successfully updated server!');
    }

    public function toggleStatus(string $id)
    {
        $server = Server::findOrFail($id);
        $server->update([
            'status' => !$server->status,
        ]);

        return back()->with('success', 'Successfully updated server status!');
    }

    public function testConnection(string $id, WebserverPublisherService $publisher)
    {
        $server = Server::findOrFail($id);
        $res = $publisher->testSshConnection($server);

        if ($res['success']) {
            return back()->with('success', $res['message'] . "\n" . $res['log']);
        }

        return back()->with('error', $res['message'] . "\n" . $res['log']);
    }

    public function reloadNginx(string $id)
    {
        $server = Server::findOrFail($id);
        $log = [];

        try {
            if ($server->type === 'ssh') {
                $log[] = "[SSH Reload] Connecting to {$server->username}@{$server->host}:{$server->port}";
                $ssh = $this->createSshConnection($server);
                if (!$ssh) {
                    throw new \Exception("Could not establish SSH connection.");
                }
                $log[] = "[SSH Reload] Connected successfully.";

                $testCmd = 'sudo nginx -t';
                $log[] = "[SSH Reload] Running: {$testCmd}";
                $testResult = $ssh->exec($testCmd);
                $log[] = "[SSH Reload] Test output: " . trim($testResult);

                $reloadCmd = $server->reload_command ?: 'sudo systemctl reload nginx';
                $log[] = "[SSH Reload] Running: {$reloadCmd}";
                $reloadResult = $ssh->exec($reloadCmd);
                $log[] = "[SSH Reload] Reload output: " . trim($reloadResult);

                return response()->json([
                    'success' => true,
                    'message' => "Nginx reloaded on {$server->name}!",
                    'log' => implode("\n", $log),
                ]);
            } else {
                $log[] = "[Local Reload] Running: sudo nginx -t";
                $testProc = Process::run('sudo nginx -t');
                $log[] = "[Local Reload] Test output: " . trim($testProc->output() . ' ' . $testProc->errorOutput());

                $reloadCmd = $server->reload_command ?: 'sudo systemctl reload nginx';
                $log[] = "[Local Reload] Running: {$reloadCmd}";
                $reloadProc = Process::run($reloadCmd);
                $log[] = "[Local Reload] Reload output: " . trim($reloadProc->output() . ' ' . $reloadProc->errorOutput());

                $success = $reloadProc->successful();
                return response()->json([
                    'success' => $success,
                    'message' => $success ? "Nginx reloaded successfully!" : "Nginx reload failed!",
                    'log' => implode("\n", $log),
                ]);
            }
        } catch (\Throwable $e) {
            $log[] = "[Error] " . $e->getMessage();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reload nginx!',
                'log' => implode("\n", $log),
            ], 500);
        }
    }

    public function restartNginx(string $id)
    {
        $server = Server::findOrFail($id);
        $log = [];

        try {
            if ($server->type === 'ssh') {
                $log[] = "[SSH Restart] Connecting to {$server->username}@{$server->host}:{$server->port}";
                $ssh = $this->createSshConnection($server);
                if (!$ssh) {
                    throw new \Exception("Could not establish SSH connection.");
                }
                $log[] = "[SSH Restart] Connected successfully.";

                $testCmd = 'sudo nginx -t';
                $log[] = "[SSH Restart] Running: {$testCmd}";
                $testResult = $ssh->exec($testCmd);
                $log[] = "[SSH Restart] Test output: " . trim($testResult);

                $restartCmd = str_replace('reload', 'restart', $server->reload_command ?: 'sudo systemctl reload nginx');
                $log[] = "[SSH Restart] Running: {$restartCmd}";
                $restartResult = $ssh->exec($restartCmd);
                $log[] = "[SSH Restart] Restart output: " . trim($restartResult);

                return response()->json([
                    'success' => true,
                    'message' => "Nginx restarted on {$server->name}!",
                    'log' => implode("\n", $log),
                ]);
            } else {
                $log[] = "[Local Restart] Running: sudo nginx -t";
                $testProc = Process::run('sudo nginx -t');
                $log[] = "[Local Restart] Test output: " . trim($testProc->output() . ' ' . $testProc->errorOutput());

                $restartCmd = str_replace('reload', 'restart', $server->reload_command ?: 'sudo systemctl reload nginx');
                $bgCmd = 'nohup bash -c "sleep 2 && ' . $restartCmd . '" > /dev/null 2>&1 & echo $!';
                $log[] = "[Local Restart] Running in background (2s delay): {$restartCmd}";
                $restartProc = Process::run($bgCmd);
                $log[] = "[Local Restart] Background PID: " . trim($restartProc->output());
                $log[] = "[Local Restart] Nginx will restart in ~2 seconds.";

                return response()->json([
                    'success' => true,
                    'message' => "Nginx restart initiated! Server will restart in ~2 seconds.",
                    'log' => implode("\n", $log),
                ]);
            }
        } catch (\Throwable $e) {
            $log[] = "[Error] " . $e->getMessage();
            return response()->json([
                'success' => false,
                'message' => 'Failed to restart nginx!',
                'log' => implode("\n", $log),
            ], 500);
        }
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
        $ssh->setTimeout(15);

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

    public function destroy(string $id)
    {
        Server::findOrFail($id)->forceDelete();
        return back()->with('success', 'Successfully deleted server!');
    }
}
