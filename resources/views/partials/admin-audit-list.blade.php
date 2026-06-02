@php
    $logs = $logs ?? collect();
    $emptyTitle = $emptyTitle ?? 'No audit events yet';
    $emptyCopy = $emptyCopy ?? 'Lifecycle changes will appear here as your team works.';
@endphp

<div class="audit-timeline">
    @forelse ($logs as $log)
        @php
            $actor = $log->user?->name ?? 'System';
            $batchNumber = $log->metadata['batch_number'] ?? null;
            $companyName = $log->metadata['company'] ?? null;
            $eventLabel = Str::headline(str_replace('.', ' ', $log->event));
            $eventInitials = Str::of($eventLabel)->explode(' ')->filter()->map(fn ($word) => Str::substr($word, 0, 1))->take(2)->implode('');
            $isQrEvent = Str::startsWith($log->event, 'qr.');
            $isRiskEvent = Str::contains($log->event, ['revoked', 'archived', 'deleted']);
        @endphp
        <article class="audit-item">
            <div class="audit-dot {{ $isRiskEvent ? 'risk' : ($isQrEvent ? 'qr' : '') }}">{{ $eventInitials ?: 'A' }}</div>
            <div class="audit-event-card">
                <div class="audit-event-head">
                    <div>
                        <div class="audit-event-title">{{ $log->description }}</div>
                        <div class="audit-event-meta">
                            {{ $actor }} · {{ $log->created_at?->format('M j, Y H:i') }}
                        </div>
                    </div>
                    <div class="audit-event-actions">
                        <span class="audit-event-time">{{ $log->created_at?->diffForHumans() }}</span>
                        <span class="status-pill {{ $isRiskEvent ? 'revoked' : ($isQrEvent ? 'active' : '') }}">{{ $eventLabel }}</span>
                    </div>
                </div>
                <div class="audit-chips">
                    @if ($isRiskEvent)
                        <span class="audit-chip danger">Needs review</span>
                    @endif
                    @if ($companyName)
                        <span class="audit-chip">{{ $companyName }}</span>
                    @endif
                    @if ($batchNumber)
                        <span class="audit-chip qr">{{ $batchNumber }}</span>
                    @endif
                    @if (! empty($log->metadata['qr_status']))
                        <span class="audit-chip {{ $log->metadata['qr_status'] === 'revoked' ? 'danger' : 'qr' }}">QR {{ $log->metadata['qr_status'] }}</span>
                    @endif
                    @if (! empty($log->metadata['qa_status']))
                        <span class="audit-chip">QA {{ $log->metadata['qa_status'] }}</span>
                    @endif
                </div>
            </div>
        </article>
    @empty
        <div class="empty-guide" style="margin:18px">
            <div class="empty-guide-icon">
                <svg width="18" height="18"><use href="#i-activity"></use></svg>
            </div>
            <div>
                <div class="empty-guide-title">{{ $emptyTitle }}</div>
                <div class="empty-guide-copy">{{ $emptyCopy }}</div>
            </div>
        </div>
    @endforelse
</div>
