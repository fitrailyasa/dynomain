<!-- Button Edit -->
<button role="button" class="btn btn-sm m-1 btn-warning text-white" data-bs-toggle="modal" data-bs-target="#editSubdomainModal{{ $item->id }}">
    <i class="fas fa-edit"></i>
</button>

<!-- Modal Edit -->
<div class="modal fade" id="editSubdomainModal{{ $item->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.subdomain.update', $item->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Edit Subdomain Data') }}</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-left">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Domain Utamanya') }}<span class="text-danger">*</span></label>
                            <select class="form-select" name="domain_id" required>
                                @foreach ($domains as $domain)
                                    <option value="{{ $domain->id }}" {{ $item->domain_id == $domain->id ? 'selected' : '' }}>
                                        {{ $domain->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Nama Subdomain') }}<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="{{ $item->name }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('IP Backend') }}<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="ip" value="{{ $item->ip }}" required>
                        </div>

                        <!-- Target Server Selection -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Target Server (Lokal / SSH Remote)') }}</label>
                            <select class="form-select" name="server_id">
                                <option value="">Server Lokal (Localhost / Server Ini)</option>
                                @foreach($servers as $srv)
                                    <option value="{{ $srv->id }}" {{ $item->server_id == $srv->id ? 'selected' : '' }}>
                                        {{ $srv->name }} ({{ strtoupper($srv->type) }} - {{ $srv->host ?: 'Local' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Webserver Type -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Tipe Webserver') }}<span class="text-danger">*</span></label>
                            <select class="form-select" name="webserver_type" required>
                                <option value="nginx" {{ $item->webserver_type === 'nginx' ? 'selected' : '' }}>Nginx</option>
                                <option value="apache" {{ $item->webserver_type === 'apache' ? 'selected' : '' }}>Apache</option>
                            </select>
                        </div>

                        <!-- Target Type & Destination -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Tipe Routing Target') }}<span class="text-danger">*</span></label>
                            <select class="form-select" name="target_type" required>
                                <option value="proxy" {{ $item->target_type === 'proxy' ? 'selected' : '' }}>Reverse Proxy (e.g. http://127.0.0.1:8000)</option>
                                <option value="webroot" {{ $item->target_type === 'webroot' ? 'selected' : '' }}>Web Root / Directory (e.g. /var/www/html)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Target Destination') }}</label>
                            <input type="text" class="form-control" name="target_destination" value="{{ $item->target_destination }}" placeholder="http://127.0.0.1:8000 atau /var/www/html">
                        </div>

                        <!-- SSL Config -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Mode SSL / HTTPS') }}<span class="text-danger">*</span></label>
                            <select class="form-select" name="ssl_type" required>
                                <option value="cloudflare" {{ $item->ssl_type === 'cloudflare' ? 'selected' : '' }}>Cloudflare Proxy (Port 80 HTTP + Real IP Header)</option>
                                <option value="certbot" {{ $item->ssl_type === 'certbot' ? 'selected' : '' }}>Certbot Let's Encrypt (Port 443 SSL)</option>
                                <option value="custom" {{ $item->ssl_type === 'custom' ? 'selected' : '' }}>Custom SSL Certificate</option>
                                <option value="none" {{ $item->ssl_type === 'none' ? 'selected' : '' }}>Tidak Pakai SSL (HTTP Plain)</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Status Subdomain') }}<span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="1" {{ $item->status ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ !$item->status ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                        </div>

                        <!-- Custom Directives -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">{{ __('Custom Directives') }}</label>
                            <textarea class="form-control font-monospace" name="custom_nginx_config" rows="3">{{ $item->custom_nginx_config }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <x-button.close />
                    <x-button.save />
                </div>
            </form>
        </div>
    </div>
</div>
