<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductCategoryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['slug'] = $this->uniqueSlug($data['company_id'], $data['name']);

        ProductCategory::query()->create($data);

        return redirect()->route('active8.page', 'categories')->with('status', 'Category created successfully.');
    }

    public function update(Request $request, ProductCategory $category): RedirectResponse
    {
        $data = $this->validatedData($request);

        if ($category->company_id !== (int) $data['company_id'] && $category->products()->exists()) {
            return redirect()->route('active8.page', 'categories')->withErrors('Move or remove assigned products before changing the category company.');
        }

        $data['slug'] = $this->uniqueSlug($data['company_id'], $data['name'], $category->id);

        $category->update($data);
        $category->products()->update(['category' => $category->name]);

        return redirect()->route('active8.page', 'categories')->with('status', 'Category updated successfully.');
    }

    public function destroy(ProductCategory $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('active8.page', 'categories')->with('status', 'Category deleted successfully.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['activate', 'deactivate', 'delete'])],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'exists:product_categories,id'],
        ]);

        $categories = ProductCategory::query()->whereIn('id', $data['category_ids']);

        match ($data['action']) {
            'activate' => $categories->update(['status' => 'active']),
            'deactivate' => $categories->update(['status' => 'inactive']),
            'delete' => $categories->delete(),
        };

        return redirect()->route('active8.page', 'categories')->with('status', 'Category bulk action applied successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        return $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
    }

    private function uniqueSlug(int $companyId, string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: 'category';
        $slug = $baseSlug;
        $suffix = 2;

        while (
            ProductCategory::query()
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
