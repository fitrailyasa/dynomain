<?php

namespace App\Http\Controllers\Admin;

use App\Models\GithubSsh;
use App\Http\Controllers\Controller;
use App\Http\Requests\TableRequest;
use App\Http\Requests\GithubSshRequest;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class AdminGithubSshController extends Controller implements HasMiddleware
{
    protected $title = "Github SSH";
    protected $permissionName = 'github-ssh';

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view:github-ssh', only: ['index']),
            new Middleware('permission:create:github-ssh', only: ['store']),
            new Middleware('permission:edit:github-ssh', only: ['update', 'toggleStatus']),
            new Middleware('permission:delete:github-ssh', only: ['destroy', 'bulkDelete']),
        ];
    }

    public function index(TableRequest $request)
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('perPage', 10);
        $validPerPage = in_array($perPage, [10, 50, 100]) ? $perPage : 10;

        if ($search) {
            $githubSsh = GithubSsh::withTrashed()
                ->where('name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
                ->paginate($validPerPage);
        } else {
            $githubSsh = GithubSsh::withTrashed()->paginate($validPerPage);
        }

        $permission = $this->permissionName;

        return view('admin.github-ssh.index', compact('githubSsh', 'search', 'perPage', 'permission'));
    }

    public function store(GithubSshRequest $request)
    {
        $data = $request->validated();

        $githubSsh = GithubSsh::create($data);

        if ($githubSsh) {
            return back()->with('success', 'Successfully created GitHub SSH!');
        }

        return back()->with('error', 'Failed to create GitHub SSH!');
    }

    public function update(GithubSshRequest $request, string $id)
    {
        $githubSsh = GithubSsh::findOrFail($id);
        $data = $request->validated();

        if (empty($data['pat'])) {
            unset($data['pat']);
        }

        $githubSsh->update($data);

        return back()->with('success', 'Successfully updated GitHub SSH!');
    }

    public function toggleStatus(string $id)
    {
        $githubSsh = GithubSsh::findOrFail($id);
        $githubSsh->update([
            'status' => !$githubSsh->status,
        ]);

        return back()->with('success', 'Successfully updated GitHub SSH status!');
    }

    public function destroy(string $id)
    {
        GithubSsh::findOrFail($id)->forceDelete();
        return back()->with('success', 'Successfully deleted GitHub SSH!');
    }

    public function bulkDelete(\Illuminate\Http\Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'No items selected for deletion.');
        }

        GithubSsh::whereIn('id', $ids)->forceDelete();

        return back()->with('success', 'Successfully deleted ' . count($ids) . ' GitHub SSH(s)!');
    }
}
