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
        @include('components.search')
    </x-slot>

    <!-- Table -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>{{ __('No') }}</th>
                <th>{{ __('Domain Name') }}</th>
                <th>{{ __('Target / IP') }}</th>
                <th>{{ __('Webserver') }}</th>
                <th>{{ __('Target Server') }}</th>
                <th>{{ __('Publish Status') }}</th>
                <th>{{ __('Status') }}</th>
                @canany(['edit:domain', 'delete:domain'])
                    <th class="text-center">{{ __('Action') }}</th>
                @endcanany
            </tr>
        </thead>
        <tbody>
            @foreach ($domains as $item)
                <tr>
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
                                    <form action="{{ route('admin.server.restart-nginx', $item->server->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-danger text-white m-1" title="Restart Nginx on {{ $item->server->name }}" onclick="return confirm('Restart nginx di {{ $item->server->name }}?')">
                                            <i class="fas fa-redo"></i> Restart
                                        </button>
                                    </form>
                                </div>
                            @endcan
                        @else
                            <span class="badge bg-secondary"><i class="fas fa-server"></i> Server Lokal</span>
                        @endif
                    </td>
                    <td>
                        @if($item->publish_status === 'published')
                            <span class="badge bg-success" title="Published: {{ $item->published_at }}"><i class="fas fa-check-circle"></i> Published</span>
                            @if($item->published_at)
                                <br><small class="text-muted">{{ $item->published_at->diffForHumans() }}</small>
                            @endif
                        @elseif($item->publish_status === 'failed')
                            <span class="badge bg-danger" title="Publish Error"><i class="fas fa-times-circle"></i> Failed</span>
                        @else
                            <span class="badge bg-secondary"><i class="fas fa-clock"></i> Pending</span>
                        @endif

                        @if($item->publish_log)
                            <button type="button" class="btn btn-xs btn-link p-0 d-block text-left" data-bs-toggle="modal" data-bs-target="#logModal{{ $item->id }}">
                                <small>View Log</small>
                            </button>
                            <!-- Log Modal -->
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

                                <!-- Auto Publish Button -->
                                <form action="{{ route('admin.domain.publish-config', $item->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success text-white m-1" title="Publish Nginx/Apache Config Ke Server" onclick="return confirm('Publish konfigurasi {{ $item->webserver_type }} untuk {{ $item->name }} ke {{ $item->server ? $item->server->name : 'Server Lokal' }}?')">
                                        <i class="fas fa-paper-plane"></i> Publish
                                    </button>
                                </form>

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
    {{ $domains->appends(['perPage' => $perPage, 'search' => $search])->links('vendor.pagination.mobile') }}

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
    </script>

</x-admin-table>
