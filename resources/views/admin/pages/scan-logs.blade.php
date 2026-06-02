<section class="screen {{ $activePage === 'scan-logs' ? 'active' : '' }}" id="screen-scan-logs">
            <div class="topbar center">
                <div>
                    <div class="page-title">Scan Logs</div>
                    <div class="page-subtitle">Review every public verification scan, including genuine, invalid, and flagged attempts.</div>
                </div>
                <div class="topbar-actions">
                    <a class="btn-learn" href="{{ route('active8.page', 'scan-analytics') }}">Analytics</a>
                    <a class="btn-share" href="{{ route('active8.page', 'dashboard') }}">
                        <svg width="14" height="14"><use href="#i-activity"></use></svg>
                        Dashboard
                    </a>
                </div>
            </div>

            <div class="company-page-layout scan-logs-layout">
                <div>
                    <div class="company-detail-grid">
                        <div class="company-detail-card">
                            <div class="company-detail-label">Total Scans</div>
                            <div class="company-detail-value">{{ number_format($scanSummary['total']) }}</div>
                        </div>
                        <div class="company-detail-card">
                            <div class="company-detail-label">Genuine</div>
                            <div class="company-detail-value">{{ number_format($scanSummary['genuine']) }}</div>
                        </div>
                        <div class="company-detail-card">
                            <div class="company-detail-label">Invalid</div>
                            <div class="company-detail-value">{{ number_format($scanSummary['invalid']) }}</div>
                        </div>
                        <div class="company-detail-card">
                            <div class="company-detail-label">Flagged</div>
                            <div class="company-detail-value">{{ number_format($scanSummary['flagged']) }}</div>
                        </div>
                        <div class="company-detail-card">
                            <div class="company-detail-label">Mobile</div>
                            <div class="company-detail-value">{{ number_format($scanSummary['mobile']) }}</div>
                        </div>
                        <div class="company-detail-card">
                            <div class="company-detail-label">Desktop</div>
                            <div class="company-detail-value">{{ number_format($scanSummary['desktop']) }}</div>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="admin-card-header">
                            <div>
                                <div class="admin-card-title">Verification Scan Console</div>
                                <div class="admin-card-sub">Logged automatically when a customer opens a `/verify/{token}` QR URL.</div>
                            </div>
                            <div class="table-actions">
                                <a class="btn-learn" href="{{ route('reports.verification.export', request()->only(['status', 'company', 'batch'])) }}">
                                    <svg width="14" height="14"><use href="#i-download"></use></svg>
                                    Export CSV
                                </a>
                                <span class="status-pill active">Live logging</span>
                            </div>
                        </div>

                        <div class="table-toolbar" data-scan-table-toolbar>
                            <label class="table-search" aria-label="Search scan logs">
                                <svg width="14" height="14"><use href="#i-search"></use></svg>
                                <input type="search" data-scan-search placeholder="Search token, batch, product, IP">
                            </label>
                            <div class="table-filter-row">
                                <select class="font-select" data-scan-status-filter aria-label="Filter by result">
                                    <option value="all">All results</option>
                                    <option value="genuine">Genuine</option>
                                    <option value="invalid">Invalid</option>
                                    <option value="suspicious">Suspicious</option>
                                    <option value="revoked">Revoked</option>
                                    <option value="expired">Expired</option>
                                    <option value="recalled">Recalled</option>
                                </select>
                                <select class="font-select" data-scan-device-filter aria-label="Filter by device">
                                    <option value="all">All devices</option>
                                    <option value="mobile">Mobile</option>
                                    <option value="desktop">Desktop</option>
                                    <option value="tablet">Tablet</option>
                                    <option value="unknown">Unknown</option>
                                </select>
                            </div>
                        </div>

                        <div class="admin-table-wrap">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>Token / Batch</th>
                                        <th>Product</th>
                                        <th>Device</th>
                                        <th>IP / Location</th>
                                        <th class="fit">Time</th>
                                        <th class="fit">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($scanLogs as $scan)
                                        @php
                                            $riskFlags = collect($scan->metadata['risk_flags'] ?? []);
                                        @endphp
                                        <tr class="row-clickable" data-scan-row data-scan-id="{{ $scan->id }}" data-scan-status="{{ $scan->status_result ?? 'unknown' }}" data-scan-device="{{ $scan->device_type ?? 'unknown' }}" data-scan-search-text="{{ Str::lower(($scan->token_scanned ?? '').' '.($scan->qrCode?->batch?->batch_number ?? '').' '.($scan->qrCode?->batch?->product?->name ?? '').' '.($scan->qrCode?->batch?->product?->company?->name ?? '').' '.($scan->ip_address ?? '').' '.($scan->country ?? '').' '.($scan->city ?? '').' '.($scan->browser ?? '').' '.($scan->device_type ?? '')) }}">
                                            <td>
                                                <span class="status-pill {{ $scan->status_result }}">{{ ucfirst($scan->status_result) }}</span>
                                                @if ($riskFlags->isNotEmpty())
                                                    <div style="margin-top:6px"><span class="health-pill warning">{{ $riskFlags->count() }} risk {{ Str::plural('flag', $riskFlags->count()) }}</span></div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="company-main-name">{{ $scan->qrCode?->batch?->batch_number ?? 'Unknown batch' }}</div>
                                                <div class="company-sub-name">{{ $scan->token_scanned ?? 'No token captured' }}</div>
                                            </td>
                                            <td>
                                                <div class="company-main-name">{{ $scan->qrCode?->batch?->product?->name ?? 'Unknown product' }}</div>
                                                <div class="company-sub-name">{{ $scan->qrCode?->batch?->product?->company?->name ?? 'No company match' }}</div>
                                            </td>
                                            <td>
                                                <div class="company-main-name">{{ ucfirst($scan->device_type ?? 'unknown') }}</div>
                                                <div class="company-sub-name">{{ $scan->browser ?? 'Unknown browser' }}</div>
                                            </td>
                                            <td>
                                                <div class="company-main-name">{{ $scan->ip_address ?? 'Unknown' }}</div>
                                                <div class="company-sub-name">{{ collect([$scan->city, $scan->country])->filter()->implode(', ') ?: 'Unknown location' }}</div>
                                            </td>
                                            <td class="fit">
                                                <div class="company-main-name">{{ $scan->scanned_at?->diffForHumans() }}</div>
                                                <div class="company-sub-name">{{ $scan->scanned_at?->format('M j, Y H:i') }}</div>
                                            </td>
                                            <td class="fit">
                                                <button class="icon-btn" type="button" data-scan-drawer-open data-scan-form="details-{{ $scan->id }}" aria-label="View scan details">
                                                    <svg width="15" height="15"><use href="#i-eye"></use></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr data-scan-empty><td colspan="7">@include('partials.admin-empty-state', ['icon' => 'i-eye', 'title' => 'No scans logged yet', 'copy' => 'Customer verification events will appear here after QR codes are scanned.', 'actionLabel' => 'View Analytics', 'actionTarget' => route('active8.page', 'scan-analytics'), 'actionType' => 'link'])</td></tr>
                                    @endforelse
                                    <tr class="filter-empty" data-scan-no-results>
                                        <td colspan="7">
                                            <div class="filter-empty-title">No scans match those filters.</div>
                                            <div class="filter-empty-copy">Try another token, batch, result, or device.</div>
                                            <button class="btn-learn" type="button" data-clear-scan-filters>Clear filters</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="table-pagination" data-scan-pagination>
                            <span data-scan-page-summary>Showing scans</span>
                            <div class="pager-actions">
                                <button class="pager-btn" type="button" data-scan-page-prev aria-label="Previous scan page">‹</button>
                                <span data-scan-page-current>1</span>
                                <button class="pager-btn" type="button" data-scan-page-next aria-label="Next scan page">›</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="user-create-drawer" id="scan-drawer" aria-hidden="true">
                <div class="drawer-backdrop" data-scan-drawer-close></div>
                <div class="drawer-panel" role="dialog" aria-modal="true" aria-labelledby="scan-drawer-title">
                    <div class="drawer-header">
                        <div>
                            <div class="admin-card-title" id="scan-drawer-title">Scan Details</div>
                            <div class="admin-card-sub" id="scan-drawer-subtitle">Review the verification event context.</div>
                        </div>
                        <button class="drawer-close" type="button" data-scan-drawer-close aria-label="Close scan details">×</button>
                    </div>
                    <div class="drawer-body">
                        @foreach ($scanLogs as $scan)
                            @php
                                $riskFlags = collect($scan->metadata['risk_flags'] ?? []);
                            @endphp
                            <div class="drawer-user-form activity-panel" id="scan-form-details-{{ $scan->id }}" data-form-title="Scan Details" data-form-subtitle="{{ $scan->qrCode?->batch?->batch_number ?? $scan->token_scanned ?? 'Verification event' }}">
                                <div class="activity-hero">
                                    <div class="company-logo-sm" style="width:48px;height:48px;font-size:16px">{{ Str::of($scan->status_result ?? 'SC')->substr(0, 2)->upper() }}</div>
                                    <div style="min-width:0">
                                        <div class="company-main-name">{{ ucfirst($scan->status_result ?? 'unknown') }} scan</div>
                                        <div class="company-sub-name">{{ $scan->scanned_at?->format('M j, Y H:i') ?? 'Time not recorded' }}</div>
                                    </div>
                                </div>
                                <div class="activity-meta">
                                    <div class="activity-meta-row"><span>Token</span><strong>{{ $scan->token_scanned ?? 'No token captured' }}</strong></div>
                                    <div class="activity-meta-row"><span>Batch</span><strong>{{ $scan->qrCode?->batch?->batch_number ?? 'Unknown batch' }}</strong></div>
                                    <div class="activity-meta-row"><span>Product</span><strong>{{ $scan->qrCode?->batch?->product?->name ?? 'Unknown product' }}</strong></div>
                                    <div class="activity-meta-row"><span>Company</span><strong>{{ $scan->qrCode?->batch?->product?->company?->name ?? 'No company match' }}</strong></div>
                                    <div class="activity-meta-row"><span>Device</span><strong>{{ ucfirst($scan->device_type ?? 'unknown') }}</strong></div>
                                    <div class="activity-meta-row"><span>Browser</span><strong>{{ $scan->browser ?? 'Unknown browser' }}</strong></div>
                                    <div class="activity-meta-row"><span>IP address</span><strong>{{ $scan->ip_address ?? 'Unknown' }}</strong></div>
                                    <div class="activity-meta-row"><span>Location</span><strong>{{ collect([$scan->city, $scan->country])->filter()->implode(', ') ?: 'Unknown location' }}</strong></div>
                                    <div class="activity-meta-row"><span>Scan count</span><strong>{{ number_format($scan->metadata['scan_count'] ?? 1) }}</strong></div>
                                    <div class="activity-meta-row"><span>Same IP repeats</span><strong>{{ number_format($scan->metadata['same_ip_scan_count'] ?? 0) }}</strong></div>
                                    <div class="activity-meta-row"><span>Same device hints</span><strong>{{ number_format($scan->metadata['same_fingerprint_scan_count'] ?? 0) }}</strong></div>
                                    <div class="activity-meta-row"><span>Geo source</span><strong>{{ Str::headline($scan->metadata['geo_source'] ?? 'unavailable') }}</strong></div>
                                    <div class="activity-meta-row"><span>Previous scan</span><strong>{{ ! empty($scan->metadata['previous_scan_at']) ? \Carbon\Carbon::parse($scan->metadata['previous_scan_at'])->diffForHumans() : 'None recorded' }}</strong></div>
                                </div>
                                @if ($riskFlags->isNotEmpty())
                                    <div class="role-preview">
                                        <div class="role-preview-title">Risk Signals</div>
                                        <div class="role-preview-list">
                                            @foreach ($riskFlags as $flag)
                                                <span>{{ Str::headline($flag) }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                @if ($scan->user_agent)
                                    <div class="role-preview">
                                        <div class="role-preview-title">User agent</div>
                                        <div class="company-sub-name" style="margin-top:8px;word-break:break-word">{{ $scan->user_agent }}</div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
