<label class="form-label">Company Name</label>
<input class="input-field" name="name" value="{{ old('name', $item?->name) }}" placeholder="Akoma Naturals Ltd" required>
<div class="form-row-2">
    <div><label class="form-label">Registration Number</label><input class="input-field" name="registration_number" value="{{ old('registration_number', $item?->registration_number) }}" placeholder="GH-RG-2026-0192"></div>
    <div><label class="form-label">Industry</label><input class="input-field" name="industry" value="{{ old('industry', $item?->industry) }}" placeholder="Cosmetics and personal care"></div>
</div>
<label class="form-label">Contact Person</label>
<input class="input-field" name="contact_person" value="{{ old('contact_person', $item?->contact_person) }}" placeholder="Ama Mensah">
<div class="form-row-2">
    <div><label class="form-label">Email</label><input class="input-field" type="email" name="email" value="{{ old('email', $item?->email) }}" placeholder="support@example.com"></div>
    <div><label class="form-label">Phone</label><input class="input-field" name="phone" value="{{ old('phone', $item?->phone) }}" placeholder="+233..."></div>
</div>
<div class="form-row-2">
    <div><label class="form-label">Country</label><input class="input-field" name="country" value="{{ old('country', $item?->country) }}" placeholder="Ghana"></div>
    <div><label class="form-label">Region</label><input class="input-field" name="region" value="{{ old('region', $item?->region) }}" placeholder="Greater Accra"></div>
</div>
<div class="form-row-2">
    <div><label class="form-label">City</label><input class="input-field" name="city" value="{{ old('city', $item?->city) }}" placeholder="Accra"></div>
    <div><label class="form-label">Status</label><select class="font-select" name="status" required>@foreach (['active', 'pending', 'suspended'] as $status)<option value="{{ $status }}" @selected(old('status', $item?->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
</div>
<label class="form-label">Website</label>
<input class="input-field" type="url" name="website" value="{{ old('website', $item?->website) }}" placeholder="https://example.com">
<label class="form-label">Notes</label>
<textarea class="textarea-field" name="notes" placeholder="Internal notes for onboarding, labels, and approvals.">{{ old('notes', $item?->notes) }}</textarea>
<button class="btn-add-content" type="submit">{{ $submitLabel }}</button>
