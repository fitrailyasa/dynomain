<x-admin-table>

    <!-- Title -->
    <x-slot name="title">
        Subdomain
    </x-slot>

    <!-- Button Form Create -->
    <x-slot name="formCreate">
        @can('create:subdomain')
            @include('admin.subdomain.create')
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
                <th>{{ __('Name') }}</th>
                <th>{{ __('IP') }}</th>
                <th>{{ __('Domain') }}</th>
                <th>{{ __('Status') }}</th>
                @canany(['edit:subdomain', 'delete:subdomain'])
                    <th class="text-center">{{ __('Action') }}</th>
                @endcanany
            </tr>
        </thead>
        <tbody>
            @foreach ($subdomains as $item)
                <tr>
                    <td>{{ $subdomains->firstItem() + $loop->index }}</td>
                    <td>
                        {{ $item->name ?? '-' }}
                    </td>
                    <td>
                        {{ $item->ip ?? '-' }}
                    </td>
                    <td>
                        {{ $item->domain->name ?? '-' }}
                    </td>
                    <td>
                        @if ($item->status)
                            <span class="badge badge-success">aktif</span>
                        @else
                            <span class="badge badge-danger">tidak aktif</span>
                        @endif
                    </td>
                    @canany(['edit:subdomain', 'delete:subdomain'])
                        <td class="manage-row text-center">
                            @can('edit:subdomain')
                                @include('admin.subdomain.edit')
                            @endcan
                            @can('delete:subdomain')
                                @include('admin.subdomain.delete')
                            @endcan
                        </td>
                    @endcanany
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $subdomains->appends(['perPage' => $perPage, 'search' => $search])->links('vendor.pagination.mobile') }}

</x-admin-table>
