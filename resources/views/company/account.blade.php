@extends('company.layout')

@section('title', 'Account | '.$company->name.' | Active8')

@section('content')
    @php
        $errorBag = $errors ?? new \Illuminate\Support\ViewErrorBag;
    @endphp

    <header class="company-topbar">
        <div>
            <div class="company-title-row">
                <span class="company-title-icon" aria-hidden="true"><svg><use href="#company-i-users"></use></svg></span>
                <h1 class="company-title">Account</h1>
            </div>
            <p class="company-subtitle">Profile, users, password security, and recent account activity.</p>
        </div>
        <a class="company-btn" href="{{ route('company.dashboard') }}">Dashboard</a>
    </header>

    <section class="company-content">
        <div class="account-tabs" role="tablist" aria-label="Account sections">
            @foreach ($accountTabs as $tab)
                <a
                    class="account-tab {{ $activeTab === $tab['key'] ? 'active' : '' }}"
                    href="{{ route('company.account', ['tab' => $tab['key']]) }}"
                    role="tab"
                    aria-selected="{{ $activeTab === $tab['key'] ? 'true' : 'false' }}"
                >
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </div>

        @if ($activeTab === 'profile')
            <div class="account-grid">
                <article class="admin-card">
                    <div class="admin-card-header">
                        <div>
                            <h2 class="admin-card-title">Company profile</h2>
                            <p class="admin-card-sub">Core account details used across the verification portal.</p>
                        </div>
                        <div class="account-card-actions">
                            <span class="status-pill {{ $company->status }}">{{ $company->status }}</span>
                            <button class="company-btn" type="button" data-account-profile-open>Edit</button>
                        </div>
                    </div>

                    <div class="details">
                        <div class="detail-row">
                            <div class="detail-label">Company</div>
                            <div class="detail-value">{{ $company->name }}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Industry</div>
                            <div class="detail-value">{{ $company->industry ?? 'Not recorded' }}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Contact</div>
                            <div class="detail-value">{{ $company->contact_person ?? 'Not recorded' }}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Email</div>
                            <div class="detail-value">{{ $company->email ?? 'Not recorded' }}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Phone</div>
                            <div class="detail-value">{{ $company->phone ?? 'Not recorded' }}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Website</div>
                            <div class="detail-value">{{ $company->website ?? 'Not recorded' }}</div>
                        </div>
                    </div>
                </article>

                <article class="admin-card">
                    <div class="admin-card-header">
                        <div>
                            <h2 class="admin-card-title">Verification profile</h2>
                            <p class="admin-card-sub">Public-facing support and verification settings.</p>
                        </div>
                        <div class="account-card-actions">
                            <span class="status-pill {{ $company->profile?->publication_status ?? 'draft' }}">
                                {{ $company->profile?->publication_status ?? 'draft' }}
                            </span>
                            <button class="company-btn" type="button" data-account-profile-open>Edit</button>
                        </div>
                    </div>

                    <div class="details">
                        <div class="detail-row">
                            <div class="detail-label">Support phone</div>
                            <div class="detail-value">{{ $company->profile?->support_phone ?? 'Not recorded' }}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Support email</div>
                            <div class="detail-value">{{ $company->profile?->support_email ?? 'Not recorded' }}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Published</div>
                            <div class="detail-value">{{ $company->profile?->published_at?->format('M j, Y g:i A') ?? 'Not published' }}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Updated</div>
                            <div class="detail-value">{{ $company->updated_at?->format('M j, Y g:i A') ?? 'Not recorded' }}</div>
                        </div>
                    </div>
                </article>
            </div>

            <div class="account-modal {{ $errorBag->has('company.*') || $errorBag->has('profile.*') ? 'active' : '' }}" data-account-profile-modal aria-hidden="{{ $errorBag->has('company.*') || $errorBag->has('profile.*') ? 'false' : 'true' }}">
                <button class="account-modal-backdrop" type="button" data-account-profile-close aria-label="Close edit profile modal"></button>
                <div class="account-modal-card" role="dialog" aria-modal="true" aria-labelledby="account-profile-modal-title">
                    <div class="account-modal-head">
                        <div class="account-modal-title-row">
                            <div class="account-modal-icon">
                                <svg width="18" height="18"><use href="#company-i-building"></use></svg>
                            </div>
                            <div>
                                <h2 class="admin-card-title" id="account-profile-modal-title">Edit profile</h2>
                                <p class="admin-card-sub">Update company details and public verification support information.</p>
                            </div>
                        </div>
                        <button class="account-modal-close" type="button" data-account-profile-close aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>

                    <div class="account-modal-summary">
                        <div class="company-logo-sm">{{ Str::of($company->name)->substr(0, 2)->upper() }}</div>
                        <div>
                            <strong>{{ $company->name }}</strong>
                            <span>{{ $company->industry ?? 'Industry not recorded' }}</span>
                        </div>
                        <span class="status-pill {{ $company->profile?->publication_status ?? 'draft' }}">{{ $company->profile?->publication_status ?? 'draft' }}</span>
                    </div>

                    <form method="POST" action="{{ route('company.account.profile.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="account-modal-body">
                            <div class="account-form-section">
                                <div class="account-section-head">
                                    <div>
                                        <div class="section-label">Company identity</div>
                                        <p>Legal identity fields are managed by the platform team for audit accuracy.</p>
                                    </div>
                                </div>
                                <div class="account-locked-grid">
                                    <div class="account-locked-field">
                                        <span>Company name</span>
                                        <strong>{{ $company->name }}</strong>
                                    </div>
                                    <div class="account-locked-field">
                                        <span>Registration number</span>
                                        <strong>{{ $company->registration_number ?? 'Not recorded' }}</strong>
                                    </div>
                                    <div class="account-locked-field">
                                        <span>Industry</span>
                                        <strong>{{ $company->industry ?? 'Not recorded' }}</strong>
                                    </div>
                                    <div class="account-locked-field">
                                        <span>Location</span>
                                        <strong>{{ collect([$company->city, $company->region, $company->country])->filter()->join(', ') ?: 'Not recorded' }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="account-form-section">
                                <div class="account-section-head">
                                    <div>
                                        <div class="section-label">Operational contact</div>
                                        <p>These details help the platform and customers reach the right team.</p>
                                    </div>
                                </div>
                                <div class="account-form-grid">
                                    <label class="account-field">
                                        <span>Contact person</span>
                                        <input name="company[contact_person]" value="{{ old('company.contact_person', $company->contact_person) }}" placeholder="Ama Mensah">
                                        <em>Primary person for company account and verification follow-up.</em>
                                        @if ($errorBag->has('company.contact_person')) <small>{{ $errorBag->first('company.contact_person') }}</small> @endif
                                    </label>
                                    <label class="account-field">
                                        <span>Company email</span>
                                        <input type="email" name="company[email]" value="{{ old('company.email', $company->email) }}" placeholder="operations@example.com">
                                        <em>Used for operational notices from the platform.</em>
                                        @if ($errorBag->has('company.email')) <small>{{ $errorBag->first('company.email') }}</small> @endif
                                    </label>
                                    <label class="account-field">
                                        <span>Company phone</span>
                                        <input name="company[phone]" value="{{ old('company.phone', $company->phone) }}" placeholder="+233 24 000 0000">
                                        <em>Visible to internal support teams handling escalations.</em>
                                        @if ($errorBag->has('company.phone')) <small>{{ $errorBag->first('company.phone') }}</small> @endif
                                    </label>
                                    <label class="account-field">
                                        <span>Website</span>
                                        <input type="url" name="company[website]" value="{{ old('company.website', $company->website) }}" placeholder="https://example.com">
                                        <em>Main company website. Use a full URL including https://.</em>
                                        @if ($errorBag->has('company.website')) <small>{{ $errorBag->first('company.website') }}</small> @endif
                                    </label>
                                </div>
                            </div>

                            <div class="account-form-section">
                                <div class="account-section-head">
                                    <div>
                                        <div class="section-label">Verification profile</div>
                                        <p>Public support details shown around product verification.</p>
                                    </div>
                                </div>
                                <div class="account-form-grid">
                                    <label class="account-field account-field-wide">
                                        <span>Verification headline</span>
                                        <input name="profile[verification_headline]" value="{{ old('profile.verification_headline', $company->profile?->verification_headline) }}" placeholder="Verify your original product">
                                        <em>Short heading shown on public product verification pages.</em>
                                        @if ($errorBag->has('profile.verification_headline')) <small>{{ $errorBag->first('profile.verification_headline') }}</small> @endif
                                    </label>
                                    <label class="account-field">
                                        <span>Support email</span>
                                        <input type="email" name="profile[support_email]" value="{{ old('profile.support_email', $company->profile?->support_email) }}" placeholder="verify@example.com">
                                        <em>Customer-facing address for verification questions.</em>
                                        @if ($errorBag->has('profile.support_email')) <small>{{ $errorBag->first('profile.support_email') }}</small> @endif
                                    </label>
                                    <label class="account-field">
                                        <span>Support phone</span>
                                        <input name="profile[support_phone]" value="{{ old('profile.support_phone', $company->profile?->support_phone) }}" placeholder="+233 24 000 0000">
                                        <em>Customer-facing phone number for verification support.</em>
                                        @if ($errorBag->has('profile.support_phone')) <small>{{ $errorBag->first('profile.support_phone') }}</small> @endif
                                    </label>
                                    <label class="account-field">
                                        <span>Verification website</span>
                                        <input type="url" name="profile[website_url]" value="{{ old('profile.website_url', $company->profile?->website_url) }}" placeholder="https://verify.example.com">
                                        <em>Optional public link shown after a customer checks a product.</em>
                                        @if ($errorBag->has('profile.website_url')) <small>{{ $errorBag->first('profile.website_url') }}</small> @endif
                                    </label>
                                    <label class="account-field">
                                        <span>Manufacturer</span>
                                        <input name="profile[manufacturer_name]" value="{{ old('profile.manufacturer_name', $company->profile?->manufacturer_name) }}" placeholder="Example Manufacturing Ltd">
                                        <em>Name customers should see as the product manufacturer.</em>
                                        @if ($errorBag->has('profile.manufacturer_name')) <small>{{ $errorBag->first('profile.manufacturer_name') }}</small> @endif
                                    </label>
                                    <label class="account-field">
                                        <span>Certification number</span>
                                        <input name="profile[certification_number]" value="{{ old('profile.certification_number', $company->profile?->certification_number) }}" placeholder="FDA/GSA/CERT-0000">
                                        <em>Public certificate or regulatory reference, if applicable.</em>
                                        @if ($errorBag->has('profile.certification_number')) <small>{{ $errorBag->first('profile.certification_number') }}</small> @endif
                                    </label>
                                </div>
                                <label class="account-field">
                                    <span>Company description</span>
                                    <textarea name="profile[company_description]" rows="4" placeholder="Briefly describe the company, products, and authenticity promise.">{{ old('profile.company_description', $company->profile?->company_description) }}</textarea>
                                    <em>Appears on public-facing verification experiences.</em>
                                    @if ($errorBag->has('profile.company_description')) <small>{{ $errorBag->first('profile.company_description') }}</small> @endif
                                </label>
                                <label class="account-field">
                                    <span>Factory address</span>
                                    <textarea name="profile[factory_address]" rows="3" placeholder="Industrial Area, Accra, Ghana">{{ old('profile.factory_address', $company->profile?->factory_address) }}</textarea>
                                    <em>Manufacturing address shown for traceability where relevant.</em>
                                    @if ($errorBag->has('profile.factory_address')) <small>{{ $errorBag->first('profile.factory_address') }}</small> @endif
                                </label>
                            </div>
                        </div>

                        <div class="account-modal-actions">
                            <button class="btn-learn" type="button" data-account-profile-close>Cancel</button>
                            <button class="btn-share" type="submit">Save profile</button>
                        </div>
                    </form>
                </div>
            </div>
        @elseif ($activeTab === 'users')
            <article class="admin-card">
                <div class="admin-card-header">
                    <div>
                        <h2 class="admin-card-title">Users</h2>
                        <p class="admin-card-sub">{{ number_format($companyUsers->count()) }} people can access this company dashboard.</p>
                    </div>
                    <div class="account-card-actions">
                        @if ($accountUser->isCompanyOwner())
                            <button class="btn-share" type="button" data-account-user-open>Add user</button>
                        @endif
                        <a class="company-btn" href="{{ route('company.users') }}">Full table</a>
                    </div>
                </div>

                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($companyUsers as $companyUser)
                                <tr>
                                    <td><strong>{{ $companyUser->name }}</strong></td>
                                    <td>{{ $companyUser->email }}</td>
                                    <td>{{ str_replace('_', ' ', $companyUser->role) }}</td>
                                    <td><span class="status-pill {{ $companyUser->status }}">{{ $companyUser->status }}</span></td>
                                    <td>{{ $companyUser->last_login_at?->diffForHumans() ?? 'No login recorded' }}</td>
                                    <td><a class="table-action" href="{{ route('company.users.show', $companyUser) }}">Open</a></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="company-empty-state">
                                            <div class="company-empty-icon">0</div>
                                            <div>
                                                <div class="company-empty-title">No users found</div>
                                                <div class="company-empty-copy">No company users have been assigned yet.</div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            @if ($accountUser->isCompanyOwner())
                <div class="account-user-drawer {{ $errors->has('name') || $errors->has('email') || $errors->has('role') || $errors->has('status') || $errors->has('password') ? 'active' : '' }}" data-account-user-drawer aria-hidden="{{ $errors->has('name') || $errors->has('email') || $errors->has('role') || $errors->has('status') || $errors->has('password') ? 'false' : 'true' }}">
                    <button class="account-user-drawer-backdrop" type="button" data-account-user-close aria-label="Close add user drawer"></button>
                    <aside class="account-user-drawer-panel" role="dialog" aria-modal="true" aria-labelledby="account-user-drawer-title">
                        <div class="account-user-drawer-head">
                            <div>
                                <div class="section-label">Company users</div>
                                <h2 id="account-user-drawer-title">Add user</h2>
                                <p>Create a dashboard user for {{ $company->name }}.</p>
                            </div>
                            <button class="account-modal-close" type="button" data-account-user-close aria-label="Close">×</button>
                        </div>

                        <form class="account-user-form" method="POST" action="{{ route('company.users.store') }}">
                            @csrf
                            <input type="hidden" name="return_to" value="account-users">

                            <div class="account-user-drawer-body">
                                <label class="account-field">
                                    <span>Full name</span>
                                    <input name="name" value="{{ old('name') }}" placeholder="Ama Mensah" required>
                                    <em>Name shown in activity logs and account screens.</em>
                                    @error('name') <small>{{ $message }}</small> @enderror
                                </label>

                                <label class="account-field">
                                    <span>Email address</span>
                                    <input type="email" name="email" value="{{ old('email') }}" placeholder="ama@example.com" autocomplete="email" required>
                                    <em>The user signs in with this email.</em>
                                    @error('email') <small>{{ $message }}</small> @enderror
                                </label>

                                <div class="account-form-grid">
                                    <label class="account-field">
                                        <span>Role</span>
                                        <select name="role" required>
                                            <option value="company_staff" @selected(old('role', 'company_staff') === 'company_staff')>Company Staff</option>
                                            <option value="company_owner" @selected(old('role') === 'company_owner')>Company Owner</option>
                                        </select>
                                        <em>Owners can add users. Staff can view company data.</em>
                                        @error('role') <small>{{ $message }}</small> @enderror
                                    </label>

                                    <label class="account-field">
                                        <span>Status</span>
                                        <select name="status" required>
                                            <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                                            <option value="invited" @selected(old('status') === 'invited')>Invited</option>
                                        </select>
                                        <em>Invited users cannot sign in until activated.</em>
                                        @error('status') <small>{{ $message }}</small> @enderror
                                    </label>
                                </div>

                                <label class="account-field">
                                    <span>Temporary password</span>
                                    <div class="password-toggle-field">
                                        <input type="password" name="password" placeholder="At least 8 characters" autocomplete="new-password" required data-password-toggle-input>
                                        <button type="button" data-password-toggle aria-label="Show temporary password" aria-pressed="false">Show</button>
                                    </div>
                                    <em>Share this securely and ask the user to change it after login.</em>
                                    @error('password') <small>{{ $message }}</small> @enderror
                                </label>
                            </div>

                            <div class="account-modal-actions">
                                <button class="btn-learn" type="button" data-account-user-close>Cancel</button>
                                <button class="btn-share" type="submit">Add user</button>
                            </div>
                        </form>
                    </aside>
                </div>
            @endif
        @elseif ($activeTab === 'password')
            <div class="account-password-shell">
                <article class="admin-card account-form-card">
                    <div class="admin-card-header">
                        <div>
                            <h2 class="admin-card-title">Change Password</h2>
                            <p class="admin-card-sub">Update the password for {{ $accountUser->email }}.</p>
                        </div>
                    </div>

                    <form class="account-form" method="POST" action="{{ route('company.account.password.update') }}">
                        @csrf
                        @method('PUT')

                        <label class="account-field">
                            <span>Current password</span>
                            <input type="password" name="current_password" autocomplete="current-password" placeholder="Enter your current password" required>
                            <em>Confirm it is really you before changing account access.</em>
                            @if ($errorBag->has('current_password'))
                                <small>{{ $errorBag->first('current_password') }}</small>
                            @endif
                        </label>

                        <label class="account-field">
                            <span>New password</span>
                            <input type="password" name="password" autocomplete="new-password" placeholder="At least 8 characters" required>
                            <em>Use a strong password with letters, numbers, or symbols.</em>
                            @if ($errorBag->has('password'))
                                <small>{{ $errorBag->first('password') }}</small>
                            @endif
                        </label>

                        <label class="account-field">
                            <span>Confirm new password</span>
                            <input type="password" name="password_confirmation" autocomplete="new-password" placeholder="Repeat the new password" required>
                            <em>Type the same password again to prevent mistakes.</em>
                        </label>

                        <button class="btn-share" type="submit">Update password</button>
                    </form>
                </article>
            </div>
        @else
            <article class="admin-card">
                <div class="admin-card-header">
                    <div>
                        <h2 class="admin-card-title">Log</h2>
                        <p class="admin-card-sub">Recent company activity from users, batches, and QR operations.</p>
                    </div>
                </div>

                <div class="account-log">
                    @forelse ($accountLogs as $log)
                        <div class="account-log-item">
                            <div class="account-log-dot"></div>
                            <div>
                                <div class="account-log-title">{{ $log->description }}</div>
                                <div class="muted">
                                    {{ $log->event }} · {{ $log->user?->name ?? 'System' }} · {{ $log->created_at?->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="company-empty-state">
                            <div class="company-empty-icon">0</div>
                            <div>
                                <div class="company-empty-title">No log entries yet</div>
                                <div class="company-empty-copy">Company activity will appear here when users make tracked changes.</div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </article>
        @endif
    </section>
@endsection

@push('styles')
    <style>
        .account-tabs { display:flex; gap:6px; margin-bottom:18px; padding:5px; border:1px solid var(--gray-200); border-radius:10px; background:var(--white); overflow-x:auto; box-shadow:0 10px 26px rgba(17,24,39,.05); }
        .account-tab { min-height:36px; display:inline-flex; align-items:center; justify-content:center; padding:8px 14px; border-radius:8px; color:var(--gray-700); font-size:13px; font-weight:850; white-space:nowrap; transition:background .16s ease,color .16s ease,box-shadow .16s ease; }
        .account-tab:hover { background:var(--gray-100); }
        .account-tab.active { background:var(--blue); color:var(--white); box-shadow:0 8px 18px rgba(37,99,235,.2); }
        .account-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:18px; align-items:start; }
        .account-card-actions { display:flex; align-items:center; gap:8px; flex-wrap:wrap; justify-content:flex-end; }
        .table-action { color:var(--blue); font-weight:850; }
        .account-password-shell { min-height:calc(100vh - 230px); display:grid; place-items:center; padding:18px 0 42px; }
        .account-form-card { width:min(620px, 100%); max-width:620px; }
        .account-password-shell .account-form-card { margin:0; }
        .account-form { display:grid; gap:14px; }
        .account-field { display:grid; gap:7px; color:var(--gray-700); font-size:13px; font-weight:850; }
        .account-field input, .account-field textarea, .account-field select { width:100%; padding:10px 12px; border:1px solid #dbe3ef; border-radius:8px; background:#fff; color:var(--gray-900); outline:none; transition:border-color .16s ease,box-shadow .16s ease,background .16s ease; }
        .account-field input { height:44px; }
        .account-field select { height:44px; }
        .account-field textarea { min-height:96px; resize:vertical; line-height:1.45; }
        .account-field input:hover, .account-field textarea:hover, .account-field select:hover { border-color:#c8d4e4; background:#fbfdff; }
        .account-field input:focus, .account-field textarea:focus, .account-field select:focus { border-color:rgba(37,99,235,.62); box-shadow:0 0 0 4px rgba(37,99,235,.1); background:#fff; }
        .account-field small { color:var(--red); font-weight:700; }
        .account-field em { color:var(--gray-500); font-size:12px; font-style:normal; font-weight:650; line-height:1.35; }
        .password-toggle-field { position:relative; display:flex; align-items:center; }
        .password-toggle-field input { padding-right:70px; }
        .password-toggle-field button { position:absolute; right:6px; min-width:54px; height:32px; display:inline-flex; align-items:center; justify-content:center; border-radius:7px; background:var(--gray-100); color:var(--gray-700); font-size:12px; font-weight:900; }
        .password-toggle-field button:hover { background:var(--blue-light); color:var(--blue); }
        .account-field-wide { grid-column:1 / -1; }
        .account-form .btn-share { justify-self:start; min-height:40px; }
        .account-modal { position:fixed; inset:0; z-index:80; display:none; align-items:center; justify-content:center; padding:22px; }
        .account-modal.active { display:flex; }
        .account-modal-backdrop { position:absolute; inset:0; background:rgba(15,23,42,.54); backdrop-filter:blur(8px); animation:account-fade .18s ease both; }
        .account-modal-card { position:relative; width:min(920px, 100%); max-height:min(860px, calc(100vh - 44px)); display:flex; flex-direction:column; overflow:hidden; border:1px solid rgba(226,232,240,.96); border-radius:18px; background:#fff; box-shadow:0 34px 90px rgba(15,23,42,.34); animation:account-pop .2s cubic-bezier(.2,.8,.2,1) both; }
        .account-modal-card form { min-height:0; display:flex; flex:1; flex-direction:column; }
        .account-modal-head { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; padding:22px 24px 18px; border-bottom:1px solid var(--gray-100); background:linear-gradient(180deg, #fff, #f8fbff); }
        .account-modal-title-row { display:flex; align-items:flex-start; gap:12px; min-width:0; }
        .account-modal-icon { width:40px; height:40px; display:grid; place-items:center; flex-shrink:0; border:1px solid rgba(37,99,235,.16); border-radius:12px; background:var(--blue-light); color:var(--blue); box-shadow:0 10px 22px rgba(37,99,235,.1); }
        .account-modal-close { width:34px; height:34px; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; border:1px solid transparent; border-radius:10px; background:var(--white); color:var(--gray-500); font-size:24px; line-height:1; box-shadow:0 8px 18px rgba(15,23,42,.06); transition:background .16s ease,color .16s ease,border-color .16s ease,transform .16s ease; }
        .account-modal-close:hover { border-color:var(--gray-200); background:var(--gray-50); color:var(--gray-900); transform:translateY(-1px); }
        .account-modal-summary { display:flex; align-items:center; gap:12px; padding:14px 24px; border-bottom:1px solid var(--gray-100); background:#fff; }
        .account-modal-summary strong { display:block; color:var(--gray-900); font-size:14px; font-weight:900; }
        .account-modal-summary span:not(.status-pill) { display:block; margin-top:2px; color:var(--gray-500); font-size:12px; font-weight:700; }
        .account-modal-summary .status-pill { margin-left:auto; }
        .account-modal-body { min-height:0; flex:1; overflow-y:auto; overscroll-behavior:contain; padding:20px 24px 24px; background:#f8fafc; }
        .account-form-section { display:grid; gap:16px; padding:18px; margin-bottom:16px; border:1px solid #e5edf7; border-radius:14px; background:#fff; box-shadow:0 12px 28px rgba(15,23,42,.05); }
        .account-form-section:last-child { margin-bottom:0; }
        .account-section-head { display:flex; align-items:flex-start; justify-content:space-between; gap:14px; padding-bottom:2px; }
        .account-section-head p { margin-top:3px; color:var(--gray-500); font-size:12px; font-weight:700; line-height:1.4; }
        .account-locked-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:12px; }
        .account-locked-field { min-width:0; padding:12px; border:1px solid var(--gray-200); border-radius:10px; background:linear-gradient(180deg, #f9fbff, #fff); }
        .account-locked-field span { display:block; color:var(--gray-500); font-size:11px; font-weight:900; letter-spacing:.04em; text-transform:uppercase; }
        .account-locked-field strong { display:block; margin-top:5px; overflow:hidden; color:var(--gray-900); font-size:13px; font-weight:900; text-overflow:ellipsis; white-space:nowrap; }
        .account-form-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:14px; }
        .section-label { color:var(--gray-900); font-size:12px; font-weight:950; letter-spacing:.05em; text-transform:uppercase; }
        .account-modal-actions { display:flex; align-items:center; justify-content:flex-end; gap:10px; padding:16px 24px; border-top:1px solid var(--gray-100); background:#fff; box-shadow:0 -10px 24px rgba(15,23,42,.04); }
        .account-modal-actions .btn-learn, .account-modal-actions .btn-share { min-height:40px; padding-inline:18px; }
        .account-user-drawer { position:fixed; inset:0; z-index:90; display:none; justify-content:flex-end; }
        .account-user-drawer.active { display:flex; }
        .account-user-drawer-backdrop { position:absolute; inset:0; background:rgba(15,23,42,.46); backdrop-filter:blur(7px); animation:account-fade .18s ease both; }
        .account-user-drawer-panel { position:relative; width:min(460px, 100%); height:100%; display:flex; flex-direction:column; overflow:hidden; border-left:1px solid var(--gray-200); background:#fff; box-shadow:-28px 0 70px rgba(15,23,42,.24); animation:account-user-slide .22s cubic-bezier(.2,.8,.2,1) both; }
        .account-user-drawer-head { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; padding:24px; border-bottom:1px solid var(--gray-100); background:linear-gradient(180deg, #fff, #f8fbff); }
        .account-user-drawer-head h2 { margin-top:5px; color:var(--gray-900); font-size:22px; font-weight:900; line-height:1.15; }
        .account-user-drawer-head p { margin-top:5px; color:var(--gray-500); font-size:13px; font-weight:700; line-height:1.4; }
        .account-user-form { min-height:0; display:flex; flex:1; flex-direction:column; }
        .account-user-drawer-body { min-height:0; flex:1; display:grid; align-content:start; gap:15px; overflow-y:auto; padding:22px 24px; background:#f8fafc; }
        .account-log { display:grid; gap:0; }
        .account-log-item { position:relative; display:grid; grid-template-columns:18px minmax(0, 1fr); gap:12px; padding:13px 0; border-bottom:1px solid var(--gray-100); }
        .account-log-item:last-child { border-bottom:0; }
        .account-log-dot { width:10px; height:10px; margin-top:5px; border-radius:999px; background:var(--blue); box-shadow:0 0 0 5px var(--blue-light); }
        .account-log-title { color:var(--gray-900); font-weight:850; line-height:1.35; }
        @keyframes account-fade { from { opacity:0; } to { opacity:1; } }
        @keyframes account-pop { from { opacity:0; transform:translateY(12px) scale(.985); } to { opacity:1; transform:translateY(0) scale(1); } }
        @keyframes account-user-slide { from { transform:translateX(24px); opacity:.8; } to { transform:translateX(0); opacity:1; } }
        @media (max-width: 980px) {
            .account-grid { grid-template-columns:1fr; }
            .account-password-shell { min-height:0; display:block; padding:0; }
            .account-form-grid, .account-locked-grid { grid-template-columns:1fr; }
            .account-modal { align-items:flex-start; padding:14px; overflow-y:auto; }
            .account-modal-card { max-height:calc(100vh - 28px); border-radius:14px; }
            .account-modal-head, .account-modal-summary, .account-modal-body, .account-modal-actions { padding-left:16px; padding-right:16px; }
            .account-modal-summary { align-items:flex-start; }
            .account-modal-summary .status-pill { margin-left:0; }
            .account-modal-actions { align-items:stretch; flex-direction:column-reverse; }
            .account-modal-actions .btn-learn, .account-modal-actions .btn-share { width:100%; min-height:42px; }
            .account-user-drawer-panel { border-left:0; }
            .account-tabs { scrollbar-width:thin; }
        }
        @media (max-width: 640px) {
            .account-tabs { margin-bottom:14px; }
            .account-tab { flex:0 0 auto; min-width:max-content; }
            .account-card-actions { align-items:stretch; justify-content:flex-start; flex-direction:column; }
            .account-card-actions .company-btn, .account-card-actions .btn-share { width:100%; min-height:40px; }
            .account-modal-head, .account-user-drawer-head { gap:12px; padding-top:18px; padding-bottom:16px; }
            .account-modal-title-row { gap:10px; }
            .account-modal-icon { width:36px; height:36px; border-radius:10px; }
            .account-modal-summary { flex-wrap:wrap; }
            .account-form-section { padding:14px; }
            .account-section-head { flex-direction:column; }
            .account-user-drawer-body { padding-top:18px; padding-bottom:18px; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        (() => {
            const modal = document.querySelector('[data-account-profile-modal]');
            if (modal) {
                const open = () => {
                    modal.classList.add('active');
                    modal.setAttribute('aria-hidden', 'false');
                    modal.querySelector('input, textarea, button')?.focus();
                };
                const close = () => {
                    modal.classList.remove('active');
                    modal.setAttribute('aria-hidden', 'true');
                };

                document.querySelectorAll('[data-account-profile-open]').forEach((button) => button.addEventListener('click', open));
                modal.querySelectorAll('[data-account-profile-close]').forEach((button) => button.addEventListener('click', close));
            }

            const userDrawer = document.querySelector('[data-account-user-drawer]');
            if (userDrawer) {
                const openUserDrawer = () => {
                    userDrawer.classList.add('active');
                    userDrawer.setAttribute('aria-hidden', 'false');
                    userDrawer.querySelector('input, select, button')?.focus();
                };
                const closeUserDrawer = () => {
                    userDrawer.classList.remove('active');
                    userDrawer.setAttribute('aria-hidden', 'true');
                };

                document.querySelectorAll('[data-account-user-open]').forEach((button) => button.addEventListener('click', openUserDrawer));
                userDrawer.querySelectorAll('[data-account-user-close]').forEach((button) => button.addEventListener('click', closeUserDrawer));
            }

            document.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape') return;

                if (modal?.classList.contains('active')) {
                    modal.classList.remove('active');
                    modal.setAttribute('aria-hidden', 'true');
                }
                if (userDrawer?.classList.contains('active')) {
                    userDrawer.classList.remove('active');
                    userDrawer.setAttribute('aria-hidden', 'true');
                }
            });

            document.querySelectorAll('[data-password-toggle]').forEach((button) => {
                const field = button.closest('.password-toggle-field');
                const input = field?.querySelector('[data-password-toggle-input]');
                if (!input) return;

                button.addEventListener('click', () => {
                    const isVisible = input.type === 'text';
                    input.type = isVisible ? 'password' : 'text';
                    button.textContent = isVisible ? 'Show' : 'Hide';
                    button.setAttribute('aria-label', `${isVisible ? 'Show' : 'Hide'} temporary password`);
                    button.setAttribute('aria-pressed', isVisible ? 'false' : 'true');
                    input.focus();
                });
            });
        })();
    </script>
@endpush
