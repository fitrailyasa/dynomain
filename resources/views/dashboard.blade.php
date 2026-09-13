<x-admin-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalDomains ?? 0 }}</h3>
                    <p>Domain</p>
                </div>
                <div class="icon">
                    <i class="fas fa-globe"></i>
                </div>
                <a href="{{ route('admin.domain.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $totalSubdomains ?? 0 }}</h3>
                    <p>Subdomain</p>
                </div>
                <div class="icon">
                    <i class="fas fa-sitemap"></i>
                </div>
                <a href="{{ route('admin.subdomain.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $totalServers ?? 0 }}</h3>
                    <p>Server SSH</p>
                </div>
                <div class="icon">
                    <i class="fas fa-server"></i>
                </div>
                <a href="{{ route('admin.server.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $totalUsers ?? 0 }}</h3>
                    <p>Users</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('admin.user.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-terminal"></i> Quick Actions</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.server-task.index') }}" class="btn btn-primary">
                        <i class="fas fa-terminal"></i> Server Task Runner
                    </a>
                    <a href="{{ route('admin.domain.index') }}" class="btn btn-success">
                        <i class="fas fa-globe"></i> Manage Domains
                    </a>
                    <a href="{{ route('admin.server.index') }}" class="btn btn-warning">
                        <i class="fas fa-server"></i> Manage Servers
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
