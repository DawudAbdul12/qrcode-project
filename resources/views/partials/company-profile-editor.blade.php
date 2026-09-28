@php
    $profile = $companyProfile;
    $profileDefaults = [
        'layout_style' => $profile?->layout_style ?? 'default',
        'primary_color' => $profile?->primary_color ?? '#2563EB',
        'secondary_color' => $profile?->secondary_color ?? '#111827',
        'text_color' => $profile?->text_color ?? '#111827',
        'background_color' => $profile?->background_color ?? '#FFFFFF',
        'button_color' => $profile?->button_color ?? '#2563EB',
        'button_text_color' => $profile?->button_text_color ?? '#FFFFFF',
        'success_color' => $profile?->success_color ?? '#22C55E',
        'warning_color' => $profile?->warning_color ?? '#F59E0B',
        'error_color' => $profile?->error_color ?? '#EF4444',
        'font_family' => $profile?->font_family ?? 'DM Sans',
        'badge_style' => $profile?->badge_style ?? 'official',
        'button_style' => $profile?->button_style ?? 'solid',
    ];
    $profileScore = collect([
        $profile?->logo_path,
        $profile?->cover_image_path,
        $profile?->verification_headline,
        $profile?->support_email ?: $profile?->support_phone,
        $profile?->company_description,
    ])->filter()->count() * 20;
    $previewProduct = $products->firstWhere('company_id', $company?->id);
    $previewBatch = $batches->first(fn ($batch) => $batch->product?->company_id === $company?->id);
    $previewProductInitials = $previewProduct
        ? Str::of($previewProduct->name)->explode(' ')->filter()->take(2)->map(fn ($word) => Str::substr($word, 0, 1))->implode('')
        : 'PR';
    $previewCompanyInitials = Str::of($company?->name ?? 'Active8')->explode(' ')->filter()->take(2)->map(fn ($word) => Str::substr($word, 0, 1))->implode('');
    $previewProductImage = $previewProduct?->image_path ? '/storage/'.ltrim($previewProduct->image_path, '/') : null;
    $previewCompanyLogo = $profile?->logo_path ? '/storage/'.ltrim($profile->logo_path, '/') : null;
@endphp
<div class="topbar">
    <div>
        <div class="page-title" id="editor-page-title">About</div>
        <div class="page-subtitle" id="editor-page-subtitle">Manage the official company identity shown on the verification page.</div>
    </div>
    <div class="topbar-actions">
        <span class="editor-save-state" data-editor-save-state>All changes saved</span>
        <label class="editor-company-context">
            <span class="company-sub-name">Editing</span>
            <select class="font-select" onchange="window.location.href=this.value" aria-label="Choose company profile">
                @foreach ($companies as $item)
                    <option value="{{ route('active8.page', ['page' => 'company-profile', 'company' => $item->id]) }}" @selected($company?->id === $item->id)>{{ $item->name }}</option>
                @endforeach
            </select>
        </label>
        <a class="btn-learn" href="{{ route('companies.show', $company) }}">Workspace</a>
        @if ($editorQrCode)
            <a class="btn-learn" href="{{ '/verify/'.$editorQrCode->token }}" target="_blank" rel="noopener">Public Preview</a>
        @endif
        <button class="btn-learn" type="submit" form="company-profile-editor" name="publish_action" value="draft">Save Draft</button>
        <button class="btn-share" type="submit" form="company-profile-editor" name="publish_action" value="publish">Publish</button>
    </div>
</div>

<div class="tabs">
    @foreach ($editorTabs as $tab)
        <button class="tab {{ $loop->first ? 'active' : '' }}" type="button" data-editor-tab="{{ $tab['key'] }}">
            <svg width="13" height="13"><use href="#i-{{ $tab['icon'] }}"></use></svg>{{ $tab['label'] }}
        </button>
    @endforeach
</div>

<form class="editor-layout" id="company-profile-editor" method="POST" action="{{ route('companies.profile.update', $company) }}" enctype="multipart/form-data" data-profile-editor>
    @csrf
    @method('PUT')
    <div class="editor-scroll">
        @if (session('status'))<div class="alert-success">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="alert-error">{{ $errors->first() }}</div>@endif

        <div class="editor-page" id="epage-content">
            <div class="editor-publish-strip">
                <div><strong>{{ ucfirst($profile?->publication_status ?? 'draft') }} profile</strong><span>{{ $profile?->published_at ? 'Last published '.$profile->published_at->diffForHumans() : 'Publish when the customer-facing content is ready.' }}</span></div>
                <div class="profile-meter"><span style="width:{{ $profileScore }}%"></span></div>
                <b>{{ $profileScore }}% complete</b>
            </div>
            <div class="section-block">
                <div class="section-header"><span class="section-header-title">Verification Messages</span><span class="status-pill active">Public</span></div>
                <div class="section-body">
                    <div class="section-label">Verification Headline</div>
                    <input class="input-field" name="profile[verification_headline]" value="{{ old('profile.verification_headline', $profile?->verification_headline ?? 'Verify your original product') }}" data-preview-headline>
                    <div class="section-label">Genuine Product Message</div>
                    <textarea class="textarea-field" name="profile[genuine_message]" data-preview-message="genuine">{{ old('profile.genuine_message', $profile?->genuine_message ?? 'This product is verified as part of an approved production batch.') }}</textarea>
                    <div class="section-label">Suspicious Scan Message</div>
                    <textarea class="textarea-field" name="profile[suspicious_message]" data-preview-message="suspicious">{{ old('profile.suspicious_message', $profile?->suspicious_message ?? 'This QR code has unusual scan activity. Please contact support before using this product.') }}</textarea>
                    <div class="section-label">Invalid QR Message</div>
                    <textarea class="textarea-field" name="profile[invalid_message]" data-preview-message="invalid">{{ old('profile.invalid_message', $profile?->invalid_message ?? 'This code is not recognized by our verification system.') }}</textarea>
                </div>
            </div>
            <div class="section-block">
                <div class="section-header"><span class="section-header-title">Support and Links</span><span class="status-pill active">Public</span></div>
                <div class="section-body">
                    <div class="section-label">Support Text</div>
                    <textarea class="textarea-field" name="profile[support_text]">{{ old('profile.support_text', $profile?->support_text) }}</textarea>
                    <div class="form-row-2">
                        <div><div class="section-label">Support Email</div><input class="input-field" type="email" name="profile[support_email]" value="{{ old('profile.support_email', $profile?->support_email ?? $company?->email) }}"></div>
                        <div><div class="section-label">Support Phone</div><input class="input-field" name="profile[support_phone]" value="{{ old('profile.support_phone', $profile?->support_phone ?? $company?->phone) }}"></div>
                    </div>
                    <div class="section-label">Website URL</div>
                    <input class="input-field" type="url" name="profile[website_url]" value="{{ old('profile.website_url', $profile?->website_url ?? $company?->website) }}">
                    <div class="section-label">Social Links</div>
                    <div data-editor-repeat-list="social">
                        @foreach (old('profile.social_links', $profile?->social_links ?? []) as $link)
                            <div class="repeat-row" data-editor-repeat-row><span class="drag-handle">⠿</span><select class="font-select" name="profile[social_links][{{ $loop->index }}][label]">@foreach(['Instagram','Facebook','X','LinkedIn','YouTube','TikTok','WhatsApp'] as $network)<option @selected(($link['label'] ?? '') === $network)>{{ $network }}</option>@endforeach</select><input class="input-field" type="url" name="profile[social_links][{{ $loop->index }}][url]" value="{{ $link['url'] ?? '' }}" placeholder="https://"><button class="icon-btn del" type="button" data-editor-repeat-remove>×</button></div>
                        @endforeach
                    </div>
                    <button class="btn-add" type="button" data-editor-repeat-add="social">+ Add Social Link</button>
                </div>
            </div>
            <div class="section-block">
                <div class="section-header"><span class="section-header-title">Custom Public Sections</span><span class="status-pill active">Public</span></div>
                <div class="section-body">
                    <div data-editor-repeat-list="section">
                        @foreach (old('profile.custom_sections', $profile?->custom_sections ?? []) as $section)
                            <div class="repeat-row stacked" data-editor-repeat-row><span class="drag-handle">⠿</span><input class="input-field" name="profile[custom_sections][{{ $loop->index }}][title]" value="{{ $section['title'] ?? '' }}" placeholder="Section title"><textarea class="textarea-field" name="profile[custom_sections][{{ $loop->index }}][body]" placeholder="Public section content">{{ $section['body'] ?? '' }}</textarea><button class="icon-btn del" type="button" data-editor-repeat-remove>×</button></div>
                        @endforeach
                    </div>
                    <button class="btn-add" type="button" data-editor-repeat-add="section">+ Add Custom Section</button>
                </div>
            </div>
        </div>

        <div class="editor-page active" id="epage-about">
            <div class="editor-media-grid">
                @foreach ([['logo','Company Logo',$profile?->logo_path],['cover_image','Background Cover',$profile?->cover_image_path]] as [$key,$label,$path])
                    <div class="card"><div class="card-title">{{ $label }}</div><div class="card-sub">Replace or remove the current public profile image.</div><label class="upload-zone" data-editor-upload-zone>@if($path)<img class="editor-upload-image" src="{{ '/storage/'.ltrim($path,'/') }}" alt="{{ $label }}" data-editor-upload-preview>@else<span data-editor-upload-preview>Choose image</span>@endif<input type="file" name="profile[{{ $key }}]" accept="image/jpeg,image/png,image/webp" hidden data-editor-upload-input></label><input type="hidden" name="remove_{{ $key }}" value="0" data-editor-remove-input><button class="btn-learn" type="button" data-editor-media-remove>Remove</button></div>
                @endforeach
            </div>
            <div class="card">
                <div class="card-title">Official Company Details</div><div class="card-sub">These details reassure customers that the verification page belongs to the actual manufacturer.</div>
                <label class="form-label">Company Name</label><input class="input-field" name="company[name]" value="{{ old('company.name', $company?->name) }}" data-preview-company>
                <div class="form-row-2"><div><label class="form-label">Registration Number</label><input class="input-field" name="company[registration_number]" value="{{ old('company.registration_number', $company?->registration_number) }}"></div><div><label class="form-label">Industry</label><input class="input-field" name="company[industry]" value="{{ old('company.industry', $company?->industry) }}"></div></div>
                <div class="form-row-2"><div><label class="form-label">Country</label><input class="input-field" name="company[country]" value="{{ old('company.country', $company?->country) }}"></div><div><label class="form-label">Region</label><input class="input-field" name="company[region]" value="{{ old('company.region', $company?->region) }}"></div></div>
                <label class="form-label">City</label><input class="input-field" name="company[city]" value="{{ old('company.city', $company?->city) }}">
                <label class="form-label">Company Description</label><textarea class="textarea-field" name="profile[company_description]">{{ old('profile.company_description', $profile?->company_description) }}</textarea>
            </div>
            <div class="card">
                <div class="card-title">Manufacturing and Certification</div><div class="card-sub">Show extra proof of legitimacy on public scan pages.</div>
                <label class="form-label">Manufacturer Name</label><input class="input-field" name="profile[manufacturer_name]" value="{{ old('profile.manufacturer_name', $profile?->manufacturer_name) }}">
                <label class="form-label">Factory / Production Address</label><textarea class="textarea-field" name="profile[factory_address]">{{ old('profile.factory_address', $profile?->factory_address) }}</textarea>
                <label class="form-label">Certification / License Number</label><input class="input-field" name="profile[certification_number]" value="{{ old('profile.certification_number', $profile?->certification_number) }}">
                <label class="form-label">Certificate File</label><input class="input-field" type="file" name="profile[certificate]" accept=".pdf,image/jpeg,image/png,image/webp"><input type="hidden" name="remove_certificate" value="0" data-certificate-remove>
                @if($profile?->certificate_path)<div class="editor-file-row"><span>{{ basename($profile->certificate_path) }}</span><button class="btn-learn" type="button" data-editor-certificate-remove>Remove file</button></div>@endif
            </div>
        </div>

        @if (false)
        <div class="editor-page" id="epage-appearance">
            <div class="card"><div class="card-title">Verification Page Layout</div><div class="card-sub">Choose how company, product, and batch information are arranged after a scan.</div><div class="layout-options">@foreach(['default'=>'Official Badge','product-first'=>'Product First','full-cover'=>'Full Cover'] as $value=>$label)<label class="layout-opt {{ $profileDefaults['layout_style'] === $value ? 'selected' : '' }}"><input type="radio" name="profile[layout_style]" value="{{ $value }}" @checked($profileDefaults['layout_style'] === $value) hidden><div class="layout-preview"><div class="lp-circle"></div><div class="lp-line" style="width:60%"></div><div class="lp-line" style="width:40%"></div></div><div class="layout-opt-label">{{ $label }}</div></label>@endforeach</div></div>
            <div class="card"><div class="card-title">Brand Colors and Text</div><div class="card-sub">Match the public page to the official brand.</div><label class="form-label">Font</label><select class="font-select" name="profile[font_family]">@foreach(['DM Sans','Inter','Google Sans'] as $font)<option @selected($profileDefaults['font_family'] === $font)>{{ $font }}</option>@endforeach</select><div class="editor-color-grid">@foreach(['primary_color'=>'Primary','secondary_color'=>'Secondary','text_color'=>'Text','background_color'=>'Background','button_color'=>'Button','button_text_color'=>'Button Text','success_color'=>'Success','warning_color'=>'Warning','error_color'=>'Error'] as $key=>$label)<label class="editor-color"><span>{{ $label }}</span><input type="color" name="profile[{{ $key }}]" value="{{ $profileDefaults[$key] }}" data-editor-color="{{ $key }}"><b>{{ $profileDefaults[$key] }}</b></label>@endforeach</div></div>
            <div class="card"><div class="card-title">Buttons and Status Badges</div><div class="card-sub">Choose a restrained visual treatment for support actions and trust states.</div><div class="buttons-grid">@foreach(['solid'=>'Solid','outline'=>'Outline','soft'=>'Soft'] as $value=>$label)<label class="btn-style-opt {{ $profileDefaults['button_style'] === $value ? 'selected' : '' }}"><input type="radio" name="profile[button_style]" value="{{ $value }}" @checked($profileDefaults['button_style'] === $value) hidden>{{ $label }}</label>@endforeach</div><label class="form-label">Badge Style</label><select class="font-select" name="profile[badge_style]">@foreach(['official'=>'Official','minimal'=>'Minimal','bold'=>'Bold'] as $value=>$label)<option value="{{ $value }}" @selected($profileDefaults['badge_style'] === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="card"><div class="flex-between"><div><div class="card-title">Platform Branding</div><div class="card-sub" style="margin-bottom:0">Show the Active8 verification mark on the public page.</div></div><input type="hidden" name="profile[show_platform_branding]" value="0"><label class="editor-switch"><input type="checkbox" name="profile[show_platform_branding]" value="1" @checked($profile?->show_platform_branding ?? true)><span></span></label></div></div>
        </div>
        @endif
        <input type="hidden" name="profile[layout_style]" value="{{ $profileDefaults['layout_style'] }}">
        <input type="hidden" name="profile[primary_color]" value="{{ $profileDefaults['primary_color'] }}">
        <input type="hidden" name="profile[secondary_color]" value="{{ $profileDefaults['secondary_color'] }}">
        <input type="hidden" name="profile[text_color]" value="{{ $profileDefaults['text_color'] }}">
        <input type="hidden" name="profile[background_color]" value="{{ $profileDefaults['background_color'] }}">
        <input type="hidden" name="profile[button_color]" value="{{ $profileDefaults['button_color'] }}">
        <input type="hidden" name="profile[button_text_color]" value="{{ $profileDefaults['button_text_color'] }}">
        <input type="hidden" name="profile[success_color]" value="{{ $profileDefaults['success_color'] }}">
        <input type="hidden" name="profile[warning_color]" value="{{ $profileDefaults['warning_color'] }}">
        <input type="hidden" name="profile[error_color]" value="{{ $profileDefaults['error_color'] }}">
        <input type="hidden" name="profile[font_family]" value="{{ $profileDefaults['font_family'] }}">
        <input type="hidden" name="profile[badge_style]" value="{{ $profileDefaults['badge_style'] }}">
        <input type="hidden" name="profile[button_style]" value="{{ $profileDefaults['button_style'] }}">
        <input type="hidden" name="profile[show_platform_branding]" value="{{ ($profile?->show_platform_branding ?? true) ? 1 : 0 }}">
    </div>

    <div class="preview-panel">
        <div>
            <div class="preview-device"><button class="active" type="button" data-preview-device="phone">Phone</button><button type="button" data-preview-device="desktop">Desktop</button></div>
            <div class="preview-modes">@foreach(['genuine','suspicious','invalid','expired','recalled','revoked'] as $mode)<button class="{{ $loop->first ? 'active' : '' }}" type="button" data-preview-mode="{{ $mode }}">{{ ucfirst($mode) }}</button>@endforeach</div>
            <div class="phone-shell">
                <div class="phone-screen" data-phone-screen>
                    <div class="phone-public-page">
                        <header class="phone-public-banner">
                            <div class="phone-public-cover" data-preview-cover @if($profile?->cover_image_path) style="background-image:url('{{ '/storage/'.ltrim($profile->cover_image_path,'/') }}')" @endif></div>
                            @if($previewProduct?->video_url)<span class="phone-public-video" data-preview-promotional-media><svg width="10" height="10" viewBox="0 0 24 24" aria-hidden="true"><path d="m8 5 11 7-11 7Z" fill="currentColor"/></svg></span>@endif
                            <div class="phone-public-hero">
                                <div class="phone-avatar">@if($previewProductImage)<img src="{{ $previewProductImage }}" alt="">@else{{ $previewProductInitials }}@endif</div>
                                <div>
                                    <div class="phone-public-kicker">Verified Product Profile</div>
                                    <div class="phone-name">{{ $previewProduct?->name ?? 'First registered product' }}</div>
                                    <div class="phone-public-company" data-preview-company-output>{{ $company?->name }}</div>
                                </div>
                            </div>
                        </header>
                        <div class="phone-public-body">
                            <div class="phone-verification" data-preview-verification data-tone="success">
                                <div class="phone-public-badge"><span class="phone-public-dot"></span><span data-preview-badge>Verified Genuine</span></div>
                                <h4 data-preview-title>Authentic product verified</h4>
                                <p data-preview-message-output>{{ Str::limit($profile?->genuine_message ?? 'This product is verified as part of an approved production batch.', 120) }}</p>
                            </div>
                            <div class="phone-public-meta"><span>Batch <strong>{{ $previewBatch?->batch_number ?? 'Batch preview' }}</strong></span>@if($previewBatch?->expiry_date)<span>Expires <strong>{{ $previewBatch->expiry_date->format('M j, Y') }}</strong></span>@endif</div>
                            <div class="phone-public-help phone-public-hidden" data-preview-help>
                                <strong>Need help with this product?</strong>
                                <p class="phone-public-copy" data-preview-help-copy></p>
                                <div class="phone-public-actions"><span class="phone-public-action">WhatsApp Support</span><span class="phone-public-action">Call Support</span></div>
                            </div>
                            <div data-preview-profile-content>
                                <div class="phone-public-nav">
                                    <span>Warnings</span>
                                    <span data-preview-visibility="genuine,expired">FAQs</span>
                                    <span>Gallery</span>
                                    <span data-preview-visibility="genuine">Video</span>
                                    <span>About</span>
                                </div>
                                <section class="phone-public-section">
                                    <h5>Suitability & Warnings</h5>
                                    <div class="phone-public-guidance">Read important guidance before using this product.</div>
                                </section>
                                <section class="phone-public-section" data-preview-visibility="genuine,expired">
                                    <h5>FAQs</h5>
                                    <p class="phone-public-copy">Common product questions and official answers.</p>
                                </section>
                                @if($previewProduct?->video_url)
                                    <section class="phone-public-section" data-preview-visibility="genuine">
                                        <h5>Product Video</h5>
                                        <p class="phone-public-copy">Official guide or product story.</p>
                                    </section>
                                @endif
                                <section class="phone-public-section">
                                    <h5>About Product</h5>
                                    <p class="phone-public-copy">{{ Str::limit($previewProduct?->description ?? 'Official product and batch information.', 78) }}</p>
                                </section>
                                <section class="phone-public-section">
                                    <h5>Company</h5>
                                    <div class="phone-public-company-card">
                                        <div class="phone-public-company-logo" data-preview-logo data-preview-company-initials="{{ $previewCompanyInitials }}">@if($previewCompanyLogo)<img src="{{ $previewCompanyLogo }}" alt="">@else{{ $previewCompanyInitials }}@endif</div>
                                        <div><strong data-preview-company-output>{{ $company?->name }}</strong><p class="phone-public-copy">Official manufacturer profile and support.</p></div>
                                    </div>
                                </section>
                            </div>
                            <div class="phone-public-footer" data-preview-footer>Secured by <img src="{{ asset('images/active8-logo.png') }}" alt="Active8"> batch verification</div>
                        </div>
                        <span class="phone-public-whatsapp"><svg width="13" height="13" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11.5a8 8 0 0 1-11.9 7L4 20l1.5-4A8 8 0 1 1 20 11.5Z"/><path d="M9 8c.4 3 2 4.6 5 5"/></svg></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<template data-editor-repeat-template="social"><div class="repeat-row" data-editor-repeat-row><span class="drag-handle">⠿</span><select class="font-select" name="profile[social_links][__INDEX__][label]">@foreach(['Instagram','Facebook','X','LinkedIn','YouTube','TikTok','WhatsApp'] as $network)<option>{{ $network }}</option>@endforeach</select><input class="input-field" type="url" name="profile[social_links][__INDEX__][url]" placeholder="https://"><button class="icon-btn del" type="button" data-editor-repeat-remove>×</button></div></template>
<template data-editor-repeat-template="section"><div class="repeat-row stacked" data-editor-repeat-row><span class="drag-handle">⠿</span><input class="input-field" name="profile[custom_sections][__INDEX__][title]" placeholder="Section title"><textarea class="textarea-field" name="profile[custom_sections][__INDEX__][body]" placeholder="Public section content"></textarea><button class="icon-btn del" type="button" data-editor-repeat-remove>×</button></div></template>
