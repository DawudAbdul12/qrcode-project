<section class="screen {{ $activePage === 'categories' ? 'active' : '' }}" id="screen-categories">
            <div class="topbar center">
                <div>
                    <div class="page-title">Product Categories</div>
                    <div class="page-subtitle">Organize each company's product catalog with reusable managed categories.</div>
                </div>
                <div class="topbar-actions">
                    <button class="btn-learn" type="button" data-category-drawer-open data-category-form="create">
                        <svg width="14" height="14"><use href="#i-plus"></use></svg>
                        Create Category
                    </button>
                    <a class="btn-share" href="{{ route('active8.page', 'products') }}">Products</a>
                </div>
            </div>

            <div class="company-page-layout categories-layout">
                <div>
                    @if (session('status'))
                        <div class="alert-success">{{ session('status') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert-error">{{ $errors->first() }}</div>
                    @endif

                    <div class="company-detail-grid">
                        <div class="company-detail-card"><div class="company-detail-label">Total Categories</div><div class="company-detail-value">{{ number_format($categories->count()) }}</div></div>
                        <div class="company-detail-card"><div class="company-detail-label">Active</div><div class="company-detail-value">{{ number_format($categories->where('status', 'active')->count()) }}</div></div>
                        <div class="company-detail-card"><div class="company-detail-label">Assigned Products</div><div class="company-detail-value">{{ number_format($categories->sum('products_count')) }}</div></div>
                        <div class="company-detail-card"><div class="company-detail-label">Unused</div><div class="company-detail-value">{{ number_format($categories->where('products_count', 0)->count()) }}</div></div>
                    </div>

                    <div class="admin-card">
                        <div class="admin-card-header">
                            <div>
                                <div class="admin-card-title">Category Registry</div>
                                <div class="admin-card-sub">Categories are scoped to a company and available when products are created or edited.</div>
                            </div>
                            <button class="btn-share" type="button" data-category-drawer-open data-category-form="create">
                                <svg width="14" height="14"><use href="#i-plus"></use></svg>
                                Create Category
                            </button>
                        </div>
                        <div class="table-toolbar">
                            <label class="table-search" aria-label="Search categories">
                                <svg width="14" height="14"><use href="#i-search"></use></svg>
                                <input type="search" data-category-search placeholder="Search category, company, description">
                            </label>
                            <div class="table-filter-row">
                                <select class="font-select" data-category-company-filter aria-label="Filter by company">
                                    <option value="all">All companies</option>
                                    @foreach ($companies as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach
                                </select>
                                <select class="font-select" data-category-status-filter aria-label="Filter by status">
                                    <option value="all">All statuses</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <select class="font-select" data-category-use-filter aria-label="Filter by usage">
                                    <option value="all">All usage</option>
                                    <option value="used">Assigned</option>
                                    <option value="unused">Unused</option>
                                </select>
                            </div>
                        </div>
                        <form class="table-bulk-bar" method="POST" action="{{ route('categories.bulk') }}" data-category-bulk-form data-confirm-delete data-delete-name="selected categories" data-delete-match="DELETE">
                            @csrf
                            <div><div class="table-bulk-count" data-category-bulk-count>0 selected</div><div class="company-sub-name">Apply changes to selected categories.</div></div>
                            <div class="table-bulk-actions">
                                <select class="font-select" name="action" required>
                                    <option value="activate">Activate</option>
                                    <option value="deactivate">Deactivate</option>
                                    <option value="delete">Delete</option>
                                </select>
                                <div data-category-bulk-inputs></div>
                                <button class="btn-share" type="submit">Apply</button>
                            </div>
                        </form>
                        <div class="admin-table-wrap">
                            <table class="admin-table">
                                <thead><tr><th class="fit"><input class="row-check" type="checkbox" data-category-select-all aria-label="Select all visible categories"></th><th>Category</th><th>Company</th><th class="fit">Products</th><th class="fit">Status</th><th class="fit">Actions</th></tr></thead>
                                <tbody>
                                    @forelse ($categories as $category)
                                        <tr class="row-clickable" data-category-row data-category-id="{{ $category->id }}" data-category-search-text="{{ Str::lower($category->name.' '.($category->company?->name ?? '').' '.($category->description ?? '')) }}" data-category-company="{{ $category->company_id }}" data-category-status="{{ $category->status }}" data-category-use="{{ $category->products_count > 0 ? 'used' : 'unused' }}">
                                            <td class="fit"><input class="row-check" type="checkbox" value="{{ $category->id }}" data-category-select aria-label="Select {{ $category->name }}"></td>
                                            <td><div class="company-main-name">{{ $category->name }}</div><div class="company-sub-name">{{ Str::limit($category->description ?: 'No description', 70) }}</div></td>
                                            <td>{{ $category->company?->name ?? 'No company' }}</td>
                                            <td class="fit">{{ number_format($category->products_count) }}</td>
                                            <td class="fit"><span class="status-pill {{ $category->status }}">{{ $category->status }}</span></td>
                                            <td class="fit"><div class="table-actions">
                                                <button class="icon-btn" type="button" data-category-drawer-open data-category-form="edit-{{ $category->id }}" aria-label="Edit category"><svg width="15" height="15"><use href="#i-edit"></use></svg></button>
                                                <form method="POST" action="{{ route('categories.destroy', $category) }}" data-confirm-delete data-delete-name="{{ $category->name }}" data-delete-match="{{ $category->name }}">@csrf @method('DELETE')<button class="icon-btn del" type="submit" aria-label="Delete category"><svg width="15" height="15"><use href="#i-trash"></use></svg></button></form>
                                            </div></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6">No product categories yet.</td></tr>
                                    @endforelse
                                    <tr class="filter-empty" data-category-no-results><td colspan="6"><div class="filter-empty-title">No categories match those filters.</div><div class="filter-empty-copy">Try another name, company, status, or usage state.</div><button class="btn-learn" type="button" data-clear-category-filters>Clear filters</button></td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="table-pagination" data-category-pagination><span data-category-page-summary>Showing categories</span><div class="pager-actions"><button class="pager-btn" type="button" data-category-page-prev aria-label="Previous category page">‹</button><span data-category-page-current>1</span><button class="pager-btn" type="button" data-category-page-next aria-label="Next category page">›</button></div></div>
                    </div>
                </div>
            </div>

            <div class="user-create-drawer" id="category-drawer" aria-hidden="true">
                <div class="drawer-backdrop" data-category-drawer-close></div>
                <div class="drawer-panel" role="dialog" aria-modal="true" aria-labelledby="category-drawer-title">
                    <div class="drawer-header"><div><div class="admin-card-title" id="category-drawer-title">Create Category</div><div class="admin-card-sub" id="category-drawer-subtitle">Add a reusable product category for one company.</div></div><button class="drawer-close" type="button" data-category-drawer-close aria-label="Close category panel">×</button></div>
                    <div class="drawer-body">
                        <form class="drawer-user-form active" id="category-form-create" method="POST" action="{{ route('categories.store') }}" data-form-title="Create Category" data-form-subtitle="Add a reusable product category for one company.">
                            @csrf
                            <label class="form-label">Company</label><select class="font-select" name="company_id" required><option value="">Select company</option>@foreach ($companies as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach</select>
                            <label class="form-label">Category Name</label><input class="input-field" name="name" placeholder="Skincare" required>
                            <label class="form-label">Description</label><textarea class="textarea-field" name="description" placeholder="Optional internal note about the products grouped here."></textarea>
                            <label class="form-label">Status</label><select class="font-select" name="status" required><option value="active">Active</option><option value="inactive">Inactive</option></select>
                            <button class="btn-add-content" type="submit">Create Category</button>
                        </form>
                        @foreach ($categories as $category)
                            <form class="drawer-user-form" id="category-form-edit-{{ $category->id }}" method="POST" action="{{ route('categories.update', $category) }}" data-form-title="Edit Category" data-form-subtitle="Update {{ $category->name }}.">
                                @csrf @method('PUT')
                                <label class="form-label">Company</label><select class="font-select" name="company_id" required>@foreach ($companies as $item)<option value="{{ $item->id }}" @selected($category->company_id === $item->id)>{{ $item->name }}</option>@endforeach</select>
                                <label class="form-label">Category Name</label><input class="input-field" name="name" value="{{ $category->name }}" required>
                                <label class="form-label">Description</label><textarea class="textarea-field" name="description">{{ $category->description }}</textarea>
                                <label class="form-label">Status</label><select class="font-select" name="status" required><option value="active" @selected($category->status === 'active')>Active</option><option value="inactive" @selected($category->status === 'inactive')>Inactive</option></select>
                                <button class="btn-add-content" type="submit">Save Category</button>
                            </form>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
