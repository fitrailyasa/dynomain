<!-- Button Edit -->
<button role="button" class="btn btn-sm m-1 btn-warning text-white" data-bs-toggle="modal" data-bs-target="#editGithubSshModal{{ $item->id }}">
    <i class="fas fa-edit"></i>
</button>

<!-- Modal Edit -->
<div class="modal fade" id="editGithubSshModal{{ $item->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.github-ssh.update', $item->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Edit GitHub SSH (PAT)') }}</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-left">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Nama') }}<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="{{ $item->name }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Username GitHub') }}<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="username" value="{{ $item->username }}" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">{{ __('Personal Access Token (PAT)') }}</label>
                            <input type="password" class="form-control" name="pat" placeholder="Kosongkan jika tidak diubah">
                            <small class="text-muted">Kosongkan jika tidak ingin mengubah PAT</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Status') }}<span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="1" {{ $item->status ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ !$item->status ? 'selected' : '' }}>Tidak Aktif</option>
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
