<!-- Button to open modal -->
<button role="button" class="btn btn-sm m-1 btn-primary" data-bs-toggle="modal" data-bs-target=".formCreateServer"><i
        class="fas fa-plus"></i></button>

<!-- Modal -->
<div class="modal fade formCreateServer" tabindex="-1" role="dialog" aria-labelledby="modalServerLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.server.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalServerLabel">{{ __('Tambah Server Target / SSH') }}</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-left">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Nama Server') }}<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" placeholder="e.g. VPS Singapore Cloudflare" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Tipe Server') }}<span class="text-danger">*</span></label>
                            <select class="form-select" name="type" id="server_type_create" onchange="toggleSshFieldsCreate(this.value)" required>
                                <option value="local">Server Lokal (Server Ini / Localhost)</option>
                                <option value="ssh">Remote Server via SSH</option>
                            </select>
                        </div>

                        <!-- SSH Section -->
                        <div id="ssh_section_create" class="row w-100 m-0 p-0" style="display: none;">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">{{ __('Host / IP SSH') }}</label>
                                <input type="text" class="form-control" name="host" placeholder="103.x.x.x atau domain.com">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('Port SSH') }}</label>
                                <input type="number" class="form-control" name="port" value="22">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Username SSH') }}</label>
                                <input type="text" class="form-control" name="username" placeholder="root / ubuntu">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Autentikasi SSH') }}</label>
                                <select class="form-select" name="auth_type" id="auth_type_create" onchange="toggleAuthFieldsCreate(this.value)">
                                    <option value="password">Password SSH</option>
                                    <option value="key">Private Key SSH</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3" id="password_field_create">
                                <label class="form-label">{{ __('Password SSH') }}</label>
                                <input type="password" class="form-control" name="password" placeholder="Password SSH">
                            </div>
                            <div class="col-md-12 mb-3" id="key_field_create" style="display: none;">
                                <label class="form-label">{{ __('Private Key SSH (PEM/RSA)') }}</label>
                                <textarea class="form-control font-monospace" name="private_key" rows="4" placeholder="-----BEGIN OPENSSH PRIVATE KEY----- ..."></textarea>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Status Server') }}<span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
                            </select>
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
    function toggleSshFieldsCreate(type) {
        document.getElementById('ssh_section_create').style.display = (type === 'ssh') ? 'flex' : 'none';
    }
    function toggleAuthFieldsCreate(authType) {
        if (authType === 'key') {
            document.getElementById('password_field_create').style.display = 'none';
            document.getElementById('key_field_create').style.display = 'block';
        } else {
            document.getElementById('password_field_create').style.display = 'block';
            document.getElementById('key_field_create').style.display = 'none';
        }
    }
</script>
