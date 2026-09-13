<x-admin-table>

    <!-- Title -->
    <x-slot name="title">
        User
    </x-slot>

    <!-- Button Form Create -->
    <x-slot name="formCreate">
        @can('create:user')
            @include('admin.user.create')
        @endcan
    </x-slot>

    <!-- Search & Pagination -->
    <x-slot name="search">
        @include('components.search')
        @can('delete:user')
            <button type="button" class="btn btn-sm btn-danger ms-2" id="bulkDeleteBtn" disabled onclick="bulkDeleteConfirm('user')">
                <i class="fas fa-trash"></i> Delete Selected (<span id="selectedCount">0</span>)
            </button>
        @endcan
    </x-slot>

    <!-- Table -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                @can('delete:user')
                    <th class="text-center" style="width: 40px;">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this, 'user')">
                    </th>
                @endcan
                <th>{{ __('No') }}</th>
                <th>{{ __('Profile') }}</th>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Email') }}</th>
                <th>{{ __('Role') }}</th>
                <th>{{ __('Status') }}</th>
                @canany(['edit:user', 'delete:user'])
                    <th class="text-center">{{ __('Action') }}</th>
                @endcanany
            </tr>
        </thead>
        <tbody>
            @foreach ($users->where('email', '!=', 'super@admin.com') as $item)
                <tr>
                    @can('delete:user')
                        <td class="text-center">
                            <input type="checkbox" name="ids[]" value="{{ $item->id }}" class="item-checkbox user-checkbox" onchange="updateBulkBtn('user')">
                        </td>
                    @endcan
                    <td>{{ $users->firstItem() + $loop->index }}</td>
                    <td>
                        @if ($item->img == null)
                            <img src="{{ asset('assets/profile/default.png') }}" alt="{{ $item->name }}" width="100">
                        @else
                            <a href="#" data-bs-toggle="modal" data-bs-target="#myModal{{ $item->id }}">
                                <img class="img img-fluid rounded" src="{{ asset('storage/' . $item->img) }}"
                                    alt="{{ $item->img }}" width="100" loading="lazy">
                            </a>

                            <!-- Modal -->
                            <div class="modal fade" id="myModal{{ $item->id }}" tabindex="-1" role="dialog"
                                aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-body">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h3 class="card-title">{{ $item->name }}</h3>
                                                    <div class="card-tools">
                                                        <button type="button" class="btn btn-tool"
                                                            data-card-widget="maximize"><i
                                                                class="fas fa-expand"></i></button>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <img class="img img-fluid col-12"
                                                        src="{{ asset('storage/' . $item->img) }}"
                                                        alt="{{ $item->img }}">
                                                    <!-- Tombol Download -->
                                                    <a href="{{ asset('storage/' . $item->img) }}"
                                                        download="{{ $item->img }}"
                                                        class="btn btn-success mt-2 col-12">Download
                                                        Gambar</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </td>
                    <td>{{ $item->name ?? '-' }}</td>
                    <td>
                        <a href="mailto:{{ $item->email }}" target="_blank">{{ $item->email ?? '-' }}</a>
                    </td>
                    <td>
                        {{ $item->getRoleNames()->implode(', ') }}
                    </td>
                    <td>
                        <form action="{{ route('admin.user.toggle-status', $item->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <label class="toggle-switch mb-0" title="{{ $item->email_verified_at ? 'aktif' : 'tidak aktif' }}">
                                <input type="checkbox" {{ $item->email_verified_at ? 'checked' : '' }}
                                    onchange="this.form.submit()" @cannot('edit:user') disabled @endcannot>
                                <span class="toggle-slider"></span>
                            </label>
                        </form>
                    </td>
                    @canany(['edit:user', 'delete:user'])
                        <td class="manage-row text-center">
                            @can('edit:user')
                                @include('admin.user.edit')
                            @endcan
                            @can('delete:user')
                                @include('admin.user.delete')
                            @endcan
                        </td>
                    @endcanany
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $users->appends(['perPage' => $perPage, 'search' => $search])->links('vendor.pagination.mobile') }}

    <script>
        function toggleSelectAll(checkbox, entity) {
            document.querySelectorAll('.' + entity + '-checkbox').forEach(function(cb) {
                cb.checked = checkbox.checked;
            });
            updateBulkBtn(entity);
        }

        function updateBulkBtn(entity) {
            var checked = document.querySelectorAll('.' + entity + '-checkbox:checked').length;
            document.getElementById('selectedCount').textContent = checked;
            document.getElementById('bulkDeleteBtn').disabled = checked === 0;
        }

        function bulkDeleteConfirm(entity) {
            var checked = document.querySelectorAll('.' + entity + '-checkbox:checked');
            var ids = [];
            checked.forEach(function(cb) { ids.push(cb.value); });

            if (ids.length === 0) return;

            if (confirm('Hapus ' + ids.length + ' user yang dipilih?')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '/admin/' + entity + '/bulk-delete';

                var csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);

                var method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'DELETE';
                form.appendChild(method);

                ids.forEach(function(id) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    form.appendChild(input);
                });

                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>

</x-admin-table>
