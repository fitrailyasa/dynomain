<x-admin-table>

    <!-- Title -->
    <x-slot name="title">
        Domain & Wildcard Cloudflare
    </x-slot>

    <!-- Button Form Create -->
    <x-slot name="formCreate">
        @can('create:domain')
            @include('admin.domain.create')
        @endcan
    </x-slot>

    <!-- Search & Pagination -->
    <x-slot name="search">
        <div class="row mb-2">
            <div class="col-md-12">
                <form method="GET" action="{{ route('admin.domain.index') }}" class="d-flex flex-wrap gap-2 align-items-center">
                    <input type="text" name="search" class="form-control form-control-sm" style="max-width: 200px;" placeholder="Search..." value="{{ $search }}">

                    <select name="webserver_type" class="form-select form-select-sm" style="max-width: 150px;" onchange="this.form.submit()">
                        <option value="">Semua Webserver</option>
                        <option value="nginx" {{ ($webserverType ?? '') === 'nginx' ? 'selected' : '' }}>Nginx</option>
                        <option value="apache" {{ ($webserverType ?? '') === 'apache' ? 'selected' : '' }}>Apache</option>
                    </select>

                    <select name="target_type" class="form-select form-select-sm" style="max-width: 150px;" onchange="this.form.submit()">
                        <option value="">Semua Target</option>
                        <option value="proxy" {{ ($targetType ?? '') === 'proxy' ? 'selected' : '' }}>Proxy</option>
                        <option value="webroot" {{ ($targetType ?? '') === 'webroot' ? 'selected' : '' }}>Webroot</option>
                        <option value="laravel" {{ ($targetType ?? '') === 'laravel' ? 'selected' : '' }}>Laravel</option>
                        <option value="wordpress" {{ ($targetType ?? '') === 'wordpress' ? 'selected' : '' }}>WordPress</option>
                        <option value="redirect" {{ ($targetType ?? '') === 'redirect' ? 'selected' : '' }}>Redirect</option>
                    </select>

                    <select name="publish_status" class="form-select form-select-sm" style="max-width: 150px;" onchange="this.form.submit()">
                        <option value="">Semua Publish</option>
                        <option value="published" {{ ($publishStatus ?? '') === 'published' ? 'selected' : '' }}>Published</option>
                        <option value="pending" {{ ($publishStatus ?? '') === 'pending' ? 'selected' : '' }}>Unpublished</option>
                    </select>

                    <select name="status" class="form-select form-select-sm" style="max-width: 150px;" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="1" {{ ($status ?? '') === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ ($status ?? '') === '0' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>

                    <input type="hidden" name="perPage" value="{{ $perPage }}">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> Filter</button>
                    @if($search || $webserverType || $targetType || $publishStatus || ($status !== null && $status !== ''))
                        <a href="{{ route('admin.domain.index') }}" class="btn btn-sm btn-secondary"><i class="fas fa-times"></i> Reset</a>
                    @endif
                </form>
            </div>
        </div>

        @include('components.search')
        @can('delete:domain')
            <button type="button" class="btn btn-sm btn-danger ms-2" id="bulkDeleteBtn" style="display:none" onclick="bulkDeleteConfirm('domain')">
                <i class="fas fa-trash"></i> Delete Selected (<span id="selectedCount">0</span>)
            </button>
        @endcan
    </x-slot>

    <!-- Table -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                @can('delete:domain')
                    <th class="text-center" style="width: 40px;">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this, 'domain')">
                    </th>
                @endcan
                <th>{{ __('No') }}</th>
                <th>{{ __('Domain Name') }}</th>
                <th>{{ __('Target / IP') }}</th>
                <th>{{ __('Webserver') }}</th>
                <th>{{ __('Target Server') }}</th>
                <th class="text-center">{{ __('Publish') }}</th>
                <th>{{ __('Status') }}</th>
                @canany(['edit:domain', 'delete:domain'])
                    <th class="text-center">{{ __('Action') }}</th>
                @endcanany
            </tr>
        </thead>
        <tbody>
            @foreach ($domains as $item)
                <tr>
                    @can('delete:domain')
                        <td class="text-center">
                            <input type="checkbox" name="ids[]" value="{{ $item->id }}" class="item-checkbox domain-checkbox" onchange="updateBulkBtn('domain')">
                        </td>
                    @endcan
                    <td>{{ $domains->firstItem() + $loop->index }}</td>
                    <td>
                        <strong>{{ $item->name }}</strong>
                        @if($item->is_wildcard)
                            <br><span class="badge bg-info" title="Wildcard Cloudflare domain (*.{{ $item->name }})"><i class="fas fa-asterisk"></i> Wildcard</span>
                        @endif
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
                            @can('edit:server')
                                <div class="mt-1">
                                    <form action="{{ route('admin.server.reload-nginx', $item->server->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-warning text-dark m-1" title="Reload Nginx on {{ $item->server->name }}" onclick="return confirm('Reload nginx di {{ $item->server->name }}?')">
                                            <i class="fas fa-sync-alt"></i> Reload
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.server.restart-nginx', $item->server->id) }}" method="POST" class="d-inline" id="restartForm{{ $item->server->id }}">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-danger text-white m-1" title="Restart Nginx on {{ $item->server->name }}" onclick="return confirm('Restart nginx di {{ $item->server->name }}? Nginx akan restart dalam ~2 detik.')">
                                            <i class="fas fa-redo"></i> Restart
                                        </button>
                                    </form>
                                </div>
                            @endcan
                        @else
                            <span class="badge bg-secondary"><i class="fas fa-server"></i> Server Lokal</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @can('edit:domain')
                            @if($item->publish_status === 'published')
                                <form action="{{ route('admin.domain.unpublish', $item->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-success" title="Published - Klik untuk Unpublish" onclick="return confirm('Unpublish config {{ $item->name }}? Config akan di-disable dari server.')">
                                        <i class="fas fa-check-circle"></i> Published
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.domain.publish-config', $item->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-outline-secondary" title="Unpublished - Klik untuk Publish" onclick="return confirm('Publish config {{ $item->name }} ke {{ $item->server ? $item->server->name : 'Server Lokal' }}?')">
                                        <i class="fas fa-clock"></i> Unpublished
                                    </button>
                                </form>
                            @endif
                            @if($item->publish_log)
                                <br>
                                <button type="button" class="btn btn-xs btn-link p-0" data-bs-toggle="modal" data-bs-target="#logModal{{ $item->id }}">
                                    <small>View Log</small>
                                </button>
                                <div class="modal fade" id="logModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
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
                        <form action="{{ route('admin.domain.toggle-status', $item->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <label class="toggle-switch mb-0" title="{{ $item->status ? 'aktif' : 'tidak aktif' }}">
                                <input type="checkbox" {{ $item->status ? 'checked' : '' }}
                                    onchange="this.form.submit()" @cannot('edit:domain') disabled @endcannot>
                                <span class="toggle-slider"></span>
                            </label>
                        </form>
                    </td>
                    @canany(['edit:domain', 'delete:domain'])
                        <td class="manage-row text-center">
                            @can('edit:domain')
                                <!-- Preview Config Button -->
                                <button type="button" class="btn btn-sm btn-info text-white m-1" onclick="previewDomainConfig('{{ route('admin.domain.preview-config', $item->id) }}')" title="Preview Webserver Config">
                                    <i class="fas fa-code"></i> Preview
                                </button>

                                @include('admin.domain.edit')
                            @endcan
                            @can('delete:domain')
                                @include('admin.domain.delete')
                            @endcan
                        </td>
                    @endcanany
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $domains->appends(['perPage' => $perPage, 'search' => $search, 'webserver_type' => $webserverType, 'target_type' => $targetType, 'publish_status' => $publishStatus, 'status' => $status])->links('vendor.pagination.mobile') }}

    <!-- Shared Preview Config Modal -->
    <div class="modal fade" id="previewConfigModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewConfigModalTitle">Webserver Config Preview</h5>
                    <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body text-left">
                    <div class="mb-2 d-flex justify-content-between align-items-center">
                        <small class="text-muted" id="previewConfigFilename"></small>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="copyConfigToClipboard()"><i class="fas fa-copy"></i> Copy Config</button>
                    </div>
                    <textarea id="previewConfigContent" class="form-control font-monospace bg-dark text-light" rows="18" readonly></textarea>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewDomainConfig(url) {
            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('previewConfigModalTitle').innerText = 'Config Preview (' + data.webserver_type.toUpperCase() + ') - ' + data.domain_name;
                        document.getElementById('previewConfigFilename').innerText = 'Filename: ' + data.filename;
                        document.getElementById('previewConfigContent').value = data.config;
                        var modal = new bootstrap.Modal(document.getElementById('previewConfigModal'));
                        modal.show();
                    }
                })
                .catch(err => alert('Error loading config preview'));
        }

        function copyConfigToClipboard() {
            var content = document.getElementById('previewConfigContent');
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

            if (confirm('Hapus ' + ids.length + ' item yang dipilih? Config akan dihapus dari server.')) {
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
