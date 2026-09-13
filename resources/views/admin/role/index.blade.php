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
        @can('delete:role')
            <button type="button" class="btn btn-sm btn-danger ms-2" id="bulkDeleteBtn" disabled onclick="bulkDeleteConfirm('role')">
                <i class="fas fa-trash"></i> Delete Selected (<span id="selectedCount">0</span>)
            </button>
        @endcan
    </x-slot>

    <!-- Table -->
    <table id="" class="table table-bordered table-striped">
        <thead>
            <tr>
                @can('delete:role')
                    <th class="text-center" style="width: 40px;">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this, 'role')">
                    </th>
                @endcan
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
                    @can('delete:role')
                        <td class="text-center">
                            <input type="checkbox" name="ids[]" value="{{ $item->id }}" class="item-checkbox role-checkbox" onchange="updateBulkBtn('role')">
                        </td>
                    @endcan
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

    <script>
        function toggleSelectAll(checkbox, entity) {
            document.querySelectorAll('.' + entity + '-checkbox').forEach(function(cb) {
                cb.checked = checkbox.checked;
            });
            updateBulkBtn(entity);
        }

        function updateBulkBtn(entity) {
            var checked = document.querySelectorAll('.' + entity + '-checkbox:checked').length;
            document.getElementById('selectedCount').textContent = checked;
            document.getElementById('bulkDeleteBtn').disabled = checked === 0;
        }

        function bulkDeleteConfirm(entity) {
            var checked = document.querySelectorAll('.' + entity + '-checkbox:checked');
            var ids = [];
            checked.forEach(function(cb) { ids.push(cb.value); });

            if (ids.length === 0) return;

            if (confirm('Hapus ' + ids.length + ' role yang dipilih?')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '/admin/' + entity + '/bulk-delete';

                var csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);

                var method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'DELETE';
                form.appendChild(method);

                ids.forEach(function(id) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    form.appendChild(input);
                });

                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>

</x-admin-table>
