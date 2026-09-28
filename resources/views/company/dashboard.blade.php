@extends('company.layout')

@section('title', $company->name.' Dashboard | Active8')

@push('styles')
    <style>
        .metrics-grid { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:12px; margin-bottom:18px; }
        .metric { min-width:0; display:grid; gap:12px; padding:16px; border:1px solid var(--gray-200); border-radius:var(--radius); background:var(--white); color:inherit; box-shadow:0 10px 28px rgba(17,24,39,.07); transition:transform .16s ease, border-color .16s ease, box-shadow .16s ease; }
        .metric:hover { border-color:rgba(37,99,235,.26); box-shadow:0 16px 34px rgba(37,99,235,.09); transform:translateY(-1px); }
        .metric-head { display:flex; align-items:center; justify-content:space-between; gap:12px; }
        .metric-icon { width:34px; height:34px; display:grid; place-items:center; border-radius:10px; background:var(--blue-light); color:var(--blue); }
        .metric-icon svg { width:17px; height:17px; }
        .metric span { display:block; color:var(--gray-500); font-size:12px; font-weight:700; text-transform:uppercase; line-height:1.3; letter-spacing:.04em; }
        .metric strong { display:block; font-size:25px; line-height:1; }
        .metric.risk .metric-icon { background:var(--red-light); color:var(--red); }
        .metric.success .metric-icon { background:#ecfdf3; color:#067647; }
        .dashboard-grid { display:grid; grid-template-columns:minmax(0, 1.35fr) minmax(320px, .65fr); gap:18px; }
        .chart-grid { display:grid; grid-template-columns:repeat(12, minmax(0, 1fr)); gap:18px; margin-bottom:18px; }
        .chart-span-12 { grid-column:span 12; }
        .chart-span-6 { grid-column:span 6; }
        .chart-card { min-height:286px; }
        .chart-card.trend-card { min-height:380px; }
        .chart-card.compact { min-height:286px; }
        .chart-frame { position:relative; height:210px; }
        .trend-card .chart-frame { height:300px; }
        .chart-card.compact .chart-frame { height:200px; }
        .dashboard-stack { display:grid; gap:18px; }
        .product-rank { display:grid; grid-template-columns:minmax(0, 1fr) auto; gap:12px; align-items:center; padding:13px 0; border-bottom:1px solid var(--gray-100); }
        .product-rank:last-child { border-bottom:0; }
        .rank-count { font-weight:950; color:#1d4ed8; }
        .chart-fallback { display:none; place-items:center; height:100%; padding:18px; border:1px dashed var(--gray-300); border-radius:12px; color:var(--gray-500); font-size:13px; text-align:center; }
        .no-chart .chart-fallback { display:grid; }
        .no-chart canvas { display:none; }
        @media (max-width: 1180px) { .metrics-grid { grid-template-columns:repeat(3, minmax(0, 1fr)); } }
        @media (max-width: 1180px) { .chart-grid { grid-template-columns:1fr; } .chart-span-12, .chart-span-6 { grid-column:span 1; } }
        @media (max-width: 980px) { .dashboard-grid { grid-template-columns:1fr; } }
        @media (max-width: 760px) {
            .metrics-grid { grid-template-columns:repeat(2, minmax(0, 1fr)); gap:10px; }
            .metric { padding:14px; gap:10px; }
            .metric strong { font-size:22px; }
            .chart-grid, .dashboard-grid, .dashboard-stack { gap:14px; }
            .chart-card, .chart-card.compact { min-height:auto; }
            .chart-card.trend-card { min-height:auto; }
            .chart-frame, .chart-card.compact .chart-frame { height:230px; }
            .trend-card .chart-frame { height:260px; }
        }
        @media (max-width: 520px) {
            .metrics-grid { grid-template-columns:1fr; }
            .metric-head { align-items:flex-start; }
            .chart-frame, .chart-card.compact .chart-frame, .trend-card .chart-frame { height:220px; }
            .product-rank { grid-template-columns:1fr; gap:6px; }
            .rank-count { width:max-content; }
        }
    </style>
@endpush

@section('content')
    <div class="company-topbar">
        <div>
            <div class="company-title-row">
                <span class="company-title-icon" aria-hidden="true"><svg><use href="#company-i-activity"></use></svg></span>
                <h1 class="company-title">Dashboard</h1>
            </div>
            <div class="company-subtitle">A focused view of products, batches, labels, QR scans, USSD checks, and suspicious activity for {{ $company->name }}.</div>
        </div>
        <span class="company-pill">{{ ucfirst($company->status) }}</span>
    </div>

    <div class="company-content">
        <section class="metrics-grid" aria-label="Company metrics">
            <a class="metric" href="{{ route('company.products') }}"><div class="metric-head"><span>Total Products</span><div class="metric-icon"><svg><use href="#company-i-bag"></use></svg></div></div><strong>{{ number_format($stats['products']) }}</strong></a>
            <a class="metric" href="{{ route('company.batches') }}"><div class="metric-head"><span>Active Batches</span><div class="metric-icon"><svg><use href="#company-i-layers"></use></svg></div></div><strong>{{ number_format($stats['active_batches']) }}</strong></a>
            <a class="metric" href="{{ route('company.label-orders') }}"><div class="metric-head"><span>Label Orders</span><div class="metric-icon"><svg><use href="#company-i-tag"></use></svg></div></div><strong>{{ number_format($stats['label_orders']) }}</strong></a>
            <a class="metric" href="{{ route('company.verifications', ['channel' => 'qr']) }}"><div class="metric-head"><span>Total QR Scans</span><div class="metric-icon"><svg><use href="#company-i-list"></use></svg></div></div><strong>{{ number_format($stats['qr_scans']) }}</strong></a>
            <a class="metric" href="{{ route('company.ussd-codes') }}"><div class="metric-head"><span>Total USSD Checks</span><div class="metric-icon"><svg><use href="#company-i-code"></use></svg></div></div><strong>{{ number_format($stats['ussd_checks']) }}</strong></a>
            <a class="metric success" href="{{ route('company.verifications', ['status' => 'genuine']) }}"><div class="metric-head"><span>Genuine</span><div class="metric-icon"><svg><use href="#company-i-activity"></use></svg></div></div><strong>{{ number_format($stats['genuine']) }}</strong></a>
            <a class="metric risk" href="{{ route('company.suspicious') }}"><div class="metric-head"><span>Suspicious / Invalid</span><div class="metric-icon"><svg><use href="#company-i-alert"></use></svg></div></div><strong>{{ number_format($stats['flagged']) }}</strong></a>
        </section>

        <section class="chart-grid" aria-label="Company verification charts">
            <div class="admin-card chart-card trend-card chart-span-12">
                <div class="admin-card-header">
                    <div>
                        <div class="admin-card-title">Verification Trend</div>
                        <div class="admin-card-sub">QR and USSD activity over the last 30 days.</div>
                    </div>
                </div>
                <div class="chart-frame">
                    <canvas id="companyTrendChart" aria-label="Verification trend chart"></canvas>
                    <div class="chart-fallback">Charts are unavailable. Use Verification Logs for the same data in table form.</div>
                </div>
            </div>

            <div class="admin-card chart-card compact chart-span-6">
                <div class="admin-card-header">
                    <div>
                        <div class="admin-card-title">Channel Mix</div>
                        <div class="admin-card-sub">How customers verify products.</div>
                    </div>
                </div>
                <div class="chart-frame">
                    <canvas id="companyChannelChart" aria-label="Verification channel chart"></canvas>
                    <div class="chart-fallback">Charts are unavailable. Use Verification Logs for the same data in table form.</div>
                </div>
            </div>

            <div class="admin-card chart-card compact chart-span-6">
                <div class="admin-card-header">
                    <div>
                        <div class="admin-card-title">Outcome Breakdown</div>
                        <div class="admin-card-sub">Genuine checks versus flagged attempts.</div>
                    </div>
                </div>
                <div class="chart-frame">
                    <canvas id="companyOutcomeChart" aria-label="Verification outcome chart"></canvas>
                    <div class="chart-fallback">Charts are unavailable. Use Verification Logs for the same data in table form.</div>
                </div>
            </div>
        </section>

        <section class="dashboard-grid">
            <div class="dashboard-stack">
                <div class="admin-card">
                    <div class="admin-card-header">
                    <div>
                        <div class="admin-card-title">Recent Verification Activity</div>
                        <div class="admin-card-sub">Latest QR and USSD checks tied to this company.</div>
                    </div>
                    <a class="btn-learn" href="{{ route('company.verifications') }}">View Logs</a>
                </div>
                <div class="company-panel-body company-list">
                    @forelse ($recentScans as $scan)
                        @php
                            $productName = $scan->qrCode?->batch?->product?->name
                                ?? $scan->labelCode?->batch?->product?->name
                                ?? 'Unknown product';
                        @endphp
                        <div class="company-item">
                            <div>
                                <div class="company-item-title">{{ $productName }}</div>
                                <div class="muted">{{ strtoupper($scan->device_type ?? 'web') }} · {{ $scan->token_scanned ?? 'No token' }} · {{ $scan->scanned_at?->diffForHumans() }}</div>
                            </div>
                            <div class="company-status {{ $scan->status_result }}">{{ $scan->status_result }}</div>
                        </div>
                    @empty
                        <div class="company-empty">No customer verification activity has been recorded yet.</div>
                    @endforelse
                </div>
            </div>

                <div class="admin-card">
                    <div class="admin-card-header">
                    <div>
                        <div class="admin-card-title">Suspicious Activity</div>
                        <div class="admin-card-sub">Invalid, suspicious, revoked, expired, and recalled checks.</div>
                    </div>
                    <a class="btn-learn" href="{{ route('company.suspicious') }}">Review</a>
                </div>
                <div class="company-panel-body company-list">
                    @forelse ($suspiciousScans as $scan)
                        @php
                            $productName = $scan->qrCode?->batch?->product?->name
                                ?? $scan->labelCode?->batch?->product?->name
                                ?? 'Unknown product';
                        @endphp
                        <div class="company-item">
                            <div>
                                <div class="company-item-title">{{ $productName }}</div>
                                <div class="muted">{{ $scan->token_scanned ?? 'No token' }} · {{ $scan->scanned_at?->diffForHumans() }}</div>
                            </div>
                            <div class="company-status {{ $scan->status_result }}">{{ $scan->status_result }}</div>
                        </div>
                    @empty
                        <div class="company-empty">No suspicious verification activity has been recorded.</div>
                    @endforelse
                </div>
            </div>
        </div>

            <div class="dashboard-stack">
                <div class="admin-card">
                    <div class="admin-card-header">
                    <div>
                        <div class="admin-card-title">Most Checked Products</div>
                        <div class="admin-card-sub">Products with the highest verification activity.</div>
                    </div>
                </div>
                <div class="company-panel-body">
                    @forelse ($topProducts as $product)
                        <div class="product-rank">
                            <div>
                                <div class="company-item-title">{{ $product['name'] }}</div>
                                <div class="muted">{{ ucfirst($product['status']) }}</div>
                            </div>
                            <div class="rank-count">{{ number_format($product['checks']) }}</div>
                        </div>
                    @empty
                        <div class="company-empty">No product verification counts yet.</div>
                    @endforelse
                </div>
            </div>

                <div class="admin-card">
                    <div class="admin-card-header">
                    <div>
                        <div class="admin-card-title">Recent Label Orders</div>
                        <div class="admin-card-sub">Latest print jobs and generated code counts.</div>
                    </div>
                    <a class="btn-learn" href="{{ route('company.label-orders') }}">View Orders</a>
                </div>
                <div class="company-panel-body company-list">
                    @forelse ($recentOrders as $order)
                        <div class="company-item">
                            <div>
                                <div class="company-item-title">{{ $order->batch?->product?->name ?? 'General order' }}</div>
                                <div class="muted">{{ number_format($order->print_quantity) }} {{ $order->label_type }} labels · {{ number_format($order->label_codes_count) }} codes</div>
                            </div>
                            <div class="company-status {{ $order->status }}">{{ $order->status }}</div>
                        </div>
                    @empty
                        <div class="company-empty">No label orders have been created for this company yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (() => {
            const chartData = @json($chartData);
            const gridColor = 'rgba(156, 163, 175, .22)';
            const textColor = '#6b7280';

            if (!window.Chart) {
                document.querySelectorAll('.chart-frame').forEach((frame) => frame.classList.add('no-chart'));
                return;
            }

            const baseOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: textColor,
                            boxWidth: 10,
                            boxHeight: 10,
                            usePointStyle: true,
                        },
                    },
                },
            };

            const trendCanvas = document.getElementById('companyTrendChart');
            if (trendCanvas) {
                new Chart(trendCanvas, {
                    type: 'line',
                    data: {
                        labels: chartData.trend.labels,
                        datasets: [
                            {
                                label: 'Total',
                                data: chartData.trend.total,
                                borderColor: '#2563eb',
                                backgroundColor: 'rgba(37, 99, 235, .12)',
                                tension: .35,
                                fill: true,
                                pointRadius: 2,
                            },
                            {
                                label: 'QR',
                                data: chartData.trend.qr,
                                borderColor: '#067647',
                                backgroundColor: 'rgba(6, 118, 71, .08)',
                                tension: .35,
                                pointRadius: 2,
                            },
                            {
                                label: 'USSD',
                                data: chartData.trend.ussd,
                                borderColor: '#b54708',
                                backgroundColor: 'rgba(181, 71, 8, .08)',
                                tension: .35,
                                pointRadius: 2,
                            },
                        ],
                    },
                    options: {
                        ...baseOptions,
                        scales: {
                            x: { grid: { display: false }, ticks: { color: textColor, maxRotation: 0 } },
                            y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor, precision: 0 } },
                        },
                    },
                });
            }

            const channelCanvas = document.getElementById('companyChannelChart');
            if (channelCanvas) {
                new Chart(channelCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: chartData.channels.labels,
                        datasets: [{
                            data: chartData.channels.values,
                            backgroundColor: ['#2563eb', '#067647', '#9ca3af'],
                            borderColor: '#fff',
                            borderWidth: 3,
                        }],
                    },
                    options: {
                        ...baseOptions,
                        cutout: '68%',
                    },
                });
            }

            const outcomeCanvas = document.getElementById('companyOutcomeChart');
            if (outcomeCanvas) {
                new Chart(outcomeCanvas, {
                    type: 'bar',
                    data: {
                        labels: chartData.outcomes.labels,
                        datasets: [{
                            label: 'Checks',
                            data: chartData.outcomes.values,
                            backgroundColor: ['#067647', '#ef4444', '#b42318', '#7f1d1d', '#b54708', '#9333ea'],
                            borderRadius: 7,
                        }],
                    },
                    options: {
                        ...baseOptions,
                        plugins: { ...baseOptions.plugins, legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: textColor, maxRotation: 0 } },
                            y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor, precision: 0 } },
                        },
                    },
                });
            }
        })();
    </script>
@endpush
