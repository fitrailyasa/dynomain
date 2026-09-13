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
            new Middleware('permission:edit:domain', only: ['update', 'toggleStatus', 'previewConfig', 'publishConfig', 'unpublish']),
            new Middleware('permission:delete:domain', only: ['destroy', 'bulkDelete']),
        ];
    }

    public function index(TableRequest $request)
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('perPage', 10);
        $validPerPage = in_array($perPage, [10, 50, 100]) ? $perPage : 10;

        $webserverType = $request->input('webserver_type');
        $targetType = $request->input('target_type');
        $publishStatus = $request->input('publish_status');
        $status = $request->input('status');

        $servers = Server::where('status', true)->get();

        $query = Domain::withTrashed()->with('server');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('ip', 'like', "%{$search}%");
            });
        }

        if ($webserverType) {
            $query->where('webserver_type', $webserverType);
        }

        if ($targetType) {
            $query->where('target_type', $targetType);
        }

        if ($publishStatus) {
            $query->where('publish_status', $publishStatus);
        }

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        $domains = $query->paginate($validPerPage);

        $permission = $this->permissionName;

        return view('admin.domain.index', compact('domains', 'search', 'perPage', 'permission', 'servers', 'webserverType', 'targetType', 'publishStatus', 'status'));
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

        // Get old filename and webserver type before update
        $oldFilename = $publisher->getGenerator()->getFilename($domain);
        $oldWebserverType = $domain->webserver_type;

        $domainData = $request->validated();
        $domainData['is_wildcard'] = $request->has('is_wildcard');

        $domain->update($domainData);

        // Cleanup old config files if filename or webserver type changed
        $server = $domain->server;
        $publisher->cleanupOldConfig($domain, $oldFilename, $server, $oldWebserverType);

        // Auto-publish config ke server setelah update
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

    public function unpublish(string $id, WebserverPublisherService $publisher)
    {
        $domain = Domain::findOrFail($id);
        $server = $domain->server;
        $result = $publisher->unpublish($domain, $server);

        if ($result['success']) {
            return back()->with('success', $result['message'] . "\n\nLog:\n" . $result['log']);
        }

        return back()->with('error', $result['message'] . "\n\nLog:\n" . $result['log']);
    }

    public function destroy(string $id, WebserverPublisherService $publisher)
    {
        $domain = Domain::findOrFail($id);

        // Hapus config file & symlink dari server
        $publisher->deleteConfig($domain, $domain->server);

        $domain->forceDelete();

        return back()->with('success', 'Successfully Deleted domain & config removed from server!');
    }

    public function bulkDelete(Request $request, WebserverPublisherService $publisher)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'No items selected for deletion.');
        }

        $domains = Domain::whereIn('id', $ids)->get();
        $deleted = 0;

        foreach ($domains as $domain) {
            $publisher->deleteConfig($domain, $domain->server);
            $domain->forceDelete();
            $deleted++;
        }

        return back()->with('success', "Successfully deleted {$deleted} domain(s) & configs removed from server!");
    }
}
