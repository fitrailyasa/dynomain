<!-- Button to open modal -->
<button role="button" class="btn btn-sm m-1 btn-primary" data-bs-toggle="modal" data-bs-target=".formCreateSubdomain"><i
        class="fas fa-plus"></i></button>

<!-- Modal -->
<div class="modal fade formCreateSubdomain" tabindex="-1" role="dialog" aria-labelledby="modalSubdomainLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
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
                            <select class="form-select" name="target_type" id="target_type_subdomain_create" required onchange="toggleTargetFieldsSubdomainCreate(this.value)">
                                <option value="proxy">Reverse Proxy (e.g. http://127.0.0.1:8000)</option>
                                <option value="webroot">Web Root / Directory (e.g. /var/www/html)</option>
                                <option value="laravel">Laravel Project (e.g. /var/www/app/public)</option>
                                <option value="wordpress">WordPress Project (e.g. /var/www/wordpress)</option>
                                <option value="codeigniter">CodeIgniter Project (e.g. /var/www/html)</option>
                                <option value="react">React SPA (e.g. /var/www/html/dist)</option>
                                <option value="vue">Vue SPA (e.g. /var/www/html/dist)</option>
                                <option value="next">Next.js SSR (Proxy to port 3000)</option>
                                <option value="nuxt">Nuxt.js SSR (Proxy to port 3000)</option>
                                <option value="redirect">Redirect ke Domain Lain</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3" id="target_destination_group_subdomain_create">
                            <label class="form-label">{{ __('Target Destination') }}</label>
                            <input type="text" class="form-control" name="target_destination" id="target_destination_subdomain_create" placeholder="http://127.0.0.1:8000 atau /var/www/html">
                        </div>
                        <div class="col-md-6 mb-3" id="redirect_url_group_subdomain_create" style="display:none">
                            <label class="form-label">{{ __('Redirect Ke URL') }}<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="redirect_url" id="redirect_url_subdomain_create" placeholder="https://example.com/new-page">
                            <small class="text-muted">Masukkan URL tujuan redirect</small>
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
                            <label class="form-label">{{ __('Status Subdomain') }}<span class="text-danger">*</span></label>
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
                                <select class="form-select" name="custom_config_mode" id="custom_config_mode_subdomain_create" onchange="toggleCustomConfig(this, 'custom_config_textarea_subdomain_create')">
                                    <option value="default">Default - Pakai config bawaan Nginx/Apache</option>
                                    <option value="replace">Replace - Timpa semua dengan custom directives</option>
                                    <option value="add">Add - Gabungkan config bawaan + custom directives</option>
                                </select>
                            </div>
                            <textarea class="form-control font-monospace" name="custom_nginx_config" id="custom_config_textarea_subdomain_create" rows="3" placeholder="# Custom rules... (hanya aktif jika mode Replace atau Add)" style="display:none"></textarea>
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
function toggleTargetFieldsSubdomainCreate(targetType) {
    var destGroup = document.getElementById('target_destination_group_subdomain_create');
    var redirectGroup = document.getElementById('redirect_url_group_subdomain_create');
    var webserverGroup = document.querySelector('[name="webserver_type"]').closest('.col-md-6');
    var customConfigGroup = document.getElementById('custom_config_mode_subdomain_create').closest('.col-md-12');

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
