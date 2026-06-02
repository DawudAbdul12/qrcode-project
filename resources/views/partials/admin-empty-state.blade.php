<div class="empty-guide">
    <div class="empty-guide-icon">
        <svg width="18" height="18"><use href="#{{ $icon ?? 'i-plus' }}"></use></svg>
    </div>
    <div>
        <div class="empty-guide-title">{{ $title }}</div>
        <div class="empty-guide-copy">{{ $copy }}</div>
        @if (! empty($actionLabel) && ! empty($actionTarget))
            @if (($actionType ?? 'button') === 'link')
                <a class="btn-learn" href="{{ $actionTarget }}">{{ $actionLabel }}</a>
            @else
                <button class="btn-learn" type="button" {!! $actionTarget !!}>{{ $actionLabel }}</button>
            @endif
        @endif
    </div>
</div>
