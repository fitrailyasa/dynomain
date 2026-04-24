<div class="p-1 mb-3">
    <form id="filterForm" method="GET" action="{{ url()->current() }}">
        <div class="row align-items-end g-2">

            <div class="col-md-1">
                <select name="perPage" class="form-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>

            <div class="col-md-3 ms-auto">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                        placeholder="Search...">
                    <button class="btn border" type="submit">Search</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        $('.select-search').select2({
            theme: 'bootstrap-5',
            width: '100%',
            allowClear: false
        });

        $('.select-search').on('change', function() {
            $('#filterForm').submit();
        });
    });
</script>
