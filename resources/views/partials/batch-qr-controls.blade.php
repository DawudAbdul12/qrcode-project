@php
    $qrCode = $qrCode ?? null;
    $batch = $batch ?? null;
    $showExpiry = $showExpiry ?? true;
    $customPathPlaceholder = $batch?->batch_number ? Str::slug($batch->batch_number) : 'akoma-shea-001';
@endphp

<div class="qr-custom-form">
    <label class="form-label">Custom QR Link</label>
    <div class="qr-link-builder">
        <span>{{ url('/verify') }}/</span>
        <input class="input-field" type="text" name="custom_path" value="{{ old('custom_path', $qrCode?->custom_path) }}" placeholder="{{ $customPathPlaceholder }}">
    </div>
    <div class="company-sub-name">Leave blank to use the secure token URL.</div>

    <label class="form-label">QR Design</label>
    <div class="form-row-2">
        <label class="qr-color-field">
            Foreground
            <input type="color" name="foreground_color" value="{{ old('foreground_color', $qrCode?->foreground_color ?? '#111827') }}">
        </label>
        <label class="qr-color-field">
            Background
            <input type="color" name="background_color" value="{{ old('background_color', $qrCode?->background_color ?? '#ffffff') }}">
        </label>
    </div>
    <input class="input-field" type="text" name="label_text" value="{{ old('label_text', $qrCode?->label_text ?? $batch?->batch_number) }}" placeholder="Label text under QR">
    <label class="qr-toggle-row">
        <input type="checkbox" name="show_label" value="1" @checked(old('show_label', $qrCode?->show_label ?? false))>
        Show label text on downloaded QR
    </label>
    <label class="qr-toggle-row">
        <input type="checkbox" name="show_logo" value="1" @checked(old('show_logo', $qrCode?->show_logo ?? false))>
        Show center Active8/company badge
    </label>

    <label class="form-label">Approved External Link</label>
    <input class="input-field" type="text" name="external_link_label" value="{{ old('external_link_label', $qrCode?->external_link_label) }}" placeholder="Button label, e.g. Buy from official shop">
    <input class="input-field" type="url" name="external_link_url" value="{{ old('external_link_url', $qrCode?->external_link_url) }}" placeholder="https://example.com/product">
    <label class="qr-toggle-row">
        <input type="checkbox" name="show_external_link" value="1" @checked(old('show_external_link', $qrCode?->show_external_link ?? false))>
        Show this button on the verified public page
    </label>

    @if ($showExpiry)
        <label class="form-label">QR Expiry Control</label>
        <input class="input-field" type="date" name="qr_expires_at" value="{{ old('qr_expires_at', $qrCode?->expires_at?->format('Y-m-d')) }}">
    @endif
</div>
