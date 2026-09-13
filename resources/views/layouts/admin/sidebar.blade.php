<!-- Main Sidebar Container -->
<aside class="main-sidebar elevation-4">
    <!-- Brand Logo -->
    <a href="#" class="brand-link border-bottom text-center">
        <span class="brand-text font-weight-bold text-white">{{ strtoupper(config('app.name')) }}</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}"
                        class="nav-link text-white {{ Request::routeIs('admin.dashboard') ? 'aktif' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Auth -->
                @canany(['view:user', 'view:role'])
                    <li class="nav-header text-white">Auth</li>
                @endcanany

                @can('view:user')
                    <li class="nav-item">
                        <a href="{{ route('admin.user.index') }}"
                            class="nav-link text-white {{ Request::routeIs('admin.user.index') ? 'aktif' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>User</p>
                        </a>
                    </li>
                @endcan

                @can('view:role')
                    <li class="nav-item">
                        <a href="{{ route('admin.role.index') }}"
                            class="nav-link text-white {{ Request::routeIs('admin.role.index') ? 'aktif' : '' }}">
                            <i class="nav-icon fas fa-key"></i>
                            <p>Role</p>
                        </a>
                    </li>
                @endcan

                <li class="nav-header text-white">Webserver & DNS</li>

                @can('view:server')
                    <li class="nav-item">
                        <a href="{{ route('admin.server.index') }}"
                            class="nav-link text-white {{ Request::routeIs('admin.server.index') ? 'aktif' : '' }}">
                            <i class="nav-icon fas fa-server"></i>
                            <p>Server & SSH</p>
                        </a>
                    </li>
                @endcan

                @can('view:github-ssh')
                    <li class="nav-item">
                        <a href="{{ route('admin.github-ssh.index') }}"
                            class="nav-link text-white {{ Request::routeIs('admin.github-ssh.index') ? 'aktif' : '' }}">
                            <i class="nav-icon fab fa-github"></i>
                            <p>GitHub SSH</p>
                        </a>
                    </li>
                @endcan

                <li class="nav-item">
                    <a href="{{ route('admin.server-task.index') }}"
                        class="nav-link text-white {{ Request::routeIs('admin.server-task.*') ? 'aktif' : '' }}">
                        <i class="nav-icon fas fa-terminal"></i>
                        <p>Server Task</p>
                    </a>
                </li>

                @can('view:domain')
                    <li class="nav-item">
                        <a href="{{ route('admin.domain.index') }}"
                            class="nav-link text-white {{ Request::routeIs('admin.domain.index') ? 'aktif' : '' }}">
                            <i class="nav-icon fas fa-globe"></i>
                            <p>Domain</p>
                        </a>
                    </li>
                @endcan

                @can('view:subdomain')
                    <li class="nav-item">
                        <a href="{{ route('admin.subdomain.index') }}"
                            class="nav-link text-white {{ Request::routeIs('admin.subdomain.index') ? 'aktif' : '' }}">
                            <i class="nav-icon fas fa-sitemap"></i>
                            <p>Subdomain</p>
                        </a>
                    </li>
                @endcan

                <!-- Setting -->
                <li class="nav-header text-white">Setting</li>
                <li class="nav-item">
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" hidden>
                        @csrf
                    </form>
                    <a href="#" class="nav-link text-white"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>Logout</p>
                    </a>
                </li>
            </ul>

        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
