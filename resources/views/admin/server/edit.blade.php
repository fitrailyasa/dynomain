<!-- Button Edit -->
<button role="button" class="btn btn-sm m-1 btn-warning text-white" data-bs-toggle="modal" data-bs-target="#editServerModal{{ $item->id }}">
    <i class="fas fa-edit"></i>
</button>

<!-- Modal Edit -->
<div class="modal fade" id="editServerModal{{ $item->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.server.update', $item->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Edit Server Target / SSH') }}</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-left">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Nama Server') }}<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="{{ $item->name }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Tipe Server') }}<span class="text-danger">*</span></label>
                            <select class="form-select" name="type" id="server_type_edit_{{ $item->id }}" onchange="toggleSshFieldsEdit{{ $item->id }}(this.value)" required>
                                <option value="local" {{ $item->type === 'local' ? 'selected' : '' }}>Server Lokal (Server Ini / Localhost)</option>
                                <option value="ssh" {{ $item->type === 'ssh' ? 'selected' : '' }}>Remote Server via SSH</option>
                            </select>
                        </div>

                        <!-- SSH Section -->
                        <div id="ssh_section_edit_{{ $item->id }}" class="row w-100 m-0 p-0" style="display: {{ $item->type === 'ssh' ? 'flex' : 'none' }};">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">{{ __('Host / IP SSH') }}</label>
                                <input type="text" class="form-control" name="host" value="{{ $item->host }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('Port SSH') }}</label>
                                <input type="number" class="form-control" name="port" value="{{ $item->port ?: 22 }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Username SSH') }}</label>
                                <input type="text" class="form-control" name="username" value="{{ $item->username }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Autentikasi SSH') }}</label>
                                <select class="form-select" name="auth_type" id="auth_type_edit_{{ $item->id }}" onchange="toggleAuthFieldsEdit{{ $item->id }}(this.value)">
                                    <option value="password" {{ $item->auth_type === 'password' ? 'selected' : '' }}>Password SSH</option>
                                    <option value="key" {{ $item->auth_type === 'key' ? 'selected' : '' }}>Private Key SSH</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3" id="password_field_edit_{{ $item->id }}" style="display: {{ $item->auth_type === 'key' ? 'none' : 'block' }};">
                                <label class="form-label">{{ __('Password SSH (Kosongkan jika tidak diubah)') }}</label>
                                <input type="password" class="form-control" name="password" placeholder="••••••••">
                            </div>
                            <div class="col-md-12 mb-3" id="key_field_edit_{{ $item->id }}" style="display: {{ $item->auth_type === 'key' ? 'block' : 'none' }};">
                                <label class="form-label">{{ __('Private Key SSH (Kosongkan jika tidak diubah)') }}</label>
                                <textarea class="form-control font-monospace" name="private_key" rows="4" placeholder="-----BEGIN OPENSSH PRIVATE KEY----- ..."></textarea>
                            </div>
                        </div>

                        <!-- Webserver Section -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Tipe Webserver Target') }}<span class="text-danger">*</span></label>
                            <select class="form-select" name="webserver_type" required>
                                <option value="nginx" {{ $item->webserver_type === 'nginx' ? 'selected' : '' }}>Nginx</option>
                                <option value="apache" {{ $item->webserver_type === 'apache' ? 'selected' : '' }}>Apache</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Status Server') }}<span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="1" {{ $item->status ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ !$item->status ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Directory Config Webserver') }}<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="config_path" value="{{ $item->config_path }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Directory Symlink (Sites-Enabled)') }}</label>
                            <input type="text" class="form-control" name="symlink_path" value="{{ $item->symlink_path }}">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">{{ __('Command Reload Webserver') }}<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="reload_command" value="{{ $item->reload_command }}" required>
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
    function toggleSshFieldsEdit{{ $item->id }}(type) {
        document.getElementById('ssh_section_edit_{{ $item->id }}').style.display = (type === 'ssh') ? 'flex' : 'none';
    }
    function toggleAuthFieldsEdit{{ $item->id }}(authType) {
        if (authType === 'key') {
            document.getElementById('password_field_edit_{{ $item->id }}').style.display = 'none';
            document.getElementById('key_field_edit_{{ $item->id }}').style.display = 'block';
        } else {
            document.getElementById('password_field_edit_{{ $item->id }}').style.display = 'block';
            document.getElementById('key_field_edit_{{ $item->id }}').style.display = 'none';
        }
    }
</script>
