<x-admin-table>

    <!-- Title -->
    <x-slot name="title">
        Domain
    </x-slot>

    <!-- Button Form Create -->
    <x-slot name="formCreate">
        @can('create:domain')
            @include('admin.domain.create')
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
                <th>{{ __('Status') }}</th>
                @canany(['edit:domain', 'delete:domain'])
                    <th class="text-center">{{ __('Action') }}</th>
                @endcanany
            </tr>
        </thead>
        <tbody>
            @foreach ($domains as $item)
                <tr>
                    <td>{{ $domains->firstItem() + $loop->index }}</td>
                    <td>
                        {{ $item->name ?? '-' }}
                    </td>
                    <td>
                        {{ $item->ip ?? '-' }}
                    </td>
                    <td>
                        @if ($item->status)
                            <span class="badge badge-success">aktif</span>
                        @else
                            <span class="badge badge-danger">tidak aktif</span>
                        @endif
                    </td>
                    @canany(['edit:domain', 'delete:domain'])
                        <td class="manage-row text-center">
                            @can('edit:domain')
                                @include('admin.domain.edit')
                            @endcan
                            @can('delete:domain')
                                @include('admin.domain.delete')
                            @endcan
                        </td>
                    @endcanany
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $domains->appends(['perPage' => $perPage, 'search' => $search])->links('vendor.pagination.mobile') }}

</x-admin-table>
