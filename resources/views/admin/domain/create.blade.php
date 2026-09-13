<!-- Button to open modal -->
<button role="button" class="btn btn-sm m-1 btn-primary" data-bs-toggle="modal" data-bs-target=".formCreateDomain"><i
        class="fas fa-plus"></i><span class="d-none d-sm-inline"> {{ __('Tambah Domain / Wildcard') }}</span></button>

<!-- Modal -->
<div class="modal fade formCreateDomain" tabindex="-1" role="dialog" aria-labelledby="modalDomainLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
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
                                <input class="form-check-input" type="checkbox" name="is_wildcard" id="is_wildcard_create" value="1">
                                <label class="form-check-label font-weight-bold" for="is_wildcard_create">
                                    Aktifkan Wildcard Domain Cloudflare (Otomatis match <code>*.domain.com</code> dan <code>domain.com</code>)
                                </label>
                            </div>
                        </div>

                        <!-- Target Server Selection -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Target Server (SSH Remote)') }}<span class="text-danger">*</span></label>
                            @if($servers->isEmpty())
                                <div class="alert alert-warning mb-0 py-2">
                                    <i class="fas fa-exclamation-triangle"></i> Belum ada Server SSH. <a href="{{ route('admin.server.index') }}" target="_blank">Tambah Server SSH dulu</a>.
                                </div>
                            @else
                                <select class="form-select" name="server_id" required>
                                    @foreach($servers as $srv)
                                        <option value="{{ $srv->id }}">{{ $srv->name }} ({{ strtoupper($srv->type) }} - {{ $srv->host ?: 'Local' }})</option>
                                    @endforeach
                                </select>
                            @endif
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
                            <select class="form-select" name="target_type" id="target_type_create" required onchange="toggleTargetFields(this.value)">
                                <option value="proxy">Reverse Proxy (e.g. http://127.0.0.1:8000)</option>
                                <option value="webroot">Web Root / Directory (e.g. /var/www/html)</option>
                                <option value="laravel">Laravel Project (e.g. /var/www/app/public)</option>
                                <option value="wordpress">WordPress Project (e.g. /var/www/wordpress)</option>
                                <option value="redirect">Redirect ke Domain Lain</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3" id="target_destination_group">
                            <label class="form-label">{{ __('Target Destination') }}</label>
                            <input type="text" class="form-control" name="target_destination" id="target_destination_create" placeholder="http://127.0.0.1:8000 atau /var/www/html">
                            <small class="text-muted">Jika dikosongkan, otomatis menggunakan IP Backend</small>
                        </div>
                        <div class="col-md-6 mb-3" id="redirect_url_group" style="display:none">
                            <label class="form-label">{{ __('Redirect Ke URL') }}<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="redirect_url" id="redirect_url_create" placeholder="https://example.com/new-page">
                            <small class="text-muted">Masukkan URL tujuan redirect (contoh: https://example.com/baru)</small>
                        </div>

                        <!-- SSL Config -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Mode SSL / HTTPS') }}<span class="text-danger">*</span></label>
                            <select class="form-select" name="ssl_type" required>
                                <option value="certbot" selected>Certbot Let's Encrypt (Port 443 SSL)</option>
                                <option value="cloudflare">Cloudflare Proxy (Port 80 HTTP + Real IP Header)</option>
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
                                <select class="form-select" name="custom_config_mode" id="custom_config_mode_create" onchange="toggleCustomConfig(this, 'custom_config_textarea_create')">
                                    <option value="default">Default - Pakai config bawaan Nginx/Apache</option>
                                    <option value="replace">Replace - Timpa semua dengan custom directives</option>
                                    <option value="add">Add - Gabungkan config bawaan + custom directives</option>
                                </select>
                            </div>
                            <textarea class="form-control font-monospace" name="custom_nginx_config" id="custom_config_textarea_create" rows="3" placeholder="# Custom directives here... (hanya aktif jika mode Replace atau Add)" style="display:none"></textarea>
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

<script>
function toggleCustomConfig(select, textareaId) {
    document.getElementById(textareaId).style.display = select.value === 'default' ? 'none' : 'block';
}
function toggleTargetFields(targetType) {
    var destGroup = document.getElementById('target_destination_group');
    var redirectGroup = document.getElementById('redirect_url_group');
    var webserverGroup = document.querySelector('[name="webserver_type"]').closest('.col-md-6');
    var customConfigGroup = document.getElementById('custom_config_mode_create').closest('.col-md-12');
    var sslGroup = document.querySelector('[name="ssl_type"]').closest('.col-md-6');

    if (targetType === 'redirect') {
        destGroup.style.display = 'none';
        redirectGroup.style.display = 'block';
        webserverGroup.style.display = 'none';
        customConfigGroup.style.display = 'none';
    } else {
        destGroup.style.display = 'block';
        redirectGroup.style.display = 'none';
        webserverGroup.style.display = 'block';
        customConfigGroup.style.display = 'block';
    }
}
</script>
