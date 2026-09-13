<!-- Button to open modal -->
<button role="button" class="btn btn-sm m-1 btn-primary" data-bs-toggle="modal" data-bs-target=".formCreateGithubSsh"><i
        class="fas fa-plus"></i></button>

<!-- Modal -->
<div class="modal fade formCreateGithubSsh" tabindex="-1" role="dialog" aria-labelledby="modalGithubSshLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.github-ssh.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalGithubSshLabel">{{ __('Tambah GitHub SSH (PAT)') }}</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-left">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Nama') }}<span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                placeholder="e.g. My GitHub Account" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Username GitHub') }}<span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('username') is-invalid @enderror"
                                placeholder="e.g. johndoe" name="username" value="{{ old('username') }}" required>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">{{ __('Personal Access Token (PAT)') }}<span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('pat') is-invalid @enderror"
                                placeholder="ghp_xxxxxxxxxxxx" name="pat" value="{{ old('pat') }}" required>
                            @error('pat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Buat PAT di GitHub Settings > Developer settings > Personal access tokens</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Status') }}<span class="text-danger">*</span></label>
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
