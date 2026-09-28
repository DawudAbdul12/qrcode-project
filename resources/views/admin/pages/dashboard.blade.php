<section class="screen {{ $activePage === 'dashboard' ? 'active' : '' }}" id="screen-dashboard">
            <div class="dashboard-shell">
                <div class="dashboard-hero">
                    <div>
                        <div class="dashboard-eyebrow">Live Operations</div>
                        <div class="dashboard-hero-title">Verification control, without the clutter.</div>
                        <div class="dashboard-hero-copy">
                            A premium command center for companies, products, batch QR codes, label orders, and customer scan signals.
                        </div>
                        <div class="dashboard-hero-actions">
                            <a class="dashboard-primary" href="{{ route('active8.page', 'companies') }}">
                                <svg width="15" height="15"><use href="#i-plus"></use></svg>
                                Add Company
                            </a>
                            <a class="dashboard-secondary" href="{{ route('active8.page', 'scan-analytics') }}">
                                <svg width="15" height="15"><use href="#i-activity"></use></svg>
                                View Analytics
                            </a>
                            <a class="dashboard-secondary" href="{{ route('active8.page', 'label-orders') }}">
                                <svg width="15" height="15"><use href="#i-download"></use></svg>
                                Label Orders
                            </a>
                        </div>
                    </div>
                    <div class="dashboard-hero-panel">
                        <div class="dashboard-hero-metric">
                            <div>
                                <div class="dashboard-hero-label">Total scans</div>
                                <div class="dashboard-hero-value">{{ number_format($scanSummary['total'] ?? 0) }}</div>
                            </div>
                            <div class="dashboard-hero-note">All-time verification volume</div>
                        </div>
                        <div class="dashboard-hero-metric">
                            <div>
                                <div class="dashboard-hero-label">Genuine</div>
                                <div class="dashboard-hero-value">{{ number_format($scanSummary['genuine'] ?? 0) }}</div>
                            </div>
                            <div class="dashboard-hero-note">Trusted customer scans</div>
                        </div>
                        <div class="dashboard-hero-metric">
                            <div>
                                <div class="dashboard-hero-label">Flagged</div>
                                <div class="dashboard-hero-value">{{ number_format($scanSummary['flagged'] ?? 0) }}</div>
                            </div>
                            <div class="dashboard-hero-note">Needs review</div>
                        </div>
                    </div>
                </div>

                <div class="dashboard-kpis">
                    @foreach ($dashboardStats as $stat)
                        <div class="dashboard-kpi">
                            <div class="dashboard-kpi-top">
                                <div class="dashboard-kpi-icon"><svg width="17" height="17"><use href="#i-{{ $stat['icon'] }}"></use></svg></div>
                                <span class="health-pill info">Live</span>
                            </div>
                            <div class="dashboard-kpi-label">{{ $stat['label'] }}</div>
                            <div class="dashboard-kpi-value">{{ number_format((int) $stat['value']) }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="dashboard-grid">
                    <div class="dashboard-stack">
                        <div class="premium-card">
                            <div class="premium-card-header">
                                <div>
                                    <div class="premium-title">30-Day Scan Trend</div>
                                    <div class="premium-subtitle">Verification volume across the selected analytics period.</div>
                                </div>
                                <a class="premium-link" href="{{ route('active8.page', 'scan-analytics') }}">Full report</a>
                            </div>
                            <div class="trend-card-body">
                                <div class="trend-chart">
                                    <svg viewBox="0 0 900 260" preserveAspectRatio="none" aria-hidden="true">
                                        <line class="grid-line" x1="0" y1="50" x2="900" y2="50"/>
                                        <line class="grid-line" x1="0" y1="130" x2="900" y2="130"/>
                                        <line class="grid-line" x1="0" y1="210" x2="900" y2="210"/>
                                        <path class="trend-area" d="{{ $analyticsReport['chart_path'] }} L900,260 L0,260 Z"/>
                                        <path class="trend-line" d="{{ $analyticsReport['chart_path'] }}"/>
                                    </svg>
                                    <div class="trend-axis">
                                        @forelse (collect($analyticsReport['x_labels'])->take(4) as $label)
                                            <span>{{ $label['label'] ?? '' }}</span>
                                        @empty
                                            <span>Start</span><span>Now</span>
                                        @endforelse
                                    </div>
                                </div>
                                <div class="trend-stat-row">
                                    <div class="trend-stat"><span>Total</span><strong>{{ number_format($analyticsReport['total'] ?? 0) }}</strong></div>
                                    <div class="trend-stat"><span>Unique codes</span><strong>{{ number_format($analyticsReport['unique_tokens'] ?? 0) }}</strong></div>
                                    <div class="trend-stat"><span>Repeat scans</span><strong>{{ number_format($analyticsReport['repeat_scans'] ?? 0) }}</strong></div>
                                </div>
                            </div>
                        </div>

                        <div class="premium-card">
                            <div class="premium-card-header">
                                <div>
                                    <div class="premium-title">Recent Batches</div>
                                    <div class="premium-subtitle">The latest production batches and QR readiness.</div>
                                </div>
                                <a class="premium-link" href="{{ route('active8.page', 'batches') }}">Manage batches</a>
                            </div>
                            <div class="premium-table-wrap">
                                <table class="premium-table" data-auto-paginate data-auto-paginate-label="batches" data-auto-paginate-per-page="5">
                                    <thead>
                                        <tr>
                                            <th>Batch</th>
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Status</th>
                                            <th>QR</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentBatches as $batch)
                                            <tr>
                                                <td><strong>{{ $batch->batch_number }}</strong><small>{{ $batch->product?->company?->name ?? 'Company not set' }}</small></td>
                                                <td>{{ $batch->product?->name ?? 'Product not set' }}</td>
                                                <td>{{ number_format($batch->quantity) }}</td>
                                                <td><span class="status-pill {{ $batch->status }}">{{ $batch->status }}</span></td>
                                                <td>{{ $batch->qrCode ? 'Ready' : 'Pending' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5">No batches yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="premium-card">
                            <div class="premium-card-header">
                                <div>
                                    <div class="premium-title">Recent Scan Activity</div>
                                    <div class="premium-subtitle">Customer verification signals from public QR scans.</div>
                                </div>
                                <a class="premium-link" href="{{ route('active8.page', 'scan-logs') }}">Open logs</a>
                            </div>
                            <div class="premium-table-wrap">
                                <table class="premium-table" data-auto-paginate data-auto-paginate-label="scans" data-auto-paginate-per-page="5">
                                    <thead>
                                        <tr>
                                            <th>Result</th>
                                            <th>Batch</th>
                                            <th>Location</th>
                                            <th>Scanned</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentScans as $scan)
                                            <tr>
                                                <td><span class="status-pill {{ $scan->status_result }}">{{ $scan->status_result }}</span></td>
                                                <td><strong>{{ $scan->qrCode?->batch?->batch_number ?? $scan->token_scanned }}</strong><small>{{ $scan->qrCode?->batch?->product?->name ?? 'Unknown product' }}</small></td>
                                                <td>{{ collect([$scan->city, $scan->country])->filter()->implode(', ') ?: 'Unknown' }}</td>
                                                <td>{{ $scan->scanned_at?->diffForHumans() }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4">No scans yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="premium-card">
                            <div class="premium-card-header">
                                <div>
                                    <div class="premium-title">Audit Trail</div>
                                    <div class="premium-subtitle">Recent operational changes and QR lifecycle events.</div>
                                </div>
                                <a class="premium-link" href="{{ route('active8.page', 'audit-trail') }}">Open trail</a>
                            </div>
                            @include('partials.admin-audit-list', ['logs' => $recentActivityLogs->take(6), 'emptyCopy' => 'Batch and QR lifecycle changes will appear here.'])
                        </div>
                    </div>

                    <div class="dashboard-stack">
                        <div class="premium-card">
                            <div class="premium-card-header">
                                <div>
                                    <div class="premium-title">Priority Alerts</div>
                                    <div class="premium-subtitle">The items most likely to need attention.</div>
                                </div>
                            </div>
                            <div class="widget-list">
                                @foreach ($dashboardAlerts as $alert)
                                    <a class="widget-list-item alert-card {{ $alert['tone'] }}" href="{{ $alert['href'] }}">
                                        <span class="alert-dot"></span>
                                        <span style="flex:1;min-width:0">
                                            <span class="widget-list-title">{{ $alert['title'] }}</span>
                                            <span class="widget-list-sub">{{ $alert['copy'] }}</span>
                                        </span>
                                        <span class="premium-link">Open</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <div class="premium-card">
                            <div class="premium-card-header">
                                <div>
                                    <div class="premium-title">Quick Actions</div>
                                    <div class="premium-subtitle">The three admin paths that matter most.</div>
                                </div>
                            </div>
                            <div class="action-grid">
                                <a class="action-card" href="{{ route('active8.page', 'company-profile') }}">
                                    <span class="action-icon"><svg width="17" height="17"><use href="#i-edit"></use></svg></span>
                                    <span><span class="action-title">Polish public trust page</span><span class="action-copy">Update customer-facing brand, support, and verification content.</span></span>
                                </a>
                                <a class="action-card" href="{{ route('active8.page', 'products') }}">
                                    <span class="action-icon"><svg width="17" height="17"><use href="#i-bag"></use></svg></span>
                                    <span><span class="action-title">Register products</span><span class="action-copy">Manage products before creating secure production batches.</span></span>
                                </a>
                                <a class="action-card" href="{{ route('active8.page', 'batches') }}">
                                    <span class="action-icon"><svg width="17" height="17"><use href="#i-qr"></use></svg></span>
                                    <span><span class="action-title">Generate batch QR</span><span class="action-copy">Create one verification QR code per approved batch.</span></span>
                                </a>
                            </div>
                        </div>

                        <div class="premium-card">
                            <div class="premium-card-header">
                                <div>
                                    <div class="premium-title">Operations Health</div>
                                    <div class="premium-subtitle">A quick read on readiness and risk.</div>
                                </div>
                            </div>
                            <div class="widget-body">
                                <div class="widget-row">
                                    <div style="flex:1">
                                        <div class="widget-label">QR coverage</div>
                                        <div class="widget-progress"><span style="width:{{ $dashboardQrCoverage }}%"></span></div>
                                    </div>
                                    <div class="widget-value">{{ $dashboardQrCoverage }}%</div>
                                </div>
                                <div class="widget-row">
                                    <div>
                                        <div class="widget-label">Genuine rate</div>
                                        <div class="widget-progress"><span style="width:{{ $dashboardGenuineRate }}%"></span></div>
                                    </div>
                                    <div class="widget-value">{{ $dashboardGenuineRate }}%</div>
                                </div>
                                <div class="widget-row">
                                    <div>
                                        <div class="widget-label">Flagged rate</div>
                                        <div class="widget-progress"><span style="width:{{ $dashboardFlaggedRate }}%;background:linear-gradient(90deg,#ef4444,#f97316)"></span></div>
                                    </div>
                                    <div class="widget-value">{{ $dashboardFlaggedRate }}%</div>
                                </div>
                            </div>
                        </div>

                        <div class="premium-card">
                            <div class="premium-card-header">
                                <div>
                                    <div class="premium-title">Label Queue</div>
                                    <div class="premium-subtitle">{{ number_format($dashboardOpenOrders) }} open print {{ Str::plural('order', $dashboardOpenOrders) }}.</div>
                                </div>
                                <a class="premium-link" href="{{ route('active8.page', 'label-orders') }}">View all</a>
                            </div>
                            <div class="widget-list">
                                @forelse ($recentLabelOrders as $order)
                                    <div class="widget-list-item">
                                        <div>
                                            <div class="widget-list-title">{{ $order->company?->name ?? 'Company not set' }}</div>
                                            <div class="widget-list-sub">{{ $order->batch?->batch_number ?? 'General order' }} · {{ number_format($order->print_quantity) }} labels</div>
                                        </div>
                                        <span class="status-pill {{ $order->status }}">{{ $order->status }}</span>
                                    </div>
                                @empty
                                    <div class="widget-list-item">
                                        <div>
                                            <div class="widget-list-title">No label orders yet</div>
                                            <div class="widget-list-sub">New print jobs will appear here.</div>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="premium-card">
                            <div class="premium-card-header">
                                <div>
                                    <div class="premium-title">Device Mix</div>
                                    <div class="premium-subtitle">Where verification traffic is coming from.</div>
                                </div>
                            </div>
                            <div class="device-widget">
                                <div class="device-bar">
                                    <div class="device-item">
                                        <div class="device-head"><span>Mobile scans</span><strong>{{ $dashboardMobileRate }}%</strong></div>
                                        <div class="widget-progress"><span style="width:{{ $dashboardMobileRate }}%"></span></div>
                                    </div>
                                    <div class="device-item">
                                        <div class="device-head"><span>Desktop scans</span><strong>{{ $dashboardDesktopRate }}%</strong></div>
                                        <div class="widget-progress"><span style="width:{{ $dashboardDesktopRate }}%;background:linear-gradient(90deg,#111827,#64748b)"></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
