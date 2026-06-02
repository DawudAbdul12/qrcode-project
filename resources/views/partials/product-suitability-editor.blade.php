@php
    $suitabilityItems = $suitabilityItems ?? collect();
    $suitabilityTypes = [
        'age' => 'Age guidance',
        'audience' => 'Audience',
        'allergen' => 'Allergen',
        'dietary' => 'Dietary',
        'pregnancy' => 'Pregnancy',
        'medical' => 'Medical',
        'usage' => 'Usage',
        'safety' => 'Safety',
        'storage' => 'Storage',
        'custom' => 'Custom',
    ];
@endphp

<section class="suitability-editor" data-suitability-editor>
    <div class="suitability-editor-head">
        <div>
            <div class="suitability-editor-title">Suitability & Warnings</div>
            <div class="suitability-editor-copy">Add concise guidance customers should see after scanning the product QR code.</div>
        </div>
        <button class="suitability-add" type="button" data-suitability-add>
            <svg width="14" height="14"><use href="#i-plus"></use></svg>
            Add
        </button>
    </div>

    <div class="suitability-empty" data-suitability-empty>
        <div class="suitability-empty-icon">+</div>
        <div>
            <strong>No suitability guidance yet</strong>
            <span>Add age guidance, audience notes, allergens, or product-specific warnings.</span>
        </div>
    </div>

    <div class="suitability-list" data-suitability-list>
        @foreach ($suitabilityItems as $index => $item)
            <article class="suitability-item" data-suitability-item>
                <div class="suitability-item-head">
                    <span class="suitability-item-number" data-suitability-number>{{ $index + 1 }}</span>
                    <strong data-suitability-summary>{{ $item->short_label }}</strong>
                    <div class="suitability-item-actions">
                        <button class="icon-btn" type="button" data-suitability-up aria-label="Move guidance up">↑</button>
                        <button class="icon-btn" type="button" data-suitability-down aria-label="Move guidance down">↓</button>
                        <button class="icon-btn del" type="button" data-suitability-remove aria-label="Remove guidance">
                            <svg width="14" height="14"><use href="#i-trash"></use></svg>
                        </button>
                    </div>
                </div>
                <div class="suitability-fields">
                    <label><span>Type</span><select class="font-select" name="suitability_items[{{ $index }}][type]" required>@foreach ($suitabilityTypes as $value => $label)<option value="{{ $value }}" @selected($item->type === $value)>{{ $label }}</option>@endforeach</select></label>
                    <label><span>Short label</span><input class="input-field" name="suitability_items[{{ $index }}][short_label]" value="{{ $item->short_label }}" placeholder="18+" maxlength="80" required data-suitability-label></label>
                    <label class="wide"><span>Details</span><textarea class="textarea-field" name="suitability_items[{{ $index }}][detail]" placeholder="Not recommended for children under 18." maxlength="500">{{ $item->detail }}</textarea></label>
                    <label><span>Severity</span><select class="font-select" name="suitability_items[{{ $index }}][severity]" required><option value="info" @selected($item->severity === 'info')>Info</option><option value="caution" @selected($item->severity === 'caution')>Caution</option><option value="warning" @selected($item->severity === 'warning')>Warning</option></select></label>
                    <label><span>Visibility</span><select class="font-select" name="suitability_items[{{ $index }}][visibility]" required><option value="public" @selected($item->is_public)>Public</option><option value="internal" @selected(! $item->is_public)>Internal only</option></select></label>
                </div>
            </article>
        @endforeach
    </div>

    <template data-suitability-template>
        <article class="suitability-item" data-suitability-item>
            <div class="suitability-item-head">
                <span class="suitability-item-number" data-suitability-number></span>
                <strong data-suitability-summary>New guidance</strong>
                <div class="suitability-item-actions">
                    <button class="icon-btn" type="button" data-suitability-up aria-label="Move guidance up">↑</button>
                    <button class="icon-btn" type="button" data-suitability-down aria-label="Move guidance down">↓</button>
                    <button class="icon-btn del" type="button" data-suitability-remove aria-label="Remove guidance"><svg width="14" height="14"><use href="#i-trash"></use></svg></button>
                </div>
            </div>
            <div class="suitability-fields">
                <label><span>Type</span><select class="font-select" name="suitability_items[__INDEX__][type]" required>@foreach ($suitabilityTypes as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>
                <label><span>Short label</span><input class="input-field" name="suitability_items[__INDEX__][short_label]" placeholder="18+" maxlength="80" required data-suitability-label></label>
                <label class="wide"><span>Details</span><textarea class="textarea-field" name="suitability_items[__INDEX__][detail]" placeholder="Not recommended for children under 18." maxlength="500"></textarea></label>
                <label><span>Severity</span><select class="font-select" name="suitability_items[__INDEX__][severity]" required><option value="info">Info</option><option value="caution">Caution</option><option value="warning">Warning</option></select></label>
                <label><span>Visibility</span><select class="font-select" name="suitability_items[__INDEX__][visibility]" required><option value="public">Public</option><option value="internal">Internal only</option></select></label>
            </div>
        </article>
    </template>
</section>
