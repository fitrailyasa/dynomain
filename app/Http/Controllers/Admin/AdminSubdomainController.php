<?php

namespace App\Http\Controllers\Admin;

use App\Models\Domain;
use App\Models\Subdomain;
use App\Http\Controllers\Controller;
use App\Http\Requests\TableRequest;
use App\Http\Requests\SubdomainRequest;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class AdminSubdomainController extends Controller implements HasMiddleware
{
    protected $title = "subdomain";
    protected $permissionName = 'subdomain';

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view:subdomain', only: ['index']),
            new Middleware('permission:create:subdomain', only: ['store']),
            new Middleware('permission:edit:subdomain', only: ['update']),
            new Middleware('permission:delete:subdomain', only: ['destroy']),
        ];
    }

    public function index(TableRequest $request)
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('perPage', 10);

        $validPerPage = in_array($perPage, [10, 50, 100]) ? $perPage : 10;

        $domains = Domain::all();

        if ($search) {
            $subdomains = Subdomain::withTrashed()
                ->where('name', 'like', "%{$search}%")
                ->orWhere('ip', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%")
                ->orWhereHas('domain', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                ->with('domain')
                ->paginate($validPerPage);
        } else {
            $subdomains = Subdomain::withTrashed()->with('domain')->paginate($validPerPage);
        }

        $permission = $this->permissionName;

        return view('admin.subdomain.index', compact('subdomains', 'search', 'perPage', 'permission', 'domains'));
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

    public function update(SubdomainRequest $request, string $id)
    {
        $subdomain = Subdomain::findOrFail($id);
        $subdomain->update($request->validated());

        if ($subdomain) {
            return back()->with('success', 'Successfully Edit ' . $this->title . '!');
        } else {
            return back()->with('error', 'Failed to Edit ' . $this->title . '!');
        }
    }


    public function destroy(string $id)
    {
        Subdomain::findOrFail($id)->forceDelete();
        return back()->with('success', 'Successfully Delete ' . $this->title . '!');
    }
}
