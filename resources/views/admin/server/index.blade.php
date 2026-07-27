<x-admin-table>

    <!-- Title -->
    <x-slot name="title">
        Server & SSH Target
    </x-slot>

    <!-- Button Form Create -->
    <x-slot name="formCreate">
        @can('create:server')
            @include('admin.server.create')
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
                <th>{{ __('Nama Server') }}</th>
                <th>{{ __('Tipe Server') }}</th>
                <th>{{ __('Host / SSH') }}</th>
                <th>{{ __('Webserver') }}</th>
                <th>{{ __('Path Config') }}</th>
                <th>{{ __('Status') }}</th>
                @canany(['edit:server', 'delete:server'])
                    <th class="text-center">{{ __('Action') }}</th>
                @endcanany
            </tr>
        </thead>
        <tbody>
            @foreach ($servers as $item)
                <tr>
                    <td>{{ $servers->firstItem() + $loop->index }}</td>
                    <td>
                        <strong>{{ $item->name }}</strong>
                    </td>
                    <td>
                        @if($item->type === 'ssh')
                            <span class="badge bg-purple"><i class="fas fa-terminal"></i> Remote SSH</span>
                        @else
                            <span class="badge bg-info"><i class="fas fa-server"></i> Server Lokal</span>
                        @endif
                    </td>
                    <td>
                        @if($item->type === 'ssh')
                            <code>{{ $item->username }}@${{ $item->host }}:{{ $item->port }}</code>
                        @else
                            <span class="text-muted">localhost (Server Ini)</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $item->webserver_type === 'apache' ? 'bg-warning' : 'bg-success' }}">
                            {{ strtoupper($item->webserver_type) }}
                        </span>
                    </td>
                    <td>
                        <small><code>{{ $item->config_path }}</code></small>
                    </td>
                    <td>
                        <form action="{{ route('admin.server.toggle-status', $item->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <label class="toggle-switch mb-0" title="{{ $item->status ? 'aktif' : 'tidak aktif' }}">
                                <input type="checkbox" {{ $item->status ? 'checked' : '' }}
                                    onchange="this.form.submit()" @cannot('edit:server') disabled @endcannot>
                                <span class="toggle-slider"></span>
                            </label>
                        </form>
                    </td>
                    @canany(['edit:server', 'delete:server'])
                        <td class="manage-row text-center">
                            @if($item->type === 'ssh')
                                <form action="{{ route('admin.server.test-connection', $item->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-info text-white m-1" title="Test Connection SSH">
                                        <i class="fas fa-plug"></i> Test
                                    </button>
                                </form>
                            @endif
                            @can('edit:server')
                                @include('admin.server.edit')
                            @endcan
                            @can('delete:server')
                                @include('admin.server.delete')
                            @endcan
                        </td>
                    @endcanany
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $servers->appends(['perPage' => $perPage, 'search' => $search])->links('vendor.pagination.mobile') }}

</x-admin-table>
