<section class="screen {{ $activePage === 'accounts' ? 'active' : '' }}" id="screen-accounts">
            <div class="topbar center">
                <div>
                    <div class="page-title">Accounts</div>
                    <div class="page-subtitle">Manage dashboard users who operate companies, products, batches, QR codes, and reports.</div>
                </div>
                <div class="topbar-actions">
                    <button class="btn-learn" type="button" data-user-drawer-open data-user-form="create">
                        <svg width="14" height="14"><use href="#i-plus"></use></svg>
                        Create User
                    </button>
                    <a class="btn-learn" href="{{ route('active8.page', 'dashboard') }}">Dashboard</a>
                    <a class="btn-share" href="{{ route('active8.page', 'scan-logs') }}">
                        <svg width="14" height="14"><use href="#i-link"></use></svg>
                        Scan Logs
                    </a>
                </div>
            </div>

            <div class="company-page-layout settings-users-layout">
                <div>
                    @if (session('status'))
                        <div class="alert-success">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert-error">{{ $errors->first() }}</div>
                    @endif

                    <div class="company-detail-grid">
                        <div class="company-detail-card">
                            <div class="company-detail-label">Total Users</div>
                            <div class="company-detail-value">{{ number_format($userSummary['total']) }}</div>
                        </div>
                        <div class="company-detail-card">
                            <div class="company-detail-label">Active</div>
                            <div class="company-detail-value">{{ number_format($userSummary['active']) }}</div>
                        </div>
                        <div class="company-detail-card">
                            <div class="company-detail-label">Owners</div>
                            <div class="company-detail-value">{{ number_format($userSummary['owners']) }}</div>
                        </div>
                        <div class="company-detail-card">
                            <div class="company-detail-label">Operators</div>
                            <div class="company-detail-value">{{ number_format($userSummary['operators']) }}</div>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="admin-card-header">
                            <div>
                                <div class="admin-card-title">Dashboard Users</div>
                                <div class="admin-card-sub">Owner has full access. Admin manages operations. Operator handles products, batches, and scan review.</div>
                            </div>
                            <button class="btn-share" type="button" data-user-drawer-open data-user-form="create">
                                <svg width="14" height="14"><use href="#i-plus"></use></svg>
                                Create User
                            </button>
                        </div>

                        <div class="table-toolbar" data-user-table-toolbar>
                            <label class="table-search" aria-label="Search users">
                                <svg width="14" height="14"><use href="#i-search"></use></svg>
                                <input type="search" data-user-search placeholder="Search name or email">
                            </label>
                            <div class="table-filter-row">
                                <select class="font-select" data-user-role-filter aria-label="Filter by role">
                                    <option value="all">All roles</option>
                                    <option value="owner">Owner</option>
                                    <option value="admin">Admin</option>
                                    <option value="operator">Operator</option>
                                </select>
                                <select class="font-select" data-user-status-filter aria-label="Filter by status">
                                    <option value="all">All statuses</option>
                                    <option value="active">Active</option>
                                    <option value="invited">Invited</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                            </div>
                        </div>

                        <form class="table-bulk-bar" method="POST" action="{{ route('users.bulk') }}" data-bulk-form data-confirm-delete data-delete-name="selected users" data-delete-match="DELETE">
                            @csrf
                            <div>
                                <div class="table-bulk-count" data-bulk-count>0 selected</div>
                                <div class="company-sub-name">Apply changes to selected users.</div>
                            </div>
                            <div class="table-bulk-actions">
                                <select class="font-select" name="action" required>
                                    <option value="activate">Activate</option>
                                    <option value="suspend">Suspend</option>
                                    <option value="delete">Delete</option>
                                </select>
                                <div data-bulk-inputs></div>
                                <button class="btn-share" type="submit">Apply</button>
                            </div>
                        </form>

                        <div class="admin-table-wrap">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th class="fit"><input class="row-check" type="checkbox" data-user-select-all aria-label="Select all visible users"></th>
                                        <th>User</th>
                                        <th class="fit">Role</th>
                                        <th class="fit">Status</th>
                                        <th>Last Login</th>
                                        <th class="fit">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($users as $user)
                                        <tr class="row-clickable" data-user-row data-user-search-text="{{ Str::lower($user->name.' '.$user->email) }}" data-user-role="{{ $user->role }}" data-user-status="{{ $user->status }}" data-user-id="{{ $user->id }}">
                                            <td class="fit">
                                                <input class="row-check" type="checkbox" value="{{ $user->id }}" data-user-select aria-label="Select {{ $user->name }}">
                                            </td>
                                            <td>
                                                <div class="company-name-cell">
                                                    <div class="company-logo-sm">{{ Str::of($user->name)->explode(' ')->filter()->map(fn ($word) => Str::substr($word, 0, 1))->take(2)->implode('') }}</div>
                                                    <div style="min-width:0">
                                                        <div class="company-main-name">{{ $user->name }}</div>
                                                        <div class="company-sub-name">{{ $user->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="fit">
                                                <span class="status-pill">{{ $user->role }}</span>
                                            </td>
                                            <td class="fit">
                                                <span class="status-pill {{ $user->status }}">{{ $user->status }}</span>
                                            </td>
                                            <td>
                                                <div class="muted-cell">{{ $user->last_login_at?->diffForHumans() ?? 'No login recorded' }}</div>
                                            </td>
                                            <td class="fit">
                                                <div class="table-actions">
                                                    <button class="icon-btn" type="button" data-user-drawer-open data-user-form="activity-{{ $user->id }}" aria-label="View {{ $user->name }} activity">
                                                        <svg width="15" height="15"><use href="#i-eye"></use></svg>
                                                    </button>
                                                    <button class="icon-btn" type="button" data-user-drawer-open data-user-form="edit-{{ $user->id }}" data-user-title="{{ $user->name }}" aria-label="Edit {{ $user->name }}">
                                                        <svg width="15" height="15"><use href="#i-edit"></use></svg>
                                                    </button>
                                                    <form method="POST" action="{{ route('users.destroy', $user) }}" data-confirm-delete data-delete-name="{{ $user->name }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="icon-btn del" type="submit" aria-label="Delete {{ $user->name }}">
                                                            <svg width="15" height="15"><use href="#i-trash"></use></svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr data-user-empty><td colspan="6">No dashboard users yet.</td></tr>
                                    @endforelse
                                    <tr class="filter-empty" data-user-no-results>
                                        <td colspan="6">
                                            <div class="filter-empty-title">No users match those filters.</div>
                                            <div class="filter-empty-copy">Try a different search, role, or status.</div>
                                            <button class="btn-learn" type="button" data-clear-user-filters>Clear filters</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="table-pagination" data-user-pagination>
                            <span data-user-page-summary>Showing users</span>
                            <div class="pager-actions">
                                <button class="pager-btn" type="button" data-user-page-prev aria-label="Previous user page">‹</button>
                                <span data-user-page-current>1</span>
                                <button class="pager-btn" type="button" data-user-page-next aria-label="Next user page">›</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="user-create-drawer" id="user-create-drawer" aria-hidden="true">
                <div class="drawer-backdrop" data-user-drawer-close></div>
                <div class="drawer-panel" role="dialog" aria-modal="true" aria-labelledby="user-drawer-title">
                    <div class="drawer-header">
                        <div>
                            <div class="admin-card-title" id="user-drawer-title">Create User</div>
                            <div class="admin-card-sub" id="user-drawer-subtitle">Add a dashboard user for your internal team.</div>
                        </div>
                        <button class="drawer-close" type="button" data-user-drawer-close aria-label="Close create user panel">×</button>
                    </div>
                    <div class="drawer-body">
                        <form class="drawer-user-form active" id="user-form-create" method="POST" action="{{ route('users.store') }}" data-form-title="Create User" data-form-subtitle="Add a dashboard user for your internal team.">
                            @csrf
                            <label class="form-label">Name</label>
                            <input class="input-field" name="name" value="{{ old('name') }}" placeholder="Operations Manager" required>
                            <label class="form-label">Email</label>
                            <input class="input-field" type="email" name="email" value="{{ old('email') }}" placeholder="ops@example.com" required>
                            <label class="form-label">Password</label>
                            <input class="input-field" type="password" name="password" placeholder="Minimum 8 characters" required>
                            <div class="form-row-2">
                                <div>
                                    <label class="form-label">Role</label>
                                    <select class="font-select" name="role" required data-role-select>
                                        <option value="operator" @selected(old('role') === 'operator')>Operator</option>
                                        <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                                        <option value="owner" @selected(old('role') === 'owner')>Owner</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Status</label>
                                    <select class="font-select" name="status" required>
                                        <option value="active" @selected(old('status') === 'active')>Active</option>
                                        <option value="invited" @selected(old('status') === 'invited')>Invited</option>
                                        <option value="suspended" @selected(old('status') === 'suspended')>Suspended</option>
                                    </select>
                                </div>
                            </div>
                            <div class="role-preview" data-role-preview></div>
                            <button class="btn-add-content" type="submit">Create User</button>
                        </form>

                        @foreach ($users as $user)
                            <form class="drawer-user-form" id="user-form-edit-{{ $user->id }}" method="POST" action="{{ route('users.update', $user) }}" data-form-title="Edit User" data-form-subtitle="Update {{ $user->name }}'s dashboard access.">
                                @csrf
                                @method('PUT')
                                <label class="form-label">Name</label>
                                <input class="input-field" name="name" value="{{ old('name', $user->name) }}" required>
                                <label class="form-label">Email</label>
                                <input class="input-field" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                                <label class="form-label">New Password</label>
                                <input class="input-field" type="password" name="password" placeholder="Leave blank to keep current password">
                                <div class="form-row-2">
                                    <div>
                                        <label class="form-label">Role</label>
                                        <select class="font-select" name="role" required data-role-select>
                                            @foreach (['owner', 'admin', 'operator'] as $role)
                                                <option value="{{ $role }}" @selected(old('role', $user->role) === $role)>{{ ucfirst($role) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label">Status</label>
                                        <select class="font-select" name="status" required>
                                            @foreach (['active', 'invited', 'suspended'] as $status)
                                                <option value="{{ $status }}" @selected(old('status', $user->status) === $status)>{{ ucfirst($status) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="role-preview" data-role-preview></div>
                                <button class="btn-add-content" type="submit">Save User</button>
                            </form>
                        @endforeach

                        @foreach ($users as $user)
                            <div class="drawer-user-form activity-panel" id="user-form-activity-{{ $user->id }}" data-form-title="User Activity" data-form-subtitle="Access details for {{ $user->name }}.">
                                <div class="activity-hero">
                                    <div class="company-logo-sm" style="width:48px;height:48px;font-size:16px">{{ Str::of($user->name)->explode(' ')->filter()->map(fn ($word) => Str::substr($word, 0, 1))->take(2)->implode('') }}</div>
                                    <div style="min-width:0">
                                        <div class="company-main-name">{{ $user->name }}</div>
                                        <div class="company-sub-name">{{ $user->email }}</div>
                                    </div>
                                </div>
                                <div class="activity-meta">
                                    <div class="activity-meta-row"><span>Role</span><strong>{{ ucfirst($user->role) }}</strong></div>
                                    <div class="activity-meta-row"><span>Status</span><strong>{{ ucfirst($user->status) }}</strong></div>
                                    <div class="activity-meta-row"><span>Last login</span><strong>{{ $user->last_login_at?->diffForHumans() ?? 'No login recorded' }}</strong></div>
                                    <div class="activity-meta-row"><span>Created</span><strong>{{ $user->created_at?->format('M j, Y') ?? 'Not recorded' }}</strong></div>
                                    <div class="activity-meta-row"><span>Updated</span><strong>{{ $user->updated_at?->diffForHumans() ?? 'Not recorded' }}</strong></div>
                                </div>
                                <div class="role-preview" data-static-role="{{ $user->role }}"></div>
                                <button class="btn-share" type="button" data-user-drawer-open data-user-form="edit-{{ $user->id }}" style="width:100%;justify-content:center">Edit User</button>
                            </div>
                        @endforeach

                        <div class="drawer-guide">
                            <div class="admin-card-title">Role Guide</div>
                            <div class="onboarding-steps" style="grid-template-columns:1fr;border:1px solid var(--gray-200);border-radius:var(--radius);margin:16px 0 0;overflow:hidden">
                                <div class="onboarding-step">
                                    <div class="step-num done">1</div>
                                    <div style="flex:1"><div class="step-title active" style="text-decoration:none;color:var(--gray-900)">Owner</div><div class="step-sub">Full access to users, settings, and operations.</div></div>
                                </div>
                                <div class="onboarding-step">
                                    <div class="step-num">2</div>
                                    <div style="flex:1"><div class="step-title active">Admin</div><div class="step-sub">Manage companies, products, batches, QR codes, and reports.</div></div>
                                </div>
                                <div class="onboarding-step">
                                    <div class="step-num">3</div>
                                    <div style="flex:1"><div class="step-title active">Operator</div><div class="step-sub">Handle products, batches, and scan log review.</div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
