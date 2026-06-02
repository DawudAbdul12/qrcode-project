@php
    $product = $product ?? null;
    $isEditing = (bool) $product;
    $imageUrl = $product?->image_path ? '/storage/'.ltrim($product->image_path, '/') : null;
    $galleryPaths = collect($product?->gallery_paths ?? []);
@endphp

<div class="company-wizard product-wizard" data-product-wizard>
    <div class="wizard-progress" aria-label="Product editor progress">
        @foreach (['Details', 'Origin', 'Guidance', 'FAQs'] as $index => $label)
            <button class="wizard-progress-step {{ $loop->first ? 'active' : '' }}" type="button" data-product-wizard-go="{{ $index }}">
                <span>{{ $index + 1 }}</span>
                <strong>{{ $label }}</strong>
            </button>
        @endforeach
    </div>

    <section class="wizard-step active" data-product-wizard-step="0">
        <div class="wizard-step-title">Product Details</div>
        <div class="wizard-step-copy">Start with the product identity customers and operations staff will recognize.</div>
        <label class="form-label">Company</label>
        <input type="hidden" name="company_id" value="{{ $product?->company_id }}" data-company-value>
        <div class="smart-select" data-product-company-select>
            <button class="smart-select-trigger" type="button" data-smart-toggle><span data-smart-label>{{ $product?->company?->name ?? 'Search company' }}</span></button>
            <div class="smart-select-menu">
                <input class="smart-select-search" type="search" placeholder="Search companies" data-smart-search>
                <div class="smart-select-options">
                    @foreach ($companies as $item)
                        <button class="smart-option {{ $product?->company_id === $item->id ? 'active' : '' }}" type="button" data-smart-option data-value="{{ $item->id }}" data-search="{{ Str::lower($item->name.' '.($item->industry ?? '').' '.($item->city ?? '').' '.($item->country ?? '')) }}">{{ $item->name }}</button>
                    @endforeach
                </div>
                <div class="smart-empty" data-smart-empty>No companies found.</div>
            </div>
        </div>
        <label class="form-label">Product Name</label>
        <input class="input-field" name="name" value="{{ $product?->name }}" placeholder="Shea Glow Body Cream" required data-product-review-name>
        <div class="form-row-2">
            <div><label class="form-label">SKU</label><input class="input-field" name="sku" value="{{ $product?->sku }}" placeholder="AK-SG-250"></div>
            <div>
                <label class="form-label">Category</label>
                <select class="font-select" name="product_category_id" data-product-category-select>
                    <option value="">Uncategorized</option>
                    @foreach ($categories->where('status', 'active')->sortBy('name') as $category)
                        <option value="{{ $category->id }}" data-company-id="{{ $category->company_id }}" @selected($product?->product_category_id === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <label class="form-label">Description</label>
        <textarea class="textarea-field" name="description" placeholder="Short public product description.">{{ $product?->description }}</textarea>
        <label class="form-label">Product Image</label>
        <label class="product-upload-zone" data-product-upload-zone>
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp" hidden data-product-upload-input>
            <span class="product-upload-preview" data-product-upload-preview>
                @if ($imageUrl)<img src="{{ $imageUrl }}" alt="{{ $product?->name }}">@else<svg width="24" height="24"><use href="#i-image"></use></svg>@endif
            </span>
            <span><strong>{{ $imageUrl ? 'Replace product image' : 'Upload product image' }}</strong><small>PNG, JPG, or WebP up to 10 MB</small></span>
        </label>
        <input type="hidden" name="remove_image" value="0" data-product-remove-image>
        @if ($imageUrl)<button class="btn-learn" type="button" data-product-image-remove>Remove image</button>@endif
        <div class="product-gallery-head">
            <div><label class="form-label">Product Gallery</label><div class="product-media-note">Add up to 4 packaging or product images.</div></div>
            <label class="suitability-add" for="product-gallery-{{ $product?->id ?? 'create' }}"><svg width="14" height="14"><use href="#i-plus"></use></svg>Add images</label>
        </div>
        <input id="product-gallery-{{ $product?->id ?? 'create' }}" type="file" name="gallery_images[]" accept="image/jpeg,image/png,image/webp" multiple hidden data-product-gallery-input>
        <div class="product-gallery-grid" data-product-gallery-list>
            @foreach ($galleryPaths as $galleryPath)
                <div class="product-gallery-item" data-product-gallery-existing>
                    <img src="{{ '/storage/'.ltrim($galleryPath, '/') }}" alt="">
                    <button type="button" data-product-gallery-remove aria-label="Remove gallery image">&times;</button>
                    <input type="hidden" name="remove_gallery_paths[]" value="{{ $galleryPath }}" disabled>
                </div>
            @endforeach
        </div>
        <label class="form-label" style="margin-top:16px">Product Video</label>
        <input class="input-field" type="url" name="video_url" value="{{ $product?->video_url }}" placeholder="https://www.youtube.com/watch?v=..." data-product-video-url>
        <div class="product-media-note" data-product-video-status>
            <svg width="14" height="14"><use href="#i-play"></use></svg>
            <span>{{ $product?->video_url ? 'Video link configured' : 'Optional: add a YouTube or Vimeo product demo, guide, or story.' }}</span>
        </div>
    </section>

    <section class="wizard-step" data-product-wizard-step="1">
        <div class="wizard-step-title">Origin & Status</div>
        <div class="wizard-step-copy">Capture manufacturing provenance and choose the current lifecycle state.</div>
        <label class="form-label">Manufacturer</label>
        <input class="input-field" name="manufacturer" value="{{ $product?->manufacturer }}" placeholder="Akoma Naturals Manufacturing Unit">
        <div class="form-row-2">
            <div><label class="form-label">Country of Origin</label><input class="input-field" name="country_of_origin" value="{{ $product?->country_of_origin }}" placeholder="Ghana"></div>
            <div>
                <label class="form-label">Status</label>
                <select class="font-select" name="status" required>
                    @foreach (['draft', 'active', 'discontinued', 'recalled'] as $status)
                        <option value="{{ $status }}" @selected(($product?->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <label class="form-label">Ingredients / Materials</label>
        <textarea class="textarea-field" name="ingredients" placeholder="List ingredients, materials, active components, or notable composition details.">{{ $product?->ingredients }}</textarea>
        <label class="form-label">How to Use</label>
        <textarea class="textarea-field" name="usage_instructions" placeholder="Explain usage, dosage, setup, care, or storage instructions.">{{ $product?->usage_instructions }}</textarea>
        <div class="wizard-review-list">
            <div><span>Product identity</span><strong>Captured</strong></div>
            <div><span>Image</span><strong data-product-image-status>{{ $imageUrl ? 'Uploaded' : 'Optional' }}</strong></div>
            <div><span>Video</span><strong data-product-review-video>{{ $product?->video_url ? 'Configured' : 'Optional' }}</strong></div>
            <div><span>Gallery</span><strong data-product-gallery-status>{{ $galleryPaths->count() ? $galleryPaths->count().' images' : 'Optional' }}</strong></div>
            <div><span>Lifecycle status</span><strong>Ready</strong></div>
        </div>
    </section>

    <section class="wizard-step" data-product-wizard-step="2">
        <div class="wizard-step-title">Suitability & Warnings</div>
        <div class="wizard-step-copy">Add concise customer guidance such as age limits, allergens, pregnancy notes, and usage warnings.</div>
        @include('partials.product-suitability-editor', ['suitabilityItems' => $product?->suitabilityItems ?? collect()])
    </section>

    <section class="wizard-step" data-product-wizard-step="3">
        <div class="wizard-step-title">Product FAQs</div>
        <div class="wizard-step-copy">Finish with answers to common product-specific questions. FAQs are optional and can be refined later.</div>
        @include('partials.product-faq-editor', ['faqItems' => $product?->faq_items ?? []])
    </section>

    <div class="wizard-actions">
        <button class="btn-learn" type="button" data-product-wizard-back>Back</button>
        <button class="btn-share" type="button" data-product-wizard-next>Continue</button>
        <button class="btn-share wizard-submit" type="submit" data-product-wizard-submit>{{ $isEditing ? 'Save Product' : 'Create Product' }}</button>
    </div>
</div>
