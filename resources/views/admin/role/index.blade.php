<x-admin-table>

    <!-- Title -->
    <x-slot name="title">
        Role & Permissions
    </x-slot>

    <!-- Button Form Create -->
    <x-slot name="formCreate">
        @can('create:role')
            @include('admin.role.create', ['permissions' => $permissions])
        @endcan
    </x-slot>

    <!-- Search & Pagination -->
    <x-slot name="search">
        @include('components.search')
    </x-slot>

    <!-- Table -->
    <table id="" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>{{ __('No') }}</th>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Permissions') }}</th>
                @canany(['edit:role', 'delete:role'])
                    <th class="text-center">{{ __('Action') }}</th>
                @endcanany
            </tr>
        </thead>
        <tbody>
            @foreach ($roles as $index => $item)
                <tr>
                    <td>{{ $roles->firstItem() + $index }}</td>
                    <td>{{ $item->name ?? '-' }}</td>
                    <td>
                        @php
                            $grouped = $item->permissions
                                ->map(function ($perm) use ($permissions) {
                                    $found = $permissions->first(fn($p) => $p->id === $perm->id);
                                    return $found ?:
                                        (object) [
                                            'id' => $perm->id,
                                            'action' => explode(':', $perm->name)[0] ?? $perm->name,
                                            'entity' => explode(':', $perm->name)[1] ?? 'other',
                                            'badgeClass' => 'badge-dark',
                                        ];
                                })
                                ->groupBy('entity');
                        @endphp

                        @foreach ($grouped as $entity => $entityPerms)
                            <div class="mb-2">
                                <strong class="text-capitalize">{{ $entity }}:</strong>
                                @foreach ($entityPerms as $perm)
                                    <span class="badge {{ $perm->badgeClass }}">{{ $perm->action }}</span>
                                @endforeach
                            </div>
                        @endforeach
                    </td>

                    @canany(['edit:role', 'delete:role'])
                        <td class="text-center">
                            @can('edit:role')
                                @include('admin.role.edit', [
                                    'item' => $item,
                                    'permissions' => $permissions,
                                ])
                            @endcan
                            @can('delete:role')
                                @include('admin.role.delete', ['item' => $item])
                            @endcan
                        </td>
                    @endcanany
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $roles->appends(['perPage' => $perPage, 'search' => $search])->links('vendor.pagination.mobile') }}

</x-admin-table>
