@php
    $isValid = $qrCode !== null;
    $statusMeta = [
        'genuine' => ['label' => 'Verified Genuine', 'title' => 'Authentic product verified', 'message' => $profile?->genuine_message ?? 'This QR code belongs to an approved product batch.', 'tone' => 'success'],
        'suspicious' => ['label' => 'Review Needed', 'title' => 'Extra verification recommended', 'message' => $profile?->suspicious_message ?? 'This code exists, but the batch is not fully approved. Contact support before using this product.', 'tone' => 'warning'],
        'expired' => ['label' => 'Expired Batch', 'title' => 'This batch has expired', 'message' => 'The QR code is real, but the product batch expiry date has passed.', 'tone' => 'warning'],
        'recalled' => ['label' => 'Recalled Product', 'title' => 'Do not use this product', 'message' => 'This product or batch has been recalled. Contact the manufacturer immediately.', 'tone' => 'danger'],
        'revoked' => ['label' => 'Revoked Code', 'title' => 'This QR code is no longer active', 'message' => 'This code was revoked by the manufacturer. Contact support before using this product.', 'tone' => 'danger'],
        'invalid' => ['label' => 'Invalid Code', 'title' => 'Code not recognized', 'message' => $profile?->invalid_message ?? 'This QR code is not recognized by the verification system. The product may not be genuine.', 'tone' => 'danger'],
    ];
    $meta = $statusMeta[$status] ?? $statusMeta['invalid'];
    $primary = $profile?->primary_color ?? '#155EEF';
    $secondary = $profile?->secondary_color ?? '#101828';
    $text = $profile?->text_color ?? '#101828';
    $background = $profile?->background_color ?? '#F6F8FB';
    $success = $profile?->success_color ?? '#16A34A';
    $warning = $profile?->warning_color ?? '#D97706';
    $danger = $profile?->error_color ?? '#DC2626';
    $accent = ['success' => $success, 'warning' => $warning, 'danger' => $danger][$meta['tone']];
    $companyInitials = $company
        ? Str::of($company->name)->explode(' ')->filter()->take(2)->map(fn ($part) => Str::substr($part, 0, 1))->implode('')
        : 'A8';
    $productInitials = $product
        ? Str::of($product->name)->explode(' ')->filter()->take(2)->map(fn ($part) => Str::substr($part, 0, 1))->implode('')
        : $companyInitials;
    $logoUrl = $profile?->logo_path ? '/storage/'.ltrim($profile->logo_path, '/') : null;
    $coverUrl = $profile?->cover_image_path ? '/storage/'.ltrim($profile->cover_image_path, '/') : null;
    $productImageUrl = $product?->image_path ? '/storage/'.ltrim($product->image_path, '/') : null;
    $productGalleryUrls = collect($product?->gallery_paths ?? [])->map(fn ($path) => '/storage/'.ltrim($path, '/'));
    $productVideoEmbedUrl = $product?->videoEmbedUrl();
    $publicSuitability = $product?->suitabilityItems?->where('is_public', true) ?? collect();
    $suitabilityTypeLabels = [
        'age' => 'Age', 'audience' => 'Audience', 'allergen' => 'Allergen', 'dietary' => 'Dietary',
        'pregnancy' => 'Pregnancy', 'medical' => 'Medical', 'usage' => 'Usage', 'safety' => 'Safety',
        'storage' => 'Storage', 'custom' => 'Guidance',
    ];
    $supportPhone = $profile?->support_phone ?? $company?->phone;
    $whatsAppSocial = collect($profile?->social_links ?? [])->first(fn ($link) => Str::lower($link['label'] ?? '') === 'whatsapp');
    $whatsAppUrl = $whatsAppSocial['url'] ?? null;
    if (! $whatsAppUrl && $supportPhone) {
        $whatsAppNumber = preg_replace('/\D+/', '', $supportPhone);
        $whatsAppUrl = $whatsAppNumber ? 'https://wa.me/'.$whatsAppNumber : null;
    }
    $certificateUrl = $profile?->certificate_path ? '/storage/'.ltrim($profile->certificate_path, '/') : null;
    $needsImmediateSupport = in_array($status, ['suspicious', 'expired', 'recalled', 'revoked', 'invalid'], true);
    $isHardStop = in_array($status, ['recalled', 'revoked'], true);
    $isSuspicious = $status === 'suspicious';
    $isExpired = $status === 'expired';
    $showPromotionalMedia = ! $isSuspicious && ! $isExpired;
    $showFaqs = ! $isSuspicious;
    $showIngredients = ! $isSuspicious;
    $showUsageInstructions = ! $isSuspicious && ! $isExpired;
    $showFooter = ! in_array($status, ['recalled', 'revoked', 'invalid'], true);
    $supportGuidance = match ($status) {
        'recalled' => 'Stop using or distributing this product immediately. Contact the manufacturer for the next steps.',
        'revoked' => 'Do not rely on this QR code. Contact the manufacturer to confirm the product before use or distribution.',
        'expired' => 'This product is past its expiry date. Contact the manufacturer before use or distribution.',
        'suspicious' => 'This product could not be fully verified. Contact the manufacturer before use or distribution.',
        'invalid' => 'This QR code could not be verified. Contact the manufacturer or retailer if you need help.',
        default => null,
    };
    $iconPaths = [
        'phone' => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.4 2.1L8 9.7a16 16 0 0 0 6.3 6.3l1.3-1.3a2 2 0 0 1 2.1-.4c.8.3 1.7.5 2.6.6A2 2 0 0 1 22 16.9Z"/>',
        'email' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'website' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
        'certificate' => '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Z"/><path d="M14 3v6h6M8 13h8M8 17h6"/>',
        'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/>',
        'facebook' => '<path d="M14 8h3V4h-3a5 5 0 0 0-5 5v3H6v4h3v6h4v-6h3.5l.5-4h-4V9a1 1 0 0 1 1-1Z"/>',
        'x' => '<path d="M18 3 6 21M7 3l10 18"/>',
        'linkedin' => '<path d="M7 10v11M7 6v.01M11 21v-6a4 4 0 0 1 8 0v6M11 10v11"/>',
        'youtube' => '<path d="M22 12s0-3-1-4-2-1-9-1-8 0-9 1-1 1-1 4 0 3 1 4 2 1 9 1 8 0 9-1 1-1 1-4Z"/><path d="m10 15 5-3-5-3Z"/>',
        'tiktok' => '<path d="M15 4v10a4 4 0 1 1-3-3.9M15 4c.8 2 2 3 4 3"/>',
        'whatsapp' => '<path d="M20 11.5a8 8 0 0 1-11.9 7L4 20l1.5-4A8 8 0 1 1 20 11.5Z"/><path d="M9 8c.4 3 2 4.6 5 5"/>',
        'social' => '<path d="M16 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM8 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm8 7a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM10.5 13.5l3 4M13.5 6.5l-3 4"/>',
    ];
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isValid ? $product?->name.' | Verified Product' : 'Product Verification' }}</title>
    <style>
        :root {
            --primary: {{ $primary }};
            --secondary: {{ $secondary }};
            --text: {{ $text }};
            --background: {{ $background }};
            --accent: {{ $accent }};
            --success: {{ $success }};
            --warning: {{ $warning }};
            --danger: {{ $danger }};
            --muted: #667085;
            --line: rgba(16, 24, 40, .1);
            --card: #fff;
            --shadow: 0 14px 42px rgba(16, 24, 40, .09);
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { margin: 0; min-height: 100vh; color: var(--text); background: #f4f7fa; font-family: "{{ $profile?->font_family ?? 'DM Sans' }}", Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        a { color: inherit; text-decoration: none; }
        a, button { transition: border-color .18s ease, background .18s ease, color .18s ease, box-shadow .18s ease, transform .18s ease; }
        a:focus-visible, button:focus-visible, summary:focus-visible { outline: 3px solid color-mix(in srgb, var(--primary) 28%, transparent); outline-offset: 3px; }
        .page { width: min(900px, calc(100% - 28px)); margin: 0 auto; padding: 18px 0 42px; }
        .banner { position: relative; min-height: 310px; overflow: hidden; border-radius: 22px 22px 0 0; color: #fff; background: var(--secondary); }
        .banner::after { content: ""; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(16,24,40,.12), rgba(16,24,40,.82)); }
        .banner-cover { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .banner-content { position: absolute; z-index: 1; inset: auto 28px 26px; display: flex; align-items: flex-end; gap: 18px; }
        .banner-video { position: absolute; z-index: 2; right: 24px; top: 22px; display: inline-flex; align-items: center; gap: 9px; padding: 8px 12px 8px 8px; border: 1px solid rgba(255,255,255,.26); border-radius: 999px; background: rgba(16,24,40,.5); color: #fff; font-size: 11px; font-weight: 900; text-decoration: none; backdrop-filter: blur(12px); }
        .banner-video-icon { width: 30px; height: 30px; display: grid; place-items: center; border-radius: 50%; background: #fff; color: var(--primary); }
        .banner-video-icon svg { margin-left: 2px; }
        .product-avatar { width: 112px; height: 112px; display: grid; place-items: center; flex-shrink: 0; overflow: hidden; border: 4px solid rgba(255,255,255,.94); border-radius: 22px; background: #fff; color: var(--primary); box-shadow: 0 14px 32px rgba(16,24,40,.28); font-size: 27px; font-weight: 900; }
        .product-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .product-kicker { margin-bottom: 4px; color: rgba(255,255,255,.78); font-size: 12px; font-weight: 800; text-transform: uppercase; }
        h1 { max-width: 680px; margin: 0; font-size: 46px; line-height: 1.02; letter-spacing: 0; overflow-wrap: anywhere; }
        .company-name { margin-top: 7px; color: rgba(255,255,255,.84); font-size: 15px; font-weight: 700; }
        .profile { overflow: hidden; border: 1px solid rgba(16,24,40,.08); border-radius: 22px; background: var(--card); box-shadow: var(--shadow); }
        .profile-body { padding: 22px; background: #fff; }
        .verification { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 16px; align-items: center; padding: 18px; border: 1px solid color-mix(in srgb, var(--accent) 22%, white); border-left: 4px solid var(--accent); border-radius: 12px; background: #fff; }
        .badge { display: inline-flex; align-items: center; gap: 8px; padding: 6px 9px; border-radius: 999px; background: color-mix(in srgb, var(--accent) 9%, white); color: var(--accent); font-size: 11px; font-weight: 900; text-transform: uppercase; }
        .dot { width: 9px; height: 9px; border-radius: 50%; background: var(--accent); box-shadow: 0 0 0 4px color-mix(in srgb, var(--accent) 18%, transparent); }
        .verification h2 { margin: 7px 0 3px; font-size: 19px; }
        .verification p, .copy { margin: 0; color: #475467; font-size: 14px; line-height: 1.6; }
        .batch-context { display: flex; flex-wrap: wrap; gap: 8px 18px; margin-top: 15px; padding: 0 2px; color: var(--muted); font-size: 12px; }
        .batch-context strong { color: var(--secondary); }
        .verified-at { margin-top: 7px; color: var(--muted); font-size: 11px; }
        .profile-tools { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 15px; }
        .share-button { display: inline-flex; align-items: center; gap: 7px; flex-shrink: 0; padding: 8px 11px; border: 1px solid var(--line); border-radius: 999px; background: #fff; color: var(--secondary); font-size: 11px; font-weight: 900; cursor: pointer; }
        .share-button:hover { border-color: color-mix(in srgb, var(--primary) 34%, white); color: var(--primary); box-shadow: 0 5px 14px rgba(16,24,40,.06); }
        .share-status { color: var(--success); font-size: 11px; font-weight: 800; }
        .profile-nav { display: flex; gap: 7px; overflow-x: auto; margin-top: 17px; padding-bottom: 2px; scrollbar-width: none; }
        .profile-nav::-webkit-scrollbar { display: none; }
        .profile-nav a { flex-shrink: 0; padding: 8px 11px; border: 1px solid var(--line); border-radius: 999px; background: #fff; color: var(--secondary); font-size: 11px; font-weight: 900; text-decoration: none; }
        .profile-nav a:hover { border-color: color-mix(in srgb, var(--primary) 38%, white); background: color-mix(in srgb, var(--primary) 5%, white); color: var(--primary); }
        .label { color: var(--muted); font-size: 10px; font-weight: 900; text-transform: uppercase; }
        .value { margin-top: 6px; color: var(--secondary); font-size: 14px; font-weight: 900; overflow-wrap: anywhere; }
        .section { margin-top: 14px; padding: 20px; scroll-margin-top: 16px; border: 1px solid rgba(16,24,40,.08); border-radius: 16px; background: var(--card); box-shadow: 0 6px 18px rgba(16,24,40,.035); }
        .section-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 14px; margin-bottom: 14px; }
        .section h2 { margin: 0; font-size: 20px; letter-spacing: 0; }
        .section-sub { margin-top: 4px; color: var(--muted); font-size: 12px; line-height: 1.45; }
        .urgent-support { margin-top: 14px; padding: 14px; border: 1px solid color-mix(in srgb, var(--accent) 28%, white); border-radius: 12px; background: color-mix(in srgb, var(--accent) 6%, white); }
        .urgent-support strong { display: block; color: var(--secondary); font-size: 14px; }
        .urgent-support .copy { margin-top: 3px; font-size: 13px; }
        .guidance-list, .faq { display: grid; gap: 9px; }
        .guidance { display: grid; grid-template-columns: 9px minmax(0, 1fr); gap: 11px; padding: 12px; border: 1px solid var(--line); border-radius: 12px; background: #fff; transition: border-color .18s ease, box-shadow .18s ease; }
        .guidance:hover { border-color: color-mix(in srgb, var(--primary) 24%, white); box-shadow: 0 5px 14px rgba(16,24,40,.04); }
        .guidance-dot { width: 8px; height: 8px; margin-top: 5px; border-radius: 50%; background: var(--primary); }
        .guidance.caution .guidance-dot { background: var(--warning); }
        .guidance.warning .guidance-dot { background: var(--danger); }
        .guidance-title { display: flex; justify-content: space-between; gap: 12px; color: var(--secondary); font-size: 14px; font-weight: 900; }
        .guidance-type { color: var(--muted); font-size: 10px; text-transform: uppercase; white-space: nowrap; }
        .guidance-copy { margin-top: 4px; color: #475467; font-size: 13px; line-height: 1.5; }
        details { border: 1px solid var(--line); border-radius: 12px; background: #fff; transition: border-color .18s ease, box-shadow .18s ease; }
        details[open] { border-color: color-mix(in srgb, var(--primary) 25%, white); box-shadow: 0 5px 14px rgba(16,24,40,.04); }
        summary { padding: 13px 14px; color: var(--secondary); font-size: 14px; font-weight: 900; cursor: pointer; }
        details .copy { padding: 0 14px 14px; }
        .info-list { display: grid; gap: 0; margin-top: 14px; border-top: 1px solid var(--line); }
        .row { display: flex; justify-content: space-between; gap: 18px; padding: 11px 0; border-bottom: 1px solid var(--line); font-size: 13px; }
        .row span:first-child { color: var(--muted); }
        .row span:last-child { max-width: 62%; text-align: right; font-weight: 800; overflow-wrap: anywhere; }
        .product-video { overflow: hidden; aspect-ratio: 16 / 9; border-radius: 14px; background: #101828; }
        .product-video iframe { width: 100%; height: 100%; border: 0; }
        .gallery { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 9px; }
        .gallery a { overflow: hidden; aspect-ratio: 1; border: 1px solid var(--line); border-radius: 12px; background: #f8fafc; }
        .gallery img { width: 100%; height: 100%; object-fit: cover; transition: transform .2s ease; }
        .gallery a:hover img { transform: scale(1.04); }
        .content-copy { white-space: pre-line; }
        .company-card { display: flex; align-items: flex-start; gap: 13px; padding: 13px; border: 1px solid rgba(16,24,40,.07); border-radius: 13px; background: #f8fafc; }
        .company-logo { width: 52px; height: 52px; display: grid; place-items: center; flex-shrink: 0; overflow: hidden; border-radius: 14px; background: color-mix(in srgb, var(--primary) 10%, white); color: var(--primary); font-weight: 900; }
        .company-logo img { width: 100%; height: 100%; object-fit: cover; }
        .company-card h3 { margin: 0 0 4px; font-size: 17px; }
        .actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
        .button { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 39px; padding: 0 12px; border: 1px solid var(--line); border-radius: 10px; background: #fff; color: var(--secondary); font-size: 12px; font-weight: 900; text-decoration: none; }
        .button svg { flex-shrink: 0; }
        .social-button { width: 39px; padding: 0; }
        .social-button:hover, .button:hover { border-color: color-mix(in srgb, var(--primary) 34%, white); color: var(--primary); box-shadow: 0 5px 14px rgba(16,24,40,.06); transform: translateY(-1px); }
        .whatsapp { position: fixed; z-index: 5; right: max(16px, calc((100vw - 920px) / 2)); bottom: 20px; width: 58px; height: 58px; display: grid; place-items: center; border: 2px solid rgba(255,255,255,.92); border-radius: 50%; background: #25D366; color: #fff; box-shadow: 0 12px 26px rgba(18,140,126,.34); text-decoration: none; }
        .whatsapp::before { content: attr(data-label); position: absolute; right: 68px; padding: 8px 10px; border-radius: 8px; background: #101828; color: #fff; font-size: 11px; font-weight: 800; white-space: nowrap; opacity: 0; pointer-events: none; transform: translateX(5px); transition: opacity .18s ease, transform .18s ease; }
        .whatsapp:hover { transform: translateY(-2px); box-shadow: 0 16px 30px rgba(18,140,126,.4); }
        .whatsapp:hover::before { opacity: 1; transform: translateX(0); }
        .footer { padding: 18px 0 0; color: var(--muted); font-size: 12px; text-align: center; }
        @media (max-width: 640px) {
            .page { width: min(100% - 16px, 560px); padding-top: 8px; }
            .banner { min-height: 250px; }
            .banner-content { inset: auto 16px 16px; gap: 12px; }
            .banner-video { right: 14px; top: 14px; padding-right: 10px; }
            .product-avatar { width: 80px; height: 80px; border-radius: 16px; font-size: 20px; }
            h1 { font-size: 30px; }
            .profile-body, .section { padding: 15px; }
            .verification { grid-template-columns: minmax(0, 1fr) auto; padding: 13px; }
            .verification h2 { font-size: 17px; }
            .row { gap: 10px; }
            .row span:last-child { max-width: 58%; }
            .gallery { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .whatsapp { right: 14px; bottom: 14px; }
        }
    </style>
</head>
<body>
    <main class="page">
        <article class="profile">
            <header class="banner">
                @if ($coverUrl)<img class="banner-cover" src="{{ $coverUrl }}" alt="">@endif
                @if ($productVideoEmbedUrl && $showPromotionalMedia && ! $isHardStop)
                    <a class="banner-video" href="#product-video">
                        <span class="banner-video-icon"><svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true"><path d="m8 5 11 7-11 7Z" fill="currentColor"/></svg></span>
                        <span>Watch product video</span>
                    </a>
                @endif
                <div class="banner-content">
                    <div class="product-avatar">@if($productImageUrl)<img src="{{ $productImageUrl }}" alt="{{ $product?->name }}">@else{{ $productInitials }}@endif</div>
                    <div>
                        <div class="product-kicker">Verified Product Profile</div>
                        <h1>{{ $product?->name ?? 'Product verification' }}</h1>
                        <div class="company-name">{{ $company?->name ?? 'Active8 Verification' }}</div>
                    </div>
                </div>
            </header>

            <div class="profile-body">
                <div class="verification">
                    <div>
                        <div class="badge"><span class="dot"></span>{{ $meta['label'] }}</div>
                        <h2>{{ $meta['title'] }}</h2>
                        <p>{{ $meta['message'] }}</p>
                    </div>
                </div>

                <div class="batch-context">
                    <span>Batch <strong>{{ $batch?->batch_number ?? 'Not available' }}</strong></span>
                    @if ($batch?->expiry_date)<span>Expires <strong>{{ $batch->expiry_date->format('M j, Y') }}</strong></span>@endif
                </div>
                <div class="profile-tools">
                    <div>
                        <div class="verified-at">Verified on {{ now()->format('M j, Y \a\t H:i') }}</div>
                        <div class="share-status" data-share-status></div>
                    </div>
                    <button class="share-button" type="button" data-share-page>
                        <svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 16V3m0 0L7 8m5-5 5 5M5 12v8h14v-8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Share
                    </button>
                </div>

                @if ($needsImmediateSupport)
                    <div class="urgent-support">
                        <strong>Need help with this product?</strong>
                        <p class="copy">{{ $supportGuidance }}</p>
                        <div class="actions">
                            @if($whatsAppUrl)<a class="button" href="{{ $whatsAppUrl }}" target="_blank" rel="noopener noreferrer"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $iconPaths['whatsapp'] !!}</svg>WhatsApp Support</a>@endif
                            @if($supportPhone)<a class="button" href="tel:{{ $supportPhone }}"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $iconPaths['phone'] !!}</svg>Call Support</a>@endif
                        </div>
                    </div>
                @endif

                @if ($isValid && ! $isHardStop)
                    <nav class="profile-nav" aria-label="Product profile sections">
                        @if($publicSuitability->isNotEmpty())<a href="#suitability">Warnings</a>@endif
                        @if($showFaqs && ! empty($product?->faq_items))<a href="#faqs">FAQs</a>@endif
                        @if($productGalleryUrls->isNotEmpty())<a href="#gallery">Gallery</a>@endif
                        @if($showPromotionalMedia && $productVideoEmbedUrl)<a href="#product-video">Video</a>@endif
                        @if($showIngredients && $product?->ingredients)<a href="#ingredients">Ingredients</a>@endif
                        @if($showUsageInstructions && $product?->usage_instructions)<a href="#how-to-use">How to Use</a>@endif
                        <a href="#about-product">About Product</a>
                        <a href="#company">Company</a>
                    </nav>
                @endif
            </div>
        </article>

        @if ($isValid && ! $isHardStop)
            @if ($publicSuitability->isNotEmpty())
                <section class="section" id="suitability">
                    <div class="section-head"><div><h2>Suitability & Warnings</h2><div class="section-sub">Read before using this product.</div></div></div>
                    <div class="guidance-list">
                        @foreach ($publicSuitability as $item)
                            <div class="guidance {{ $item->severity }}">
                                <span class="guidance-dot"></span>
                                <div><div class="guidance-title"><span>{{ $item->short_label }}</span><span class="guidance-type">{{ $suitabilityTypeLabels[$item->type] ?? 'Guidance' }}</span></div>@if($item->detail)<div class="guidance-copy">{{ $item->detail }}</div>@endif</div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($showFaqs && ! empty($product?->faq_items))
                <section class="section" id="faqs">
                    <div class="section-head"><div><h2>FAQs</h2><div class="section-sub">Common questions about {{ $product->name }}.</div></div></div>
                    <div class="faq">
                        @foreach ($product->faq_items as $item)
                            <details><summary>{{ $item['question'] ?? 'Question' }}</summary><p class="copy">{{ $item['answer'] ?? '' }}</p></details>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($productGalleryUrls->isNotEmpty())
                <section class="section" id="gallery">
                    <div class="section-head"><div><h2>Product Gallery</h2><div class="section-sub">Compare the product and packaging with the item in hand.</div></div></div>
                    <div class="gallery">
                        @foreach($productGalleryUrls as $galleryUrl)<a href="{{ $galleryUrl }}" target="_blank" rel="noopener"><img src="{{ $galleryUrl }}" alt="{{ $product->name }} gallery image"></a>@endforeach
                    </div>
                </section>
            @endif

            @if ($showPromotionalMedia && $productVideoEmbedUrl)
                <section class="section" id="product-video">
                    <div class="section-head"><div><h2>Product Video</h2><div class="section-sub">Official guide or product story.</div></div></div>
                    <div class="product-video"><iframe src="{{ $productVideoEmbedUrl }}" title="{{ $product->name }} product video" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe></div>
                </section>
            @endif

            @if ($showIngredients && $product?->ingredients)
                <section class="section" id="ingredients">
                    <div class="section-head"><div><h2>Ingredients / Materials</h2><div class="section-sub">Published composition details for this product.</div></div></div>
                    <p class="copy content-copy">{{ $product->ingredients }}</p>
                </section>
            @endif

            @if ($showUsageInstructions && $product?->usage_instructions)
                <section class="section" id="how-to-use">
                    <div class="section-head"><div><h2>How to Use</h2><div class="section-sub">Official usage, care, or storage guidance.</div></div></div>
                    <p class="copy content-copy">{{ $product->usage_instructions }}</p>
                </section>
            @endif

            <section class="section" id="about-product">
                <div class="section-head"><div><h2>About Product</h2><div class="section-sub">Official product and batch information.</div></div></div>
                <p class="copy">{{ $product?->description ?? 'No product description has been published yet.' }}</p>
                <div class="info-list">
                    <div class="row"><span>SKU</span><span>{{ $product?->sku ?? 'Not listed' }}</span></div>
                    <div class="row"><span>Category</span><span>{{ $product?->category ?? 'Not listed' }}</span></div>
                    <div class="row"><span>Manufacturer</span><span>{{ $product?->manufacturer ?? $profile?->manufacturer_name ?? 'Not listed' }}</span></div>
                    <div class="row"><span>Origin</span><span>{{ $product?->country_of_origin ?? $company?->country ?? 'Not listed' }}</span></div>
                    <div class="row"><span>Manufactured</span><span>{{ $batch?->manufacture_date?->format('M j, Y') ?? 'Not listed' }}</span></div>
                    <div class="row"><span>Expires</span><span>{{ $batch?->expiry_date?->format('M j, Y') ?? 'Not listed' }}</span></div>
                    <div class="row"><span>Production Location</span><span>{{ $batch?->production_location ?? 'Not listed' }}</span></div>
                </div>
                @if($certificateUrl || $batch?->certificate_path || $profile?->certification_number)
                    <div class="actions">
                        @if($certificateUrl)<a class="button" href="{{ $certificateUrl }}" target="_blank" rel="noopener"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $iconPaths['certificate'] !!}</svg>View Certificate</a>@else<span class="button"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $iconPaths['certificate'] !!}</svg>Certificate {{ $batch?->certificate_path ?? $profile?->certification_number }}</span>@endif
                    </div>
                @endif
            </section>

            <section class="section" id="company">
                <div class="section-head"><div><h2>Company</h2><div class="section-sub">Official manufacturer profile and support channels.</div></div></div>
                <div class="company-card">
                    <div class="company-logo">@if($logoUrl)<img src="{{ $logoUrl }}" alt="{{ $company?->name }}">@else{{ $companyInitials }}@endif</div>
                    <div><h3>{{ $company?->name ?? 'Manufacturer' }}</h3><p class="copy">{{ $profile?->company_description ?? $company?->notes ?? 'This company profile is maintained by the verification platform administrator.' }}</p></div>
                </div>
                @foreach (($profile?->custom_sections ?? []) as $customSection)
                    <div class="info-list"><div class="row"><span>{{ $customSection['title'] ?? 'More Information' }}</span><span>{{ $customSection['body'] ?? '' }}</span></div></div>
                @endforeach
                <div class="actions">
                    @if($supportPhone)<a class="button" href="tel:{{ $supportPhone }}"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $iconPaths['phone'] !!}</svg>Call</a>@endif
                    @if($profile?->support_email || $company?->email)<a class="button" href="mailto:{{ $profile?->support_email ?? $company?->email }}"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $iconPaths['email'] !!}</svg>Email</a>@endif
                    @if($profile?->website_url || $company?->website)<a class="button" href="{{ $profile?->website_url ?? $company?->website }}" target="_blank" rel="noopener"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $iconPaths['website'] !!}</svg>Website</a>@endif
                    @foreach(($profile?->social_links ?? []) as $socialLink)
                        @php($socialKey = Str::lower($socialLink['label'] ?? 'social'))
                        @if(! empty($socialLink['url']))<a class="button social-button" href="{{ $socialLink['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $socialLink['label'] ?? 'Social link' }}" title="{{ $socialLink['label'] ?? 'Social link' }}"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $iconPaths[$socialKey] ?? $iconPaths['social'] !!}</svg></a>@endif
                    @endforeach
                </div>
            </section>
        @endif

        @if ($showFooter && ($profile?->show_platform_branding ?? true))<footer class="footer">Secured by Active8 batch verification</footer>@endif
    </main>

    @if ($whatsAppUrl)
        <a class="whatsapp" href="{{ $whatsAppUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Chat with support on WhatsApp" data-label="Chat on WhatsApp">
            <svg width="29" height="29" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 3.5A11.7 11.7 0 0 0 12.1 0C5.6 0 .4 5.2.4 11.7c0 2.1.5 4.1 1.6 5.9L0 24l6.6-1.9c1.7.9 3.6 1.4 5.5 1.4h.1c6.4 0 11.7-5.2 11.7-11.7 0-3.1-1.2-6.1-3.4-8.3Zm-8.3 18c-1.7 0-3.4-.5-4.9-1.3l-.5-.3-3.9 1.2 1.1-3.8-.3-.5a9.6 9.6 0 1 1 8.5 4.7Zm5.3-7.2c-.3-.1-1.7-.8-1.9-.9-.3-.1-.5-.1-.7.2-.2.3-.7.9-.9 1.1-.2.2-.3.2-.6.1-1.7-.8-2.8-1.5-3.9-3.4-.3-.5.3-.5.8-1.6.1-.2 0-.4-.1-.6-.1-.1-.7-1.6-.9-2.2-.2-.6-.5-.5-.7-.5H8c-.2 0-.6.1-.9.4-.3.3-1.2 1.2-1.2 2.9s1.2 3.3 1.4 3.6c.2.2 2.4 3.7 5.9 5.2 2.2.9 3 .9 4.1.8.7-.1 1.7-.7 1.9-1.3.2-.6.2-1.2.2-1.3-.1-.2-.3-.3-.6-.4Z" fill="currentColor"/></svg>
        </a>
    @endif
    <script>
        const shareButton = document.querySelector('[data-share-page]');
        const shareStatus = document.querySelector('[data-share-status]');
        shareButton?.addEventListener('click', async () => {
            const data = { title: document.title, text: 'View this verified product profile.', url: window.location.href };
            try {
                if (navigator.share) {
                    await navigator.share(data);
                } else {
                    await navigator.clipboard.writeText(window.location.href);
                    if (shareStatus) shareStatus.textContent = 'Verification link copied';
                }
            } catch (error) {
                if (error.name !== 'AbortError' && shareStatus) shareStatus.textContent = 'Unable to share link';
            }
        });
    </script>
</body>
</html>
