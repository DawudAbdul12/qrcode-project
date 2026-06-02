<div class="company-wizard" data-company-wizard>
    <input type="hidden" name="status" value="active" data-wizard-status>

    <div class="wizard-progress" aria-label="Company onboarding progress">
        @foreach (['Company', 'Content', 'About', 'Review'] as $index => $label)
            <button class="wizard-progress-step {{ $loop->first ? 'active' : '' }}" type="button" data-wizard-go="{{ $index }}">
                <span>{{ $index + 1 }}</span>
                <strong>{{ $label }}</strong>
            </button>
        @endforeach
    </div>

    <section class="wizard-step active" data-wizard-step="0">
        <div class="wizard-step-title">Company Details</div>
        <div class="wizard-step-copy">Start with the official identity and the person responsible for onboarding.</div>
        <label class="form-label">Company Name</label>
        <input class="input-field" name="name" placeholder="Akoma Naturals Ltd" required data-wizard-company-name>
        <div class="form-row-2">
            <div><label class="form-label">Registration Number</label><input class="input-field" name="registration_number" placeholder="GH-RG-2026-0192"></div>
            <div><label class="form-label">Industry</label><input class="input-field" name="industry" placeholder="Cosmetics and personal care"></div>
        </div>
        <label class="form-label">Contact Person</label>
        <input class="input-field" name="contact_person" placeholder="Ama Mensah">
        <div class="form-row-2">
            <div><label class="form-label">Email</label><input class="input-field" type="email" name="email" placeholder="support@example.com"></div>
            <div><label class="form-label">Phone</label><input class="input-field" name="phone" placeholder="+233..."></div>
        </div>
        <div class="form-row-2">
            <div><label class="form-label">Country</label><input class="input-field" name="country" placeholder="Ghana"></div>
            <div><label class="form-label">Region</label><input class="input-field" name="region" placeholder="Greater Accra"></div>
        </div>
        <div class="form-row-2">
            <div><label class="form-label">City</label><input class="input-field" name="city" placeholder="Accra"></div>
            <div><label class="form-label">Website</label><input class="input-field" type="url" name="website" placeholder="https://example.com"></div>
        </div>
        <label class="form-label">Internal Notes</label>
        <textarea class="textarea-field" name="notes" placeholder="Onboarding notes, approval context, and label requirements."></textarea>
    </section>

    <section class="wizard-step" data-wizard-step="1">
        <div class="wizard-step-title">Verification Content</div>
        <div class="wizard-step-copy">Write the trust messages customers see after scanning a verified batch QR code.</div>
        <label class="form-label">Verification Headline</label>
        <input class="input-field" name="profile[verification_headline]" value="Verify your original product" required>
        <label class="form-label">Genuine Product Message</label>
        <textarea class="textarea-field" name="profile[genuine_message]" placeholder="This product is verified as part of an approved production batch."></textarea>
        <label class="form-label">Suspicious Scan Message</label>
        <textarea class="textarea-field" name="profile[suspicious_message]" placeholder="This QR code has unusual scan activity. Contact support before using this product."></textarea>
        <label class="form-label">Invalid QR Message</label>
        <textarea class="textarea-field" name="profile[invalid_message]" placeholder="This code is not recognized by our verification system."></textarea>
        <label class="form-label">Support Text</label>
        <textarea class="textarea-field" name="profile[support_text]" placeholder="Need help confirming a product? Contact our support team."></textarea>
        <div class="form-row-2">
            <div><label class="form-label">Support Email</label><input class="input-field" type="email" name="profile[support_email]" placeholder="support@example.com"></div>
            <div><label class="form-label">Support Phone</label><input class="input-field" name="profile[support_phone]" placeholder="+233..."></div>
        </div>
        <label class="form-label">Public Website URL</label>
        <input class="input-field" type="url" name="profile[website_url]" placeholder="https://example.com">
        <div class="wizard-social-head">
            <div>
                <label class="form-label">Social Links</label>
                <div class="wizard-social-copy">Add the official channels customers can use to confirm the company identity.</div>
            </div>
            <button class="btn-add" type="button" data-wizard-social-add>+ Add Social Link</button>
        </div>
        <div class="wizard-social-list" data-wizard-social-list></div>
        <template data-wizard-social-template>
            <div class="wizard-social-row" data-wizard-social-row>
                <select class="font-select" name="profile[social_links][__INDEX__][label]" aria-label="Social platform" required>
                    <option value="Instagram">Instagram</option>
                    <option value="Facebook">Facebook</option>
                    <option value="X">X</option>
                    <option value="LinkedIn">LinkedIn</option>
                    <option value="YouTube">YouTube</option>
                    <option value="TikTok">TikTok</option>
                    <option value="WhatsApp">WhatsApp</option>
                </select>
                <input class="input-field" type="url" name="profile[social_links][__INDEX__][url]" placeholder="https://" aria-label="Social profile URL" required>
                <button class="icon-btn del" type="button" data-wizard-social-remove aria-label="Remove social link">&times;</button>
            </div>
        </template>
    </section>

    <section class="wizard-step" data-wizard-step="2">
        <div class="wizard-step-title">About & Media</div>
        <div class="wizard-step-copy">Mirror the profile editor with brand images, company details, and legitimacy signals customers can compare against the packaging.</div>
        <div class="wizard-upload-grid">
            <label class="wizard-upload-zone" data-wizard-upload>
                <input type="file" name="profile[logo]" accept="image/jpeg,image/png,image/webp" data-wizard-upload-input data-wizard-logo-input>
                <span class="wizard-upload-preview" data-wizard-upload-preview>
                    <svg width="24" height="24"><use href="#i-user"></use></svg>
                </span>
                <strong>Company Logo</strong>
                <small data-wizard-upload-label>PNG, JPG, or WebP up to 10 MB</small>
            </label>
            <label class="wizard-upload-zone cover" data-wizard-upload>
                <input type="file" name="profile[cover_image]" accept="image/jpeg,image/png,image/webp" data-wizard-upload-input data-wizard-cover-input>
                <span class="wizard-upload-preview" data-wizard-upload-preview>
                    <svg width="24" height="24"><use href="#i-globe"></use></svg>
                </span>
                <strong>Background Cover</strong>
                <small data-wizard-upload-label>Recommended ratio 2560 x 1080</small>
            </label>
        </div>
        <label class="form-label">Company Description</label>
        <textarea class="textarea-field" name="profile[company_description]" placeholder="A short public description of the company and its products."></textarea>
        <label class="form-label">Manufacturer Name</label>
        <input class="input-field" name="profile[manufacturer_name]" placeholder="Official manufacturing unit">
        <label class="form-label">Factory / Production Address</label>
        <textarea class="textarea-field" name="profile[factory_address]" placeholder="Factory address shown on verified product pages."></textarea>
        <label class="form-label">Certification / License Number</label>
        <input class="input-field" name="profile[certification_number]" placeholder="FDA-GH-COS-2026-421">
    </section>

    <input type="hidden" name="profile[layout_style]" value="default">
    <input type="hidden" name="profile[primary_color]" value="#2563EB">
    <input type="hidden" name="profile[secondary_color]" value="#111827">
    <input type="hidden" name="profile[button_color]" value="#2563EB">
    <input type="hidden" name="profile[font_family]" value="DM Sans">
    <input type="hidden" name="profile[badge_style]" value="official">
    <input type="hidden" name="profile[show_platform_branding]" value="1">

    <section class="wizard-step" data-wizard-step="3">
        <div class="wizard-step-title">Review & Create</div>
        <div class="wizard-step-copy">Confirm the onboarding record. You can continue refining the public profile in the editor after creation.</div>
        <div class="wizard-review-hero">
            <div class="wizard-review-cover" data-wizard-review-cover></div>
            <div class="company-logo-sm" data-wizard-review-initials>CO</div>
            <div><strong data-wizard-review-name>New company</strong><span>Verification profile ready to create</span></div>
        </div>
        <div class="wizard-review-list">
            <div><span>Company details</span><strong>Captured</strong></div>
            <div><span>Verification content</span><strong>Configured</strong></div>
            <div><span>Manufacturer details</span><strong>Ready</strong></div>
        </div>
    </section>

    <div class="wizard-actions">
        <button class="btn-learn" type="button" data-wizard-back>Back</button>
        <button class="wizard-draft" type="submit" data-wizard-draft>Save Draft</button>
        <button class="btn-share" type="button" data-wizard-next>Continue</button>
        <button class="btn-share wizard-submit" type="submit" data-wizard-submit>Create Company</button>
    </div>
</div>
