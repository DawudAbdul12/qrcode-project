<section class="screen {{ $activePage === 'company-profile' ? 'active' : '' }}" id="screen-company-profile">
            @include('partials.company-profile-editor')
            @if (false)
            <div class="topbar">
                <div>
                    <div class="page-title" id="editor-page-title">Content</div>
                    <div class="page-subtitle" id="editor-page-subtitle">Control the public trust content customers see after scanning a batch QR code.</div>
                </div>
                <div class="topbar-actions">
                    <label class="editor-company-context">
                        <span class="company-sub-name">Editing</span>
                        <select class="font-select" onchange="window.location.href=this.value" aria-label="Choose company profile">
                            @foreach ($companies as $item)
                                <option value="{{ route('active8.page', ['page' => 'company-profile', 'company' => $item->id]) }}" @selected($company?->id === $item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <a class="btn-learn" href="{{ route('active8.page', 'companies') }}">
                        Companies
                    </a>
                    <a class="btn-share" href="{{ route('active8.page', 'dashboard') }}">
                        <svg width="14" height="14"><use href="#i-qr"></use></svg>
                        Preview Dashboard
                    </a>
                </div>
            </div>

            <div class="tabs">
                @foreach ($editorTabs as $tab)
                    <button class="tab {{ $loop->first ? 'active' : '' }}" type="button" data-editor-tab="{{ $tab['key'] }}">
                        <svg width="13" height="13"><use href="#i-{{ $tab['icon'] }}"></use></svg>
                        {{ $tab['label'] }}
                    </button>
                @endforeach
            </div>

            <div class="editor-layout">
                <div class="editor-scroll">
                    <div class="editor-page active" id="epage-content">
                        <div class="section-block">
                            <div class="section-header">
                                <span class="drag-handle">⠿</span>
                                <span class="section-header-title">Verification Messages</span>
                                <span class="status-pill active">Public</span>
                            </div>
                            <div class="section-body">
                                <div class="section-label">Verification Headline</div>
                                <input class="input-field" value="{{ $companyProfile?->verification_headline ?? 'Verify your original product' }}">
                                <div class="section-label">Genuine Product Message</div>
                                <textarea class="textarea-field">{{ $companyProfile?->genuine_message ?? 'This product is verified as part of an approved production batch.' }}</textarea>
                                <div class="section-label">Suspicious Scan Message</div>
                                <textarea class="textarea-field">{{ $companyProfile?->suspicious_message ?? 'This QR code has unusual scan activity. Please contact support before using this product.' }}</textarea>
                                <div class="section-label">Invalid QR Message</div>
                                <textarea class="textarea-field">{{ $companyProfile?->invalid_message ?? 'This code is not recognized by our verification system.' }}</textarea>
                            </div>
                        </div>

                        <div class="section-block">
                            <div class="section-header">
                                <span class="drag-handle">⠿</span>
                                <span class="section-header-title">Support and Links</span>
                                <span class="text-red">✕</span>
                            </div>
                            <div class="section-body">
                                <div class="section-label">Support Text</div>
                                <textarea class="textarea-field">{{ $companyProfile?->support_text ?? 'Need help confirming a product? Contact our support team with the batch number and photos of the label.' }}</textarea>
                                <div class="form-row-2">
                                    <div>
                                        <div class="section-label">Support Email</div>
                                        <input class="input-field" value="{{ $companyProfile?->support_email ?? $company?->email }}">
                                    </div>
                                    <div>
                                        <div class="section-label">Support Phone</div>
                                        <input class="input-field" value="{{ $companyProfile?->support_phone ?? $company?->phone }}">
                                    </div>
                                </div>
                                <div class="section-label">Website URL</div>
                                <input class="input-field" value="{{ $companyProfile?->website_url ?? $company?->website }}">

                                <div class="section-label">Social Links</div>
                                @foreach (($companyProfile?->social_links ?? [['label' => 'Instagram', 'url' => 'https://instagram.com/company']]) as $link)
                                    <div class="item-row">
                                        <span class="drag-handle">⠿</span>
                                        <div class="item-icon email"><svg width="14" height="14"><use href="#i-globe"></use></svg></div>
                                        <div class="item-info"><div class="item-name">{{ $link['label'] ?? 'Social' }}</div><div class="item-url">{{ $link['url'] ?? '' }}</div></div>
                                        <div class="item-actions"><button class="icon-btn" type="button">✎</button><button class="icon-btn del" type="button">✕</button></div>
                                    </div>
                                @endforeach
                                <button class="btn-add" type="button">＋ Add Social Link</button>
                            </div>
                        </div>

                        <div class="section-block">
                            <div class="section-header">
                                <span class="drag-handle">⠿</span>
                                <span class="section-header-title">Custom Public Sections</span>
                                <span class="text-red">✕</span>
                            </div>
                            <div class="section-body">
                                @foreach (($companyProfile?->custom_sections ?? []) as $section)
                                    <div class="item-row">
                                        <span class="drag-handle">⠿</span>
                                        <div class="item-icon phone"><svg width="14" height="14"><use href="#i-edit"></use></svg></div>
                                        <div class="item-info"><div class="item-name">{{ $section['title'] ?? 'Section' }}</div><div class="item-url">{{ $section['body'] ?? '' }}</div></div>
                                        <div class="item-actions"><button class="icon-btn" type="button">✎</button><button class="icon-btn del" type="button">✕</button></div>
                                    </div>
                                @endforeach
                                <button class="btn-add" type="button">＋ Add Custom Section</button>
                            </div>
                        </div>
                    </div>

                    <div class="editor-page" id="epage-about">
                        <div class="card">
                            <div class="card-title">Company Logo</div>
                            <div class="card-sub">This appears as the profile picture on the customer verification page.</div>
                            <div class="upload-zone">
                                @if ($companyProfile?->logo_path)
                                    <img class="editor-upload-image" src="{{ '/storage/'.ltrim($companyProfile->logo_path, '/') }}" alt="{{ $company?->name }} logo">
                                @else
                                    <span style="font-size:13px;color:var(--gray-400)">Upload company logo</span><svg width="28" height="28" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                                @endif
                            </div>
                            <div class="upload-note">Upload an image with a maximum size of 10 MB</div>
                        </div>
                        <div class="card">
                            <div class="card-title">Background Cover</div>
                            <div class="card-sub">Shown behind the logo on the branded verification page. Recommended aspect ratio is 2560x1080px.</div>
                            <div class="upload-zone" style="flex-direction:column;gap:6px;height:90px;">
                                @if ($companyProfile?->cover_image_path)
                                    <img class="editor-upload-image" src="{{ '/storage/'.ltrim($companyProfile->cover_image_path, '/') }}" alt="{{ $company?->name }} cover">
                                @else
                                    <svg width="28" height="28" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                                    <span style="font-size:13px;color:var(--gray-400)">Upload background cover</span>
                                @endif
                            </div>
                            <div class="upload-note">Upload an image with a maximum size of 10 MB</div>
                        </div>
                        <div class="card">
                            <div class="card-title">Official Company Details</div>
                            <div class="card-sub">These details reassure customers that the verification page belongs to the actual manufacturer.</div>
                            <label class="form-label">Company Name</label><input class="input-field" value="{{ $company?->name }}">
                            <div class="form-row-2">
                                <div><label class="form-label">Registration Number</label><input class="input-field" value="{{ $company?->registration_number }}"></div>
                                <div><label class="form-label">Industry</label><input class="input-field" value="{{ $company?->industry }}"></div>
                            </div>
                            <div class="form-row-2">
                                <div><label class="form-label">Country</label><input class="input-field" value="{{ $company?->country }}"></div>
                                <div><label class="form-label">City / Region</label><input class="input-field" value="{{ collect([$company?->city, $company?->region])->filter()->implode(', ') }}"></div>
                            </div>
                            <label class="form-label">Company Description</label><textarea class="textarea-field">{{ $companyProfile?->company_description }}</textarea>
                            <div class="btn-save"><button type="button">Save company details</button></div>
                        </div>
                        <div class="card">
                            <div class="card-title">Manufacturing and Certification</div>
                            <div class="card-sub">Used on the scan page when you want to show extra proof of legitimacy.</div>
                            <label class="form-label">Manufacturer Name</label><input class="input-field" value="{{ $companyProfile?->manufacturer_name }}">
                            <label class="form-label">Factory / Production Address</label><textarea class="textarea-field">{{ $companyProfile?->factory_address }}</textarea>
                            <label class="form-label">Certification / License Number</label><input class="input-field" value="{{ $companyProfile?->certification_number }}">
                            <div class="btn-save"><button type="button">Save changes</button></div>
                        </div>
                    </div>

                    <div class="editor-page" id="epage-appearance">
                        <div class="card">
                            <div class="card-title">Verification Page Layout</div>
                            <div class="card-sub">Choose how company, product, and batch information is arranged after a scan.</div>
                            <div class="layout-options">
                                <div class="layout-opt selected"><div class="layout-preview"><div class="lp-circle"></div><div class="lp-line" style="width:60%"></div><div class="lp-line" style="width:40%"></div></div><div class="layout-opt-label">Official Badge</div></div>
                                <div class="layout-opt"><div class="layout-preview" style="flex-direction:row;gap:8px;padding:8px;"><div class="lp-circle"></div><div style="flex:1;display:flex;flex-direction:column;gap:5px"><div class="lp-line" style="width:80%"></div><div class="lp-line" style="width:55%"></div></div></div><div class="layout-opt-label">Product First</div></div>
                                <div class="layout-opt"><div class="layout-preview" style="background:var(--gray-200);"><div class="lp-circle" style="background:var(--gray-400)"></div><div class="lp-line" style="width:60%;background:var(--gray-400)"></div></div><div class="layout-opt-label">Full Cover</div></div>
                            </div>
                            <div class="btn-save"><button type="button">Save changes</button></div>
                        </div>
                        <div class="card">
                            <div class="card-title">Brand Colors and Text</div>
                            <div class="card-sub">Match the verification page to the client company's official brand.</div>
                            <label class="form-label">Font</label>
                            <select class="font-select"><option>{{ $companyProfile?->font_family ?? 'DM Sans' }}</option><option>Inter</option><option>Google Sans</option></select>
                            <label class="form-label">Primary Color</label>
                            <div class="color-row"><div class="color-swatch" style="background:{{ $companyProfile?->primary_color ?? '#2563EB' }};border-color:{{ $companyProfile?->primary_color ?? '#2563EB' }};"></div><span class="color-hex">{{ $companyProfile?->primary_color ?? '#2563EB' }}</span></div>
                            <label class="form-label">Secondary Color</label>
                            <div class="color-row"><div class="color-swatch" style="background:{{ $companyProfile?->secondary_color ?? '#111827' }};"></div><span class="color-hex">{{ $companyProfile?->secondary_color ?? '#111827' }}</span></div>
                            <label class="form-label">Text Color</label>
                            <div class="color-row"><div class="color-swatch" style="background:{{ $companyProfile?->text_color ?? '#111827' }};border-color:{{ $companyProfile?->text_color ?? '#111827' }};"></div><span class="color-hex">{{ $companyProfile?->text_color ?? '#111827' }}</span></div>
                            <label class="form-label">Background Color</label>
                            <div class="color-row"><div class="color-swatch" style="background:{{ $companyProfile?->background_color ?? '#FFFFFF' }};"></div><span class="color-hex">{{ $companyProfile?->background_color ?? '#FFFFFF' }}</span></div>
                        </div>
                        <div class="card">
                            <div class="card-title">Buttons and Status Badges</div>
                            <div class="card-sub">Define the look of support buttons and genuine/warning/error indicators.</div>
                            <div class="buttons-grid">
                                @foreach (range(1, 6) as $button)
                                    <div class="btn-style-opt {{ $loop->first ? 'selected' : '' }}">Button</div>
                                @endforeach
                            </div>
                            <label class="form-label">Button Color</label>
                            <div class="color-row"><div class="color-swatch" style="background:{{ $companyProfile?->button_color ?? '#2563EB' }};border-color:{{ $companyProfile?->button_color ?? '#2563EB' }};"></div><span class="color-hex">{{ $companyProfile?->button_color ?? '#2563EB' }}</span></div>
                            <label class="form-label">Button Text Color</label>
                            <div class="color-row"><div class="color-swatch" style="background:{{ $companyProfile?->button_text_color ?? '#FFFFFF' }};"></div><span class="color-hex">{{ $companyProfile?->button_text_color ?? '#FFFFFF' }}</span></div>
                            <div class="form-row-2">
                                <div><label class="form-label">Success Color</label><div class="color-row"><div class="color-swatch" style="background:{{ $companyProfile?->success_color ?? '#22C55E' }};"></div><span class="color-hex">{{ $companyProfile?->success_color ?? '#22C55E' }}</span></div></div>
                                <div><label class="form-label">Warning Color</label><div class="color-row"><div class="color-swatch" style="background:{{ $companyProfile?->warning_color ?? '#F59E0B' }};"></div><span class="color-hex">{{ $companyProfile?->warning_color ?? '#F59E0B' }}</span></div></div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="flex-between">
                                <div><div class="card-title">Platform Branding</div><div class="card-sub" style="margin-bottom:0">Show or hide the verification platform mark on the public scan page.</div></div>
                                <button class="toggle {{ $companyProfile?->show_platform_branding ? 'on' : '' }}" type="button" onclick="this.classList.toggle('on')" aria-label="Toggle branding"></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="preview-panel">
                    <div class="phone-shell">
                        <div class="phone-screen">
                            <div class="phone-bg" @if ($companyProfile?->cover_image_path) style="background-image:url('{{ '/storage/'.ltrim($companyProfile->cover_image_path, '/') }}');background-size:cover;background-position:center;" @endif><div class="phone-avatar">@if ($companyProfile?->logo_path)<img src="{{ '/storage/'.ltrim($companyProfile->logo_path, '/') }}" alt="{{ $company?->name }} logo">@else{{ $company ? Str::of($company->name)->explode(' ')->filter()->map(fn ($word) => Str::substr($word, 0, 1))->take(2)->implode('') : 'CO' }}@endif</div></div>
                            <div class="phone-info">
                                <div class="phone-name">{{ $company?->name ?? 'Company Name' }}</div>
                                <div class="phone-job">Genuine Product</div>
                                <div class="phone-location">{{ $companyProfile?->verification_headline ?? 'Verify your original product' }}</div>
                            </div>
                            <div class="phone-exchange-btn">Verified Batch</div>
                            <div class="phone-section-label">PRODUCT</div>
                            <div style="margin:0 14px 10px;padding:10px;border:1px solid var(--gray-200);border-radius:8px;font-size:12px;color:var(--gray-700)">
                                Shea Glow Body Cream<br>
                                <span style="color:var(--gray-400)">Batch AK-SG-2026-001</span>
                            </div>
                            <div class="phone-section-label">MESSAGE</div>
                            <div style="margin:0 14px 10px;color:var(--gray-500);font-size:11px;line-height:1.4">{{ Str::limit($companyProfile?->genuine_message ?? 'This product is verified as part of an approved production batch.', 120) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </section>
