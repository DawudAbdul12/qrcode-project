@php
    $faqItems = collect($faqItems ?? []);
@endphp

<section class="suitability-editor" data-product-faq-editor>
    <div class="suitability-editor-head">
        <div>
            <div class="suitability-editor-title">Product FAQs</div>
            <div class="suitability-editor-copy">Answer product-specific questions customers may have after scanning the QR code.</div>
        </div>
        <button class="suitability-add" type="button" data-product-faq-add>
            <svg width="14" height="14"><use href="#i-plus"></use></svg>
            Add
        </button>
    </div>

    <div class="suitability-empty" data-product-faq-empty>
        <div class="suitability-empty-icon">?</div>
        <div>
            <strong>No product FAQs yet</strong>
            <span>Add answers for usage, ingredients, care, sizing, compatibility, or other common questions.</span>
        </div>
    </div>

    <div class="suitability-list" data-product-faq-list>
        @foreach ($faqItems as $index => $item)
            <article class="suitability-item" data-product-faq-item>
                <div class="suitability-item-head">
                    <span class="suitability-item-number" data-product-faq-number>{{ $index + 1 }}</span>
                    <strong data-product-faq-summary>{{ $item['question'] ?? 'Question' }}</strong>
                    <div class="suitability-item-actions">
                        <button class="icon-btn" type="button" data-product-faq-up aria-label="Move question up">↑</button>
                        <button class="icon-btn" type="button" data-product-faq-down aria-label="Move question down">↓</button>
                        <button class="icon-btn del" type="button" data-product-faq-remove aria-label="Remove question"><svg width="14" height="14"><use href="#i-trash"></use></svg></button>
                    </div>
                </div>
                <div class="suitability-fields">
                    <label class="wide"><span>Question</span><input class="input-field" name="faq_items[{{ $index }}][question]" value="{{ $item['question'] ?? '' }}" placeholder="How should I use this product?" maxlength="255" required data-product-faq-question></label>
                    <label class="wide"><span>Answer</span><textarea class="textarea-field" name="faq_items[{{ $index }}][answer]" placeholder="Provide a clear, concise answer." maxlength="2000" required>{{ $item['answer'] ?? '' }}</textarea></label>
                </div>
            </article>
        @endforeach
    </div>

    <template data-product-faq-template>
        <article class="suitability-item" data-product-faq-item>
            <div class="suitability-item-head">
                <span class="suitability-item-number" data-product-faq-number></span>
                <strong data-product-faq-summary>New question</strong>
                <div class="suitability-item-actions">
                    <button class="icon-btn" type="button" data-product-faq-up aria-label="Move question up">↑</button>
                    <button class="icon-btn" type="button" data-product-faq-down aria-label="Move question down">↓</button>
                    <button class="icon-btn del" type="button" data-product-faq-remove aria-label="Remove question"><svg width="14" height="14"><use href="#i-trash"></use></svg></button>
                </div>
            </div>
            <div class="suitability-fields">
                <label class="wide"><span>Question</span><input class="input-field" name="faq_items[__INDEX__][question]" placeholder="How should I use this product?" maxlength="255" required data-product-faq-question></label>
                <label class="wide"><span>Answer</span><textarea class="textarea-field" name="faq_items[__INDEX__][answer]" placeholder="Provide a clear, concise answer." maxlength="2000" required></textarea></label>
            </div>
        </article>
    </template>
</section>
