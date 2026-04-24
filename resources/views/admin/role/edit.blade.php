<!-- Button to open modal -->
<button role="button" class="btn btn-sm m-1 btn-warning" data-bs-toggle="modal"
    data-bs-target=".formEdit{{ $item->id }}"><i class="fas fa-edit"></i><span class="d-none d-sm-inline">
        {{ __('Edit') }}</span></button>

<!-- Modal -->
<div class="modal fade formEdit{{ $item->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.role.update', $item->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Edit Data') }}</h5>
                    <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>{{ __('Name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                            value="{{ old('name', $item->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <label>{{ __('Permissions') }}</label>
                    <div class="row">
                        @php
                            $groupedPermissions = collect($permissions)->groupBy('entity');
                        @endphp

                        @foreach ($groupedPermissions as $entity => $entityPerms)
                            <div class="col-md-12 mb-3">
                                <div class="border rounded p-3">
                                    <h6 class="fw-bold text-capitalize mb-2">{{ str_replace('-', ' ', $entity) }}</h6>
                                    <div class="row">
                                        @foreach ($entityPerms as $perm)
                                            <div class="col-md-4 mb-2">
                                                <div
                                                    class="form-check border rounded px-3 py-2 d-flex align-items-center gap-2">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]"
                                                        value="{{ $perm->id }}"
                                                        {{ $item->permissions->contains('id', $perm->id) ? 'checked' : '' }}>
                                                    <span
                                                        class="badge {{ $perm->badgeClass }}">{{ $perm->action }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
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
