<!-- Button to open modal -->
<button role="button" class="btn btn-sm m-1 btn-primary" data-bs-toggle="modal" data-bs-target=".formCreateDomain"><i
        class="fas fa-plus"></i><span class="d-none d-sm-inline"> {{ __('Tambah Domain / Wildcard') }}</span></button>

<!-- Modal -->
<div class="modal fade formCreateDomain" tabindex="-1" role="dialog" aria-labelledby="modalDomainLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.domain.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDomainLabel">{{ __('Tambah Domain & Wildcard Cloudflare') }}</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-left">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Nama Domain') }}<span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                placeholder="e.g. example.com" name="name" id="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('IP Server / Backend') }}<span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('ip') is-invalid @enderror"
                                placeholder="e.g. 127.0.0.1" name="ip" id="ip" value="{{ old('ip') }}" required>
                            @error('ip')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Wildcard Option -->
                        <div class="col-md-12 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_wildcard" id="is_wildcard_create" value="1" checked>
                                <label class="form-check-label font-weight-bold" for="is_wildcard_create">
                                    Aktifkan Wildcard Domain Cloudflare (Otomatis match <code>*.domain.com</code> dan <code>domain.com</code>)
                                </label>
                            </div>
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
                            <small class="text-muted">Jika dikosongkan, otomatis menggunakan IP Backend</small>
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
                            <label class="form-label">{{ __('Status Domain') }}<span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
                            </select>
                        </div>

                        <!-- Custom Directives -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">{{ __('Custom Directives (Nginx / Apache Header/Rules)') }}</label>
                            <div class="input-group mb-2">
                                <span class="input-group-text"><i class="fas fa-cog"></i></span>
                                <select class="form-select" name="custom_config_mode" id="custom_config_mode_create">
                                    <option value="default">Default - Pakai config bawaan Nginx/Apache</option>
                                    <option value="replace">Replace - Timpa semua dengan custom directives</option>
                                    <option value="add">Add - Gabungkan config bawaan + custom directives</option>
                                </select>
                            </div>
                            <textarea class="form-control font-monospace" name="custom_nginx_config" rows="3" placeholder="# Custom directives here... (hanya aktif jika mode Replace atau Add)"></textarea>
                            <small class="text-muted">Pilih mode terlebih dahulu. <strong>Default</strong> = ignore custom directives. <strong>Replace</strong> = timpa semua. <strong>Add</strong> = gabungkan.</small>
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
