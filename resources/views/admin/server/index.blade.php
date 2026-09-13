<x-admin-table>

    <!-- Title -->
    <x-slot name="title">
        Server & SSH Target
    </x-slot>

    <!-- Button Form Create -->
    <x-slot name="formCreate">
        @can('create:server')
            @include('admin.server.create')
        @endcan
    </x-slot>

    <!-- Search & Pagination -->
    <x-slot name="search">
        @include('components.search')
        @can('delete:server')
            <button type="button" class="btn btn-sm btn-danger ms-2" id="bulkDeleteBtn" style="display:none" onclick="bulkDeleteConfirm('server')">
                <i class="fas fa-trash"></i> Delete Selected (<span id="selectedCount">0</span>)
            </button>
        @endcan
    </x-slot>

    <!-- Table -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                @can('delete:server')
                    <th class="text-center" style="width: 40px;">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this, 'server')">
                    </th>
                @endcan
                <th>{{ __('No') }}</th>
                <th>{{ __('Nama Server') }}</th>
                <th>{{ __('Tipe Server') }}</th>
                <th>{{ __('Host / SSH') }}</th>
                <th>{{ __('Status') }}</th>
                @canany(['edit:server', 'delete:server'])
                    <th class="text-center">{{ __('Action') }}</th>
                @endcanany
            </tr>
        </thead>
        <tbody>
            @foreach ($servers as $item)
                <tr>
                    @can('delete:server')
                        <td class="text-center">
                            <input type="checkbox" name="ids[]" value="{{ $item->id }}" class="item-checkbox server-checkbox" onchange="updateBulkBtn('server')">
                        </td>
                    @endcan
                    <td>{{ $servers->firstItem() + $loop->index }}</td>
                    <td>
                        <strong>{{ $item->name }}</strong>
                    </td>
                    <td>
                        @if($item->type === 'ssh')
                            <span class="badge bg-purple"><i class="fas fa-terminal"></i> Remote SSH</span>
                        @else
                            <span class="badge bg-info"><i class="fas fa-server"></i> Server Lokal</span>
                        @endif
                    </td>
                    <td>
                        @if($item->type === 'ssh')
                            <code>{{ $item->username }}@${{ $item->host }}:{{ $item->port }}</code>
                        @else
                            <span class="text-muted">localhost (Server Ini)</span>
                        @endif
                    </td>
                    <td>
                        <form action="{{ route('admin.server.toggle-status', $item->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <label class="toggle-switch mb-0" title="{{ $item->status ? 'aktif' : 'tidak aktif' }}">
                                <input type="checkbox" {{ $item->status ? 'checked' : '' }}
                                    onchange="this.form.submit()" @cannot('edit:server') disabled @endcannot>
                                <span class="toggle-slider"></span>
                            </label>
                        </form>
                    </td>
                    @canany(['edit:server', 'delete:server'])
                        <td class="manage-row text-center">
                            @if($item->type === 'ssh')
                                <form action="{{ route('admin.server.test-connection', $item->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-info text-white m-1" title="Test Connection SSH">
                                        <i class="fas fa-plug"></i> Test
                                    </button>
                                </form>
                            @endif
                            @can('edit:server')
                                @include('admin.server.edit')
                            @endcan
                            @can('delete:server')
                                @include('admin.server.delete')
                            @endcan
                        </td>
                    @endcanany
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $servers->appends(['perPage' => $perPage, 'search' => $search])->links('vendor.pagination.mobile') }}

    <script>
        function toggleSelectAll(checkbox, entity) {
            document.querySelectorAll('.' + entity + '-checkbox').forEach(function(cb) {
                cb.checked = checkbox.checked;
            });
            updateBulkBtn(entity);
        }

        function updateBulkBtn(entity) {
            var checked = document.querySelectorAll('.' + entity + '-checkbox:checked').length;
            var btn = document.getElementById('bulkDeleteBtn');
            document.getElementById('selectedCount').textContent = checked;
            btn.style.display = checked > 0 ? 'inline-block' : 'none';
        }

        function bulkDeleteConfirm(entity) {
            var checked = document.querySelectorAll('.' + entity + '-checkbox:checked');
            var ids = [];
            checked.forEach(function(cb) { ids.push(cb.value); });

            if (ids.length === 0) return;

            if (confirm('Hapus ' + ids.length + ' server yang dipilih?')) {
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
