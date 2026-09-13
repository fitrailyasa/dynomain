<?php

namespace App\Http\Controllers\Admin;

use App\Models\Domain;
use App\Models\Subdomain;
use App\Models\Server;
use App\Http\Controllers\Controller;
use App\Http\Requests\TableRequest;
use App\Http\Requests\SubdomainRequest;
use App\Services\WebserverGeneratorService;
use App\Services\WebserverPublisherService;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Http\Request;

class AdminSubdomainController extends Controller implements HasMiddleware
{
    protected $title = "subdomain";
    protected $permissionName = 'subdomain';

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view:subdomain', only: ['index']),
            new Middleware('permission:create:subdomain', only: ['store']),
            new Middleware('permission:edit:subdomain', only: ['update', 'toggleStatus', 'previewConfig', 'publishConfig']),
            new Middleware('permission:delete:subdomain', only: ['destroy', 'bulkDelete']),
        ];
    }

    public function index(TableRequest $request)
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('perPage', 10);
        $validPerPage = in_array($perPage, [10, 50, 100]) ? $perPage : 10;

        $domains = Domain::all();
        $servers = Server::where('status', true)->get();

        if ($search) {
            $subdomains = Subdomain::withTrashed()
                ->where('name', 'like', "%{$search}%")
                ->orWhere('ip', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%")
                ->orWhereHas('domain', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                ->with(['domain', 'server'])
                ->paginate($validPerPage);
        } else {
            $subdomains = Subdomain::withTrashed()->with(['domain', 'server'])->paginate($validPerPage);
        }

        $permission = $this->permissionName;

        return view('admin.subdomain.index', compact('subdomains', 'search', 'perPage', 'permission', 'domains', 'servers'));
    }

    public function store(SubdomainRequest $request)
    {
        $subdomainData = $request->validated();
        $subdomain = Subdomain::create($subdomainData);

        if ($subdomain) {
            return back()->with('success', 'Successfully Create ' . $this->title . '!');
        } else {
            return back()->with('error', 'Failed to Create ' . $this->title . '!');
        }
    }

    public function update(SubdomainRequest $request, string $id, WebserverPublisherService $publisher)
    {
        $subdomain = Subdomain::findOrFail($id);
        $subdomain->update($request->validated());

        // Auto-publish config ke server setelah update
        $server = $subdomain->server;
        $result = $publisher->publish($subdomain, $server);

        if ($result['success']) {
            return back()->with('success', 'Successfully Updated ' . $this->title . ' & config published to server!');
        }

        return back()->with('warning', 'Updated ' . $this->title . ' in database, but publish failed: ' . $result['message']);
    }

    public function toggleStatus(string $id)
    {
        $subdomain = Subdomain::findOrFail($id);
        $subdomain->update([
            'status' => !$subdomain->status,
        ]);

        return back()->with('success', 'Successfully Update Status ' . $this->title . '!');
    }

    public function previewConfig(string $id, WebserverGeneratorService $generator)
    {
        $subdomain = Subdomain::with(['domain', 'server'])->findOrFail($id);
        $config = $generator->generate($subdomain, $subdomain->webserver_type);
        $filename = $generator->getFilename($subdomain);

        return response()->json([
            'success' => true,
            'filename' => $filename,
            'config' => $config,
            'webserver_type' => $subdomain->webserver_type,
            'subdomain_name' => $subdomain->name,
        ]);
    }

    public function publishConfig(string $id, Request $request, WebserverPublisherService $publisher)
    {
        $subdomain = Subdomain::findOrFail($id);

        if ($request->has('server_id') && !empty($request->server_id)) {
            $subdomain->update(['server_id' => $request->server_id]);
        }

        $server = $subdomain->server;
        $result = $publisher->publish($subdomain, $server);

        if ($result['success']) {
            return back()->with('success', $result['message'] . "\n\nLog Details:\n" . $result['log']);
        }

        return back()->with('error', $result['message'] . "\n\nLog Details:\n" . $result['log']);
    }

    public function destroy(string $id)
    {
        Subdomain::findOrFail($id)->forceDelete();
        return back()->with('success', 'Successfully Delete ' . $this->title . '!');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'No items selected for deletion.');
        }

        Subdomain::whereIn('id', $ids)->forceDelete();

        return back()->with('success', 'Successfully deleted ' . count($ids) . ' subdomain(s)!');
    }
}
