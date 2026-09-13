<?php

namespace App\Http\Controllers\Admin;

use App\Models\Domain;
use App\Models\Server;
use App\Http\Controllers\Controller;
use App\Http\Requests\TableRequest;
use App\Http\Requests\DomainRequest;
use App\Services\WebserverGeneratorService;
use App\Services\WebserverPublisherService;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Http\Request;

class AdminDomainController extends Controller implements HasMiddleware
{
    protected $title = "domain";
    protected $permissionName = 'domain';

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view:domain', only: ['index']),
            new Middleware('permission:create:domain', only: ['store']),
            new Middleware('permission:edit:domain', only: ['update', 'toggleStatus', 'previewConfig', 'publishConfig']),
            new Middleware('permission:delete:domain', only: ['destroy']),
        ];
    }

    public function index(TableRequest $request)
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('perPage', 10);
        $validPerPage = in_array($perPage, [10, 50, 100]) ? $perPage : 10;

        $servers = Server::where('status', true)->get();

        if ($search) {
            $domains = Domain::withTrashed()
                ->where('name', 'like', "%{$search}%")
                ->orWhere('ip', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%")
                ->with('server')
                ->paginate($validPerPage);
        } else {
            $domains = Domain::withTrashed()->with('server')->paginate($validPerPage);
        }

        $permission = $this->permissionName;

        return view('admin.domain.index', compact('domains', 'search', 'perPage', 'permission', 'servers'));
    }

    public function store(DomainRequest $request)
    {
        $domainData = $request->validated();
        $domainData['is_wildcard'] = $request->has('is_wildcard');
        
        $domain = Domain::create($domainData);

        if ($domain) {
            return back()->with('success', 'Successfully Created ' . $this->title . '!');
        } else {
            return back()->with('error', 'Failed to Create ' . $this->title . '!');
        }
    }

    public function update(DomainRequest $request, string $id, WebserverPublisherService $publisher)
    {
        $domain = Domain::findOrFail($id);
        $domainData = $request->validated();
        $domainData['is_wildcard'] = $request->has('is_wildcard');

        $domain->update($domainData);

        // Auto-publish config ke server setelah update
        $server = $domain->server;
        $result = $publisher->publish($domain, $server);

        if ($result['success']) {
            return back()->with('success', 'Successfully Updated ' . $this->title . ' & config published to server!');
        }

        return back()->with('warning', 'Updated ' . $this->title . ' in database, but publish failed: ' . $result['message']);
    }

    public function toggleStatus(string $id)
    {
        $domain = Domain::findOrFail($id);
        $domain->update([
            'status' => !$domain->status,
        ]);

        return back()->with('success', 'Successfully Updated Status ' . $this->title . '!');
    }

    public function previewConfig(string $id, WebserverGeneratorService $generator)
    {
        $domain = Domain::with('server')->findOrFail($id);
        $config = $generator->generate($domain, $domain->webserver_type);
        $filename = $generator->getFilename($domain);

        return response()->json([
            'success' => true,
            'filename' => $filename,
            'config' => $config,
            'webserver_type' => $domain->webserver_type,
            'domain_name' => $domain->name,
        ]);
    }

    public function publishConfig(string $id, Request $request, WebserverPublisherService $publisher)
    {
        $domain = Domain::findOrFail($id);
        
        if ($request->has('server_id') && !empty($request->server_id)) {
            $domain->update(['server_id' => $request->server_id]);
        }

        $server = $domain->server;
        $result = $publisher->publish($domain, $server);

        if ($result['success']) {
            return back()->with('success', $result['message'] . "\n\nLog Details:\n" . $result['log']);
        }

        return back()->with('error', $result['message'] . "\n\nLog Details:\n" . $result['log']);
    }

    public function destroy(string $id)
    {
        Domain::findOrFail($id)->forceDelete();
        return back()->with('success', 'Successfully Deleted ' . $this->title . '!');
    }
}
