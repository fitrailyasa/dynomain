<?php

namespace App\Http\Controllers\Admin;

use App\Models\Server;
use App\Http\Controllers\Controller;
use App\Http\Requests\TableRequest;
use App\Http\Requests\ServerRequest;
use App\Services\WebserverPublisherService;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class AdminServerController extends Controller implements HasMiddleware
{
    protected $title = "Server";
    protected $permissionName = 'server';

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view:server', only: ['index']),
            new Middleware('permission:create:server', only: ['store']),
            new Middleware('permission:edit:server', only: ['update', 'toggleStatus', 'testConnection']),
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

    public function destroy(string $id)
    {
        Server::findOrFail($id)->forceDelete();
        return back()->with('success', 'Successfully deleted server!');
    }
}
