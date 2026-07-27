<!-- Button Delete -->
<button role="button" class="btn btn-sm m-1 btn-danger" data-bs-toggle="modal" data-bs-target="#deleteServerModal{{ $item->id }}">
    <i class="fas fa-trash"></i>
</button>

<!-- Modal Delete -->
<div class="modal fade" id="deleteServerModal{{ $item->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.server.destroy', $item->id) }}">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Hapus Server') }}</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-left">
                    Apakah Anda yakin ingin menghapus server <strong>{{ $item->name }}</strong>?
                </div>
                <div class="modal-footer">
                    <x-button.close />
                    <button type="submit" class="btn btn-danger btn-sm">{{ __('Hapus') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
