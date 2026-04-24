<?php

namespace App\Http\Controllers\Admin;

use App\Models\Domain;
use App\Http\Controllers\Controller;
use App\Http\Requests\TableRequest;
use App\Http\Requests\DomainRequest;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class AdminDomainController extends Controller implements HasMiddleware
{
    protected $title = "domain";
    protected $permissionName = 'domain';

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view:domain', only: ['index']),
            new Middleware('permission:create:domain', only: ['store']),
            new Middleware('permission:edit:domain', only: ['update']),
            new Middleware('permission:delete:domain', only: ['destroy']),
        ];
    }

    public function index(TableRequest $request)
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('perPage', 10);

        $validPerPage = in_array($perPage, [10, 50, 100]) ? $perPage : 10;

        if ($search) {
            $domains = Domain::withTrashed()
                ->where('name', 'like', "%{$search}%")
                ->orWhere('ip', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%")
                ->paginate($validPerPage);
        } else {
            $domains = Domain::withTrashed()->paginate($validPerPage);
        }

        $permission = $this->permissionName;

        return view('admin.domain.index', compact('domains', 'search', 'perPage', 'permission'));
    }

    public function store(DomainRequest $request)
    {
        $domainData = $request->validated();
        $domain = Domain::create($domainData);

        if ($domain) {
            return back()->with('success', 'Successfully Create ' . $this->title . '!');
        } else {
            return back()->with('error', 'Failed to Create ' . $this->title . '!');
        }
    }

    public function update(DomainRequest $request, string $id)
    {
        $domain = Domain::findOrFail($id);
        $domain->update($request->validated());

        if ($domain) {
            return back()->with('success', 'Successfully Edit ' . $this->title . '!');
        } else {
            return back()->with('error', 'Failed to Edit ' . $this->title . '!');
        }
    }


    public function destroy(string $id)
    {
        Domain::findOrFail($id)->forceDelete();
        return back()->with('success', 'Successfully Delete ' . $this->title . '!');
    }
}
