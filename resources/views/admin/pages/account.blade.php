<section class="screen {{ $activePage === 'account' ? 'active' : '' }}" id="screen-account">
            <div class="topbar center">
                <div>
                    <div class="page-title">Account Settings</div>
                    <div class="page-subtitle">Update your profile, password, and personal dashboard access details.</div>
                </div>
                <div class="topbar-actions">
                    <a class="btn-learn" href="{{ route('active8.page', 'dashboard') }}">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn-share" type="submit">
                            <svg width="14" height="14"><use href="#i-link"></use></svg>
                            Sign out
                        </button>
                    </form>
                </div>
            </div>

            <div class="company-page-layout">
                <div>
                    @if (session('status'))
                        <div class="alert-success">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert-error">{{ $errors->first() }}</div>
                    @endif

                    <div class="admin-card" style="padding:0;overflow:hidden">
                        <div class="tabs" style="padding:0 20px">
                            <button class="tab active" type="button" data-account-tab="profile">
                                <svg width="14" height="14"><use href="#i-user"></use></svg>
                                Profile
                            </button>
                            <button class="tab" type="button" data-account-tab="password">
                                <svg width="14" height="14"><use href="#i-settings"></use></svg>
                                Password
                            </button>
                            <button class="tab" type="button" data-account-tab="security">
                                <svg width="14" height="14"><use href="#i-activity"></use></svg>
                                Security
                            </button>
                        </div>

                        <div style="padding:24px">
                            <div class="account-page active" id="account-page-profile">
                                <div class="admin-card-header">
                                    <div>
                                        <div class="admin-card-title">Edit Profile</div>
                                        <div class="admin-card-sub">These details identify you inside the admin dashboard and activity views.</div>
                                    </div>
                                    <span class="status-pill {{ $authUser?->status }}">{{ $authUser?->status ?? 'active' }}</span>
                                </div>

                                <form method="POST" action="{{ route('account.profile.update') }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="form-row-2">
                                        <div>
                                            <label class="form-label">Full Name</label>
                                            <input class="input-field" name="name" value="{{ old('name', $authUser?->name) }}" required>
                                        </div>
                                        <div>
                                            <label class="form-label">Email Address</label>
                                            <input class="input-field" type="email" name="email" value="{{ old('email', $authUser?->email) }}" required>
                                        </div>
                                    </div>
                                    <button class="btn-add-content" type="submit">Save Profile</button>
                                </form>
                            </div>

                            <div class="account-page" id="account-page-password">
                                <div class="admin-card-header">
                                    <div>
                                        <div class="admin-card-title">Change Password</div>
                                        <div class="admin-card-sub">Use a strong password to keep QR, batch, and scan operations protected.</div>
                                    </div>
                                    <span class="status-pill active">Secure</span>
                                </div>

                                <form method="POST" action="{{ route('account.password.update') }}">
                                    @csrf
                                    @method('PUT')
                                    <label class="form-label">Current Password</label>
                                    <input class="input-field" type="password" name="current_password" autocomplete="current-password" required>
                                    <div class="form-row-2">
                                        <div>
                                            <label class="form-label">New Password</label>
                                            <input class="input-field" type="password" name="password" autocomplete="new-password" required>
                                        </div>
                                        <div>
                                            <label class="form-label">Confirm New Password</label>
                                            <input class="input-field" type="password" name="password_confirmation" autocomplete="new-password" required>
                                        </div>
                                    </div>
                                    <button class="btn-add-content" type="submit">Change Password</button>
                                </form>
                            </div>

                            <div class="account-page" id="account-page-security">
                                <div class="admin-card-header">
                                    <div>
                                        <div class="admin-card-title">Security Notes</div>
                                        <div class="admin-card-sub">A quick view of how your account access works in this V1 dashboard.</div>
                                    </div>
                                    <span class="status-pill active">{{ ucfirst($authUser?->role ?? 'operator') }}</span>
                                </div>
                                <div class="onboarding-steps" style="grid-template-columns:1fr;border:1px solid var(--gray-200);border-radius:var(--radius);overflow:hidden">
                                    <div class="onboarding-step">
                                        <div class="step-num done">1</div>
                                        <div style="flex:1"><div class="step-title active" style="text-decoration:none;color:var(--gray-900)">Private account</div><div class="step-sub">Your account controls dashboard access only. Customer scan pages remain public.</div></div>
                                    </div>
                                    <div class="onboarding-step">
                                        <div class="step-num">2</div>
                                        <div style="flex:1"><div class="step-title active">Password reset</div><div class="step-sub">Use the login page reset flow if access is lost.</div></div>
                                    </div>
                                    <div class="onboarding-step">
                                        <div class="step-num">3</div>
                                        <div style="flex:1"><div class="step-title active">Role managed</div><div class="step-sub">Owners can adjust roles from User Management.</div></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <aside>
                    <div class="admin-card">
                        <div class="company-name-cell">
                            <div class="company-logo-sm" style="width:58px;height:58px;border-radius:18px;font-size:18px">{{ $profile['initials'] }}</div>
                            <div style="min-width:0">
                                <div class="company-main-name">{{ $profile['name'] }}</div>
                                <div class="company-sub-name">{{ $authUser?->email }}</div>
                            </div>
                        </div>
                        <div class="company-detail-grid" style="grid-template-columns:1fr;margin-top:18px">
                            <div class="company-detail-card">
                                <div class="company-detail-label">Role</div>
                                <div class="company-detail-value">{{ ucfirst($authUser?->role ?? 'operator') }}</div>
                            </div>
                            <div class="company-detail-card">
                                <div class="company-detail-label">Last Login</div>
                                <div class="company-detail-value">{{ $authUser?->last_login_at?->diffForHumans() ?? 'Not recorded' }}</div>
                            </div>
                            <div class="company-detail-card">
                                <div class="company-detail-label">Account Created</div>
                                <div class="company-detail-value">{{ $authUser?->created_at?->format('M j, Y') ?? 'Not recorded' }}</div>
                            </div>
                        </div>
                    </div>

                </aside>
            </div>
        </section>
