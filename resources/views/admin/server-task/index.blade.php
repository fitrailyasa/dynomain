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
                        <input type="hidden" name="task_type" value="custom" id="taskTypeInput">

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
                                <a class="nav-link active" data-bs-toggle="tab" href="#custom-tab" role="tab">
                                    <i class="fas fa-code"></i> Custom Command
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#clone-tab" role="tab">
                                    <i class="fab fa-github"></i> Clone Repo
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#install-tab" role="tab">
                                    <i class="fas fa-download"></i> Install Library
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#chmod-tab" role="tab">
                                    <i class="fas fa-lock"></i> Chmod
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#chown-tab" role="tab">
                                    <i class="fas fa-user-shield"></i> Chown
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content mt-3">
                            <!-- Custom Tab -->
                            <div class="tab-pane fade show active" id="custom-tab" role="tabpanel">

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

                            <!-- Clone Tab -->
                            <div class="tab-pane fade" id="clone-tab" role="tabpanel">

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

                            <!-- Chmod Tab -->
                            <div class="tab-pane fade" id="chmod-tab" role="tabpanel">

                                <div class="mb-3">
                                    <label class="form-label">{{ __('File/Folder Path') }}<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="chmod_path" value="/var/www" placeholder="/var/www/html" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('Permissions (octal)') }}<span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <select class="form-select" name="chmod_permissions" required>
                                            <option value="755">755 (rwxr-xr-x) - Folder default</option>
                                            <option value="644">644 (rw-r--r--) - File default</option>
                                            <option value="777">777 (rwxrwxrwx) - Full access</option>
                                            <option value="666">666 (rw-rw-rw-) - Read/write all</option>
                                            <option value="700">700 (rwx------) - Owner only</option>
                                            <option value="600">600 (rw-------) - Owner read/write</option>
                                            <option value="444">444 (r--r--r--) - Read only</option>
                                            <option value="400">400 (r--------) - Owner read only</option>
                                        </select>
                                    </div>
                                    <small class="text-muted">755 untuk folder, 644 untuk file</small>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="chmod_recursive" value="1" id="chmod_recursive">
                                        <label class="form-check-label" for="chmod_recursive">
                                            Recursive (-R) - Terapkan ke semua subfolder/file
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Chown Tab -->
                            <div class="tab-pane fade" id="chown-tab" role="tabpanel">

                                <div class="mb-3">
                                    <label class="form-label">{{ __('File/Folder Path') }}<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="chown_path" value="/var/www" placeholder="/var/www/html" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('Owner (username)') }}<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="chown_owner" value="www-data" placeholder="www-data" required>
                                    <small class="text-muted">User yang memiliki file/folder</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('Group (optional)') }}</label>
                                    <input type="text" class="form-control" name="chown_group" value="www-data" placeholder="www-data">
                                    <small class="text-muted">Kosongkan jika hanya ganti owner</small>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="chown_recursive" value="1" id="chown_recursive">
                                        <label class="form-check-label" for="chown_recursive">
                                            Recursive (-R) - Terapkan ke semua subfolder/file
                                        </label>
                                    </div>
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

                    <h6><i class="fas fa-lock text-warning"></i> Chmod (Permissions)</h6>
                    <ul class="small">
                        <li><strong>755:</strong> Folder default (rwxr-xr-x)</li>
                        <li><strong>644:</strong> File default (rw-r--r--)</li>
                        <li><strong>777:</strong> Full access (hati-hati!)</li>
                        <li><strong>600:</strong> Private (rw-------)</li>
                    </ul>

                    <hr>

                    <h6><i class="fas fa-user-shield text-info"></i> Chown (Ownership)</h6>
                    <ul class="small">
                        <li><strong>www-data:</strong> Web server (Nginx/Apache)</li>
                        <li><strong>root:</strong> Super admin</li>
                        <li>Contoh: <code>/var/www/html www-data www-data</code></li>
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
            var target = e.target.getAttribute('href').replace('#', '').replace('-tab', '');
            document.getElementById('taskTypeInput').value = target;
        });
    });
    </script>
</x-admin-layout>
