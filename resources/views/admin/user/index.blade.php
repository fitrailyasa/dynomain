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
    </x-slot>

    <!-- Table -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
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

</x-admin-table>
