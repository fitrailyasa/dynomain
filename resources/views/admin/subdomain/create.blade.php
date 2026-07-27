<!-- Button to open modal -->
<button role="button" class="btn btn-sm m-1 btn-primary" data-bs-toggle="modal" data-bs-target=".formCreateSubdomain"><i
        class="fas fa-plus"></i><span class="d-none d-sm-inline"> {{ __('Tambah Subdomain') }}</span></button>

<!-- Modal -->
<div class="modal fade formCreateSubdomain" tabindex="-1" role="dialog" aria-labelledby="modalSubdomainLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.subdomain.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalSubdomainLabel">{{ __('Tambah Subdomain Data') }}</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-left">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Domain Utamanya') }}<span class="text-danger">*</span></label>
                            <select class="form-select @error('domain_id') is-invalid @enderror" name="domain_id" id="domain_id" required>
                                <option value="">Pilih Domain Utama</option>
                                @foreach ($domains as $domain)
                                    <option value="{{ $domain->id }}" {{ old('domain_id') == $domain->id ? 'selected' : '' }}>
                                        {{ $domain->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('domain_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Nama Subdomain') }}<span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                placeholder="e.g. app atau api" name="name" id="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('IP Backend') }}<span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('ip') is-invalid @enderror"
                                placeholder="127.0.0.1" name="ip" id="ip" value="{{ old('ip') }}" required>
                            @error('ip')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Target Server Selection -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Target Server (Lokal / SSH Remote)') }}</label>
                            <select class="form-select" name="server_id">
                                <option value="">Server Lokal (Localhost / Server Ini)</option>
                                @foreach($servers as $srv)
                                    <option value="{{ $srv->id }}">{{ $srv->name }} ({{ strtoupper($srv->type) }} - {{ $srv->host ?: 'Local' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Webserver Type -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Tipe Webserver') }}<span class="text-danger">*</span></label>
                            <select class="form-select" name="webserver_type" required>
                                <option value="nginx">Nginx</option>
                                <option value="apache">Apache</option>
                            </select>
                        </div>

                        <!-- Target Type & Destination -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Tipe Routing Target') }}<span class="text-danger">*</span></label>
                            <select class="form-select" name="target_type" required>
                                <option value="proxy">Reverse Proxy (e.g. http://127.0.0.1:8000)</option>
                                <option value="webroot">Web Root / Directory (e.g. /var/www/html)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Target Destination') }}</label>
                            <input type="text" class="form-control" name="target_destination" placeholder="http://127.0.0.1:8000 atau /var/www/html">
                        </div>

                        <!-- SSL Config -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Mode SSL / HTTPS') }}<span class="text-danger">*</span></label>
                            <select class="form-select" name="ssl_type" required>
                                <option value="cloudflare">Cloudflare Proxy (Port 80 HTTP + Real IP Header)</option>
                                <option value="certbot">Certbot Let's Encrypt (Port 443 SSL)</option>
                                <option value="custom">Custom SSL Certificate</option>
                                <option value="none">Tidak Pakai SSL (HTTP Plain)</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Status Subdomain') }}<span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
                            </select>
                        </div>

                        <!-- Custom Directives -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">{{ __('Custom Directives') }}</label>
                            <textarea class="form-control font-monospace" name="custom_nginx_config" rows="3" placeholder="# Custom rules..."></textarea>
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
