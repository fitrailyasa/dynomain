<x-admin-table>

    <!-- Title -->
    <x-slot name="title">
        GitHub SSH (PAT)
    </x-slot>

    <!-- Button Form Create -->
    <x-slot name="formCreate">
        @can('create:github-ssh')
            @include('admin.github-ssh.create')
        @endcan
    </x-slot>

    <!-- Search & Pagination -->
    <x-slot name="search">
        @include('components.search')
        @can('delete:github-ssh')
            <button type="button" class="btn btn-sm btn-danger ms-2" id="bulkDeleteBtn" style="display:none" onclick="bulkDeleteConfirm('github-ssh')">
                <i class="fas fa-trash"></i> (<span id="selectedCount">0</span>)
            </button>
        @endcan
        @can('edit:github-ssh')
            <button type="button" class="btn btn-sm btn-success ms-2" id="bulkStatusOnBtn" style="display:none" onclick="bulkStatusConfirm('github-ssh', 1)">
                <i class="fas fa-check-circle"></i> Aktifkan
            </button>
            <button type="button" class="btn btn-sm btn-warning ms-2" id="bulkStatusOffBtn" style="display:none" onclick="bulkStatusConfirm('github-ssh', 0)">
                <i class="fas fa-times-circle"></i> Nonaktifkan
            </button>
        @endcan
    </x-slot>

    <!-- Table -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                @can('delete:github-ssh')
                    <th class="text-center" style="width: 40px;">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this, 'github-ssh')">
                    </th>
                @endcan
                <th>{{ __('No') }}</th>
                <th>{{ __('Nama') }}</th>
                <th>{{ __('Username') }}</th>
                <th>{{ __('PAT') }}</th>
                <th>{{ __('Status') }}</th>
                @canany(['edit:github-ssh', 'delete:github-ssh'])
                    <th class="text-center">{{ __('Action') }}</th>
                @endcanany
            </tr>
        </thead>
        <tbody>
            @foreach ($githubSsh as $item)
                <tr>
                    @can('delete:github-ssh')
                        <td class="text-center">
                            <input type="checkbox" name="ids[]" value="{{ $item->id }}" class="item-checkbox github-ssh-checkbox" onchange="updateBulkBtn('github-ssh')">
                        </td>
                    @endcan
                    <td>{{ $githubSsh->firstItem() + $loop->index }}</td>
                    <td>
                        <strong>{{ $item->name }}</strong>
                    </td>
                    <td>
                        <code>{{ $item->username }}</code>
                    </td>
                    <td>
                        <code>********</code>
                    </td>
                    <td>
                        <form action="{{ route('admin.github-ssh.toggle-status', $item->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <label class="toggle-switch mb-0" title="{{ $item->status ? 'aktif' : 'tidak aktif' }}">
                                <input type="checkbox" {{ $item->status ? 'checked' : '' }}
                                    onchange="this.form.submit()" @cannot('edit:github-ssh') disabled @endcannot>
                                <span class="toggle-slider"></span>
                            </label>
                        </form>
                    </td>
                    @canany(['edit:github-ssh', 'delete:github-ssh'])
                        <td class="manage-row text-center">
                            @can('edit:github-ssh')
                                @include('admin.github-ssh.edit')
                            @endcan
                            @can('delete:github-ssh')
                                @include('admin.github-ssh.delete')
                            @endcan
                        </td>
                    @endcanany
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $githubSsh->appends(['perPage' => $perPage, 'search' => $search])->links('vendor.pagination.mobile') }}

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
            var btnOn = document.getElementById('bulkStatusOnBtn');
            var btnOff = document.getElementById('bulkStatusOffBtn');
            document.getElementById('selectedCount').textContent = checked;
            btn.style.display = checked > 0 ? 'inline-block' : 'none';
            if (btnOn) btnOn.style.display = checked > 0 ? 'inline-block' : 'none';
            if (btnOff) btnOff.style.display = checked > 0 ? 'inline-block' : 'none';
        }

        function bulkDeleteConfirm(entity) {
            var checked = document.querySelectorAll('.' + entity + '-checkbox:checked');
            var ids = [];
            checked.forEach(function(cb) { ids.push(cb.value); });

            if (ids.length === 0) return;

            if (confirm('Hapus ' + ids.length + ' GitHub SSH yang dipilih?')) {
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

        function bulkStatusConfirm(entity, status) {
            var checked = document.querySelectorAll('.' + entity + '-checkbox:checked');
            var ids = [];
            checked.forEach(function(cb) { ids.push(cb.value); });

            if (ids.length === 0) return;

            var statusText = status == 1 ? 'aktifkan' : 'nonaktifkan';
            if (confirm(statusText + ' ' + ids.length + ' GitHub SSH yang dipilih?')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '/admin/' + entity + '/bulk-status';

                var csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);

                var method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'PATCH';
                form.appendChild(method);

                var statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'status';
                statusInput.value = status;
                form.appendChild(statusInput);

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
