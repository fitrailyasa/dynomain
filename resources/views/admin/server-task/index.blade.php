<x-admin-layout>
    <x-slot name="title">Server Task Runner</x-slot>

    <div class="row">
        <!-- Task Form -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-terminal"></i> Server Task Runner</h3>
                </div>
                <div class="card-body">
                    @include('components.alert')

                    <form method="POST" action="{{ route('admin.server-task.execute') }}" id="taskForm">
                        @csrf

                        <!-- Server Selection -->
                        <div class="mb-3">
                            <label class="form-label">{{ __('Pilih Server SSH') }}<span class="text-danger">*</span></label>
                            @if($servers->isEmpty())
                                <div class="alert alert-warning mb-0">
                                    <i class="fas fa-exclamation-triangle"></i> Belum ada Server SSH aktif. <a href="{{ route('admin.server.index') }}" target="_blank">Tambah Server SSH dulu</a>.
                                </div>
                            @else
                                <select class="form-select" name="server_id" required>
                                    <option value="">Pilih Server</option>
                                    @foreach($servers as $srv)
                                        <option value="{{ $srv->id }}">{{ $srv->name }} ({{ $srv->host }})</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <!-- Task Type Tabs -->
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#clone-tab" role="tab">
                                    <i class="fab fa-github"></i> Clone Repo
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#install-tab" role="tab">
                                    <i class="fas fa-download"></i> Install Library
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#custom-tab" role="tab">
                                    <i class="fas fa-code"></i> Custom Command
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content mt-3">
                            <!-- Clone Tab -->
                            <div class="tab-pane fade show active" id="clone-tab" role="tabpanel">
                                <input type="hidden" name="task_type" value="clone" id="task_type_clone">

                                <div class="mb-3">
                                    <label class="form-label">{{ __('GitHub SSH (PAT)') }}</label>
                                    <select class="form-select" name="github_ssh_id" id="github_ssh_id">
                                        <option value="">Tidak pakai PAT (public repo)</option>
                                        @foreach($githubSsh as $gh)
                                            <option value="{{ $gh->id }}">{{ $gh->name }} ({{ $gh->username }})</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Pilih jika repo private, atau kosongkan untuk public repo</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('Repository URL') }}<span class="text-danger">*</span></label>
                                    <input type="url" class="form-control" name="repo_url" placeholder="https://github.com/user/repo.git" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('Destination Directory') }}</label>
                                    <input type="text" class="form-control" name="destination" value="/var/www" placeholder="/var/www">
                                    <small class="text-muted">Direktori tujuan clone</small>
                                </div>
                            </div>

                            <!-- Install Tab -->
                            <div class="tab-pane fade" id="install-tab" role="tabpanel">
                                <input type="hidden" name="task_type" value="install" id="task_type_install">

                                <div class="mb-3">
                                    <label class="form-label">{{ __('Package Manager') }}<span class="text-danger">*</span></label>
                                    <select class="form-select" name="package_manager" required>
                                        <option value="apt">APT (Ubuntu/Debian)</option>
                                        <option value="npm">NPM (Node.js)</option>
                                        <option value="composer">Composer (PHP)</option>
                                        <option value="pip">PIP (Python)</option>
                                        <option value="yarn">Yarn (Node.js)</option>
                                        <option value="pnpm">PNPM (Node.js)</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('Packages / Libraries') }}<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="packages" placeholder="nginx git curl (space separated)" required>
                                    <small class="text-muted">Pisahkan dengan spasi jika multiple packages</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('Working Directory') }}</label>
                                    <input type="text" class="form-control" name="working_dir" value="/var/www" placeholder="/var/www">
                                </div>
                            </div>

                            <!-- Custom Tab -->
                            <div class="tab-pane fade" id="custom-tab" role="tabpanel">
                                <input type="hidden" name="task_type" value="custom" id="task_type_custom">

                                <div class="mb-3">
                                    <label class="form-label">{{ __('Custom Command') }}<span class="text-danger">*</span></label>
                                    <textarea class="form-control font-monospace" name="command" rows="4" placeholder="sudo systemctl restart nginx" required></textarea>
                                    <small class="text-muted">Masukkan command yang ingin dijalankan</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('Working Directory') }}</label>
                                    <input type="text" class="form-control" name="working_dir" value="/var/www" placeholder="/var/www">
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary" id="executeBtn" {{ $servers->isEmpty() ? 'disabled' : '' }}>
                                <i class="fas fa-play"></i> Execute Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Panel -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Info</h3>
                </div>
                <div class="card-body">
                    <h6><i class="fab fa-github text-dark"></i> Clone Repo</h6>
                    <ul class="small">
                        <li>Public repo: langsung isi URL</li>
                        <li>Private repo: pilih GitHub SSH PAT</li>
                        <li>Contoh: <code>https://github.com/user/repo.git</code></li>
                    </ul>

                    <hr>

                    <h6><i class="fas fa-download text-primary"></i> Install Library</h6>
                    <ul class="small">
                        <li><strong>APT:</strong> <code>nginx git curl</code></li>
                        <li><strong>NPM:</strong> <code>axios laravel-mix</code></li>
                        <li><strong>Composer:</strong> <code>laravel/framework</code></li>
                        <li><strong>PIP:</strong> <code>flask django</code></li>
                    </ul>

                    <hr>

                    <h6><i class="fas fa-code text-success"></i> Custom Command</h6>
                    <ul class="small">
                        <li><code>sudo systemctl restart nginx</code></li>
                        <li><code>sudo apt update && sudo apt upgrade</code></li>
                        <li><code>docker-compose up -d</code></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('executeBtn')?.addEventListener('click', function() {
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Running...';
        document.getElementById('taskForm').submit();
    });

    // Update task_type based on active tab
    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(function(tab) {
        tab.addEventListener('shown.bs.tab', function(e) {
            var target = e.target.getAttribute('href').replace('#', '');
            document.getElementById('taskForm').querySelector('[name="task_type"]').value = target.replace('-tab', '');
        });
    });
    </script>
</x-admin-layout>
