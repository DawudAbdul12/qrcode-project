<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProductController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $suitabilityItems = $data['suitability_items'] ?? [];
        unset($data['suitability_items'], $data['image'], $data['remove_image'], $data['gallery_images'], $data['remove_gallery_paths']);
        $data['category'] = $this->categoryName($data['product_category_id'] ?? null);
        $data['slug'] = $this->uniqueSlug($data['company_id'], $data['name']);
        $imagePath = null;
        $galleryPaths = [];

        try {
            $imagePath = $request->hasFile('image')
                ? $request->file('image')->store('products/'.$data['slug'], 'public')
                : null;
            $galleryPaths = $this->storeGalleryImages($request, $data['slug']);
            $data['gallery_paths'] = $galleryPaths;

            DB::transaction(function () use ($data, $suitabilityItems, $imagePath): void {
                $product = Product::query()->create(array_merge($data, ['image_path' => $imagePath]));
                $this->syncSuitabilityItems($product, $suitabilityItems);
            });
        } catch (Throwable $exception) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            Storage::disk('public')->delete($galleryPaths);
            throw $exception;
        }

        return redirect()
            ->route('active8.page', 'products')
            ->with('status', 'Product created successfully.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validatedData($request);
        $suitabilityItems = $data['suitability_items'] ?? [];
        unset($data['suitability_items'], $data['image'], $data['remove_image'], $data['gallery_images'], $data['remove_gallery_paths']);
        $data['category'] = $this->categoryName($data['product_category_id'] ?? null);
        $data['slug'] = $this->uniqueSlug($data['company_id'], $data['name'], $product->id);
        $oldImagePath = null;
        $newImagePath = null;
        $currentGalleryPaths = $product->gallery_paths ?? [];
        $removedGalleryPaths = array_values(array_intersect($currentGalleryPaths, $request->input('remove_gallery_paths', [])));
        $retainedGalleryPaths = array_values(array_diff($currentGalleryPaths, $removedGalleryPaths));

        if (count($retainedGalleryPaths) + count($request->file('gallery_images', [])) > 4) {
            throw ValidationException::withMessages(['gallery_images' => 'A product can have up to 4 gallery images.']);
        }

        $newGalleryPaths = [];

        try {
            if ($request->boolean('remove_image') || $request->hasFile('image')) {
                $oldImagePath = $product->image_path;
                $data['image_path'] = null;
            }

            if ($request->hasFile('image')) {
                $newImagePath = $request->file('image')->store('products/'.$data['slug'], 'public');
                $data['image_path'] = $newImagePath;
            }

            $newGalleryPaths = $this->storeGalleryImages($request, $data['slug']);
            $data['gallery_paths'] = array_values(array_merge($retainedGalleryPaths, $newGalleryPaths));

            DB::transaction(function () use ($product, $data, $suitabilityItems): void {
                $product->update($data);
                $this->syncSuitabilityItems($product, $suitabilityItems);
            });
        } catch (Throwable $exception) {
            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }
            Storage::disk('public')->delete($newGalleryPaths);
            throw $exception;
        }

        if ($oldImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }
        Storage::disk('public')->delete($removedGalleryPaths);

        return redirect()
            ->route('active8.page', 'products')
            ->with('status', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('active8.page', 'products')
            ->with('status', 'Product archived successfully.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['activate', 'draft', 'discontinue', 'recall', 'delete'])],
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        $products = Product::query()->whereIn('id', $data['product_ids']);

        match ($data['action']) {
            'activate' => $products->update(['status' => 'active']),
            'draft' => $products->update(['status' => 'draft']),
            'discontinue' => $products->update(['status' => 'discontinued']),
            'recall' => $products->update(['status' => 'recalled']),
            'delete' => $products->delete(),
        };

        return redirect()
            ->route('active8.page', 'products')
            ->with('status', 'Product bulk action applied successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        return $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'product_category_id' => [
                'nullable',
                Rule::exists('product_categories', 'id')->where(fn ($query) => $query->where('company_id', $request->input('company_id'))),
            ],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'remove_image' => ['nullable', 'boolean'],
            'gallery_images' => ['nullable', 'array', 'max:4'],
            'gallery_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'remove_gallery_paths' => ['nullable', 'array', 'max:4'],
            'remove_gallery_paths.*' => ['string', 'max:255'],
            'video_url' => [
                'nullable',
                'url',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! (new Product(['video_url' => (string) $value]))->videoEmbedUrl()) {
                        $fail('The product video must be a valid YouTube or Vimeo video link.');
                    }
                },
            ],
            'description' => ['nullable', 'string'],
            'ingredients' => ['nullable', 'string', 'max:5000'],
            'usage_instructions' => ['nullable', 'string', 'max:5000'],
            'faq_items' => ['nullable', 'array', 'max:12'],
            'faq_items.*.question' => ['required', 'string', 'max:255'],
            'faq_items.*.answer' => ['required', 'string', 'max:2000'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'country_of_origin' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'active', 'discontinued', 'recalled'])],
            'suitability_items' => ['nullable', 'array', 'max:20'],
            'suitability_items.*.type' => ['required', Rule::in(['age', 'audience', 'allergen', 'dietary', 'pregnancy', 'medical', 'usage', 'safety', 'storage', 'custom'])],
            'suitability_items.*.short_label' => ['required', 'string', 'max:80'],
            'suitability_items.*.detail' => ['nullable', 'string', 'max:500'],
            'suitability_items.*.severity' => ['required', Rule::in(['info', 'caution', 'warning'])],
            'suitability_items.*.visibility' => ['required', Rule::in(['public', 'internal'])],
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function syncSuitabilityItems(Product $product, array $items): void
    {
        $product->suitabilityItems()->delete();

        foreach (array_values($items) as $index => $item) {
            $product->suitabilityItems()->create([
                'type' => $item['type'],
                'short_label' => $item['short_label'],
                'detail' => $item['detail'] ?? null,
                'severity' => $item['severity'],
                'is_public' => $item['visibility'] === 'public',
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * @return array<int, string>
     */
    private function storeGalleryImages(Request $request, string $slug): array
    {
        return collect($request->file('gallery_images', []))
            ->map(fn ($image) => $image->store('products/'.$slug.'/gallery', 'public'))
            ->values()
            ->all();
    }

    private function categoryName(?int $categoryId): ?string
    {
        return $categoryId ? ProductCategory::query()->find($categoryId)?->name : null;
    }

    private function uniqueSlug(int $companyId, string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: 'product';
        $slug = $baseSlug;
        $suffix = 2;

        while (
            Product::query()
                ->where('company_id', $companyId)
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        return $slug;
    }
}
