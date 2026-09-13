<x-admin-table>

    <!-- Title -->
    <x-slot name="title">
        Subdomain Management
    </x-slot>

    <!-- Button Form Create -->
    <x-slot name="formCreate">
        @can('create:subdomain')
            @include('admin.subdomain.create')
        @endcan
    </x-slot>

    <!-- Search & Pagination -->
    <x-slot name="search">
        @include('components.search')
        @can('delete:subdomain')
            <button type="button" class="btn btn-sm btn-danger ms-2" id="bulkDeleteBtn" style="display:none" onclick="bulkDeleteConfirm('subdomain')">
                <i class="fas fa-trash"></i> Delete Selected (<span id="selectedCount">0</span>)
            </button>
        @endcan
    </x-slot>

    <!-- Table -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                @can('delete:subdomain')
                    <th class="text-center" style="width: 40px;">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this, 'subdomain')">
                    </th>
                @endcan
                <th>{{ __('No') }}</th>
                <th>{{ __('Subdomain Name') }}</th>
                <th>{{ __('Main Domain') }}</th>
                <th>{{ __('Target / IP') }}</th>
                <th>{{ __('Webserver') }}</th>
                <th>{{ __('Target Server') }}</th>
                <th>{{ __('Publish Status') }}</th>
                <th>{{ __('Status') }}</th>
                @canany(['edit:subdomain', 'delete:subdomain'])
                    <th class="text-center">{{ __('Action') }}</th>
                @endcanany
            </tr>
        </thead>
        <tbody>
            @foreach ($subdomains as $item)
                <tr>
                    @can('delete:subdomain')
                        <td class="text-center">
                            <input type="checkbox" name="ids[]" value="{{ $item->id }}" class="item-checkbox subdomain-checkbox" onchange="updateBulkBtn('subdomain')">
                        </td>
                    @endcan
                    <td>{{ $subdomains->firstItem() + $loop->index }}</td>
                    <td>
                        <strong>{{ $item->name }}.{{ $item->domain ? $item->domain->name : '' }}</strong>
                    </td>
                    <td>
                        {{ $item->domain ? $item->domain->name : '-' }}
                    </td>
                    <td>
                        <small>
                            <strong>{{ strtoupper($item->target_type ?? 'proxy') }}:</strong><br>
                            <code>{{ $item->target_destination ?: ($item->ip ? 'http://' . $item->ip : 'http://127.0.0.1:8000') }}</code>
                        </small>
                    </td>
                    <td>
                        <span class="badge {{ $item->webserver_type === 'apache' ? 'bg-warning' : 'bg-success' }}">
                            {{ strtoupper($item->webserver_type ?? 'NGINX') }}
                        </span>
                    </td>
                    <td>
                        @if($item->server)
                            <span class="badge {{ $item->server->type === 'ssh' ? 'bg-purple' : 'bg-secondary' }}">
                                <i class="fas {{ $item->server->type === 'ssh' ? 'fa-terminal' : 'fa-server' }}"></i>
                                {{ $item->server->name }}
                            </span>
                        @else
                            <span class="badge bg-secondary"><i class="fas fa-server"></i> Server Lokal</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @can('edit:subdomain')
                            @if($item->publish_status === 'published')
                                <form action="{{ route('admin.subdomain.unpublish', $item->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-success" title="Published - Klik untuk Unpublish" onclick="return confirm('Unpublish config {{ $item->name }}? Config akan di-disable dari server.')">
                                        <i class="fas fa-check-circle"></i> Published
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.subdomain.publish-config', $item->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-outline-secondary" title="Unpublished - Klik untuk Publish" onclick="return confirm('Publish config {{ $item->name }} ke {{ $item->server ? $item->server->name : 'Server Lokal' }}?')">
                                        <i class="fas fa-clock"></i> Unpublished
                                    </button>
                                </form>
                            @endif
                            @if($item->publish_log)
                                <br>
                                <button type="button" class="btn btn-xs btn-link p-0" data-bs-toggle="modal" data-bs-target="#subLogModal{{ $item->id }}">
                                    <small>View Log</small>
                                </button>
                                <div class="modal fade" id="subLogModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content text-left">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Publish Log - {{ $item->name }}</h5>
                                                <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                                            </div>
                                            <div class="modal-body bg-dark text-light p-3">
                                                <pre class="m-0 text-light" style="white-space: pre-wrap;">{{ $item->publish_log }}</pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @else
                            @if($item->publish_status === 'published')
                                <span class="badge bg-success"><i class="fas fa-check-circle"></i> Published</span>
                            @else
                                <span class="badge bg-secondary"><i class="fas fa-clock"></i> Unpublished</span>
                            @endif
                        @endcan
                    </td>
                    <td>
                        <form action="{{ route('admin.subdomain.toggle-status', $item->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <label class="toggle-switch mb-0" title="{{ $item->status ? 'aktif' : 'tidak aktif' }}">
                                <input type="checkbox" {{ $item->status ? 'checked' : '' }}
                                    onchange="this.form.submit()" @cannot('edit:subdomain') disabled @endcannot>
                                <span class="toggle-slider"></span>
                            </label>
                        </form>
                    </td>
                    @canany(['edit:subdomain', 'delete:subdomain'])
                        <td class="manage-row text-center">
                            @can('edit:subdomain')
                                <!-- Preview Config Button -->
                                <button type="button" class="btn btn-sm btn-info text-white m-1" onclick="previewSubdomainConfig('{{ route('admin.subdomain.preview-config', $item->id) }}')" title="Preview Webserver Config">
                                    <i class="fas fa-code"></i> Preview
                                </button>

                                @include('admin.subdomain.edit')
                            @endcan
                            @can('delete:subdomain')
                                @include('admin.subdomain.delete')
                            @endcan
                        </td>
                    @endcanany
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $subdomains->appends(['perPage' => $perPage, 'search' => $search])->links('vendor.pagination.mobile') }}

    <!-- Shared Preview Config Modal -->
    <div class="modal fade" id="previewSubConfigModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewSubConfigModalTitle">Subdomain Config Preview</h5>
                    <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body text-left">
                    <div class="mb-2 d-flex justify-content-between align-items-center">
                        <small class="text-muted" id="previewSubConfigFilename"></small>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="copySubConfigToClipboard()"><i class="fas fa-copy"></i> Copy Config</button>
                    </div>
                    <textarea id="previewSubConfigContent" class="form-control font-monospace bg-dark text-light" rows="18" readonly></textarea>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewSubdomainConfig(url) {
            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('previewSubConfigModalTitle').innerText = 'Config Preview (' + data.webserver_type.toUpperCase() + ') - ' + data.subdomain_name;
                        document.getElementById('previewSubConfigFilename').innerText = 'Filename: ' + data.filename;
                        document.getElementById('previewSubConfigContent').value = data.config;
                        var modal = new bootstrap.Modal(document.getElementById('previewSubConfigModal'));
                        modal.show();
                    }
                })
                .catch(err => alert('Error loading config preview'));
        }

        function copySubConfigToClipboard() {
            var content = document.getElementById('previewSubConfigContent');
            content.select();
            document.execCommand('copy');
            alert('Config copied to clipboard!');
        }

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

            if (confirm('Hapus ' + ids.length + ' item yang dipilih?')) {
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
