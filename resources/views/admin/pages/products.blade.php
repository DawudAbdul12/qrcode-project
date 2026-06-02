<section class="screen {{ $activePage === 'products' ? 'active' : '' }}" id="screen-products">
            <div class="topbar center">
                <div>
                    <div class="page-title">Products</div>
                    <div class="page-subtitle">Create, update, archive, and review products before assigning production batches.</div>
                </div>
                <div class="topbar-actions">
                    <button class="btn-learn" type="button" data-product-drawer-open data-product-form="create">
                        <svg width="14" height="14"><use href="#i-plus"></use></svg>
                        Create Product
                    </button>
                    <a class="btn-learn" href="{{ route('active8.page', 'categories') }}">Categories</a>
                    <a class="btn-learn" href="{{ route('active8.page', 'companies') }}">Companies</a>
                    <a class="btn-share" href="{{ route('active8.page', 'dashboard') }}">
                        <svg width="14" height="14"><use href="#i-activity"></use></svg>
                        Dashboard
                    </a>
                </div>
            </div>

            <div class="company-page-layout products-layout">
                <div>
                    @if (session('status'))
                        <div class="alert-success">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert-error">{{ $errors->first() }}</div>
                    @endif

                    <div class="company-detail-grid">
                        <div class="company-detail-card">
                            <div class="company-detail-label">Total Products</div>
                            <div class="company-detail-value">{{ number_format($products->count()) }}</div>
                        </div>
                        <div class="company-detail-card">
                            <div class="company-detail-label">Active Products</div>
                            <div class="company-detail-value">{{ number_format($products->where('status', 'active')->count()) }}</div>
                        </div>
                        <div class="company-detail-card">
                            <div class="company-detail-label">Products With Batches</div>
                            <div class="company-detail-value">{{ number_format($products->filter(fn ($item) => $item->batches_count > 0)->count()) }}</div>
                        </div>
                        <div class="company-detail-card">
                            <div class="company-detail-label">Recalled</div>
                            <div class="company-detail-value">{{ number_format($products->where('status', 'recalled')->count()) }}</div>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="admin-card-header">
                            <div>
                                <div class="admin-card-title">Product Registry</div>
                                <div class="admin-card-sub">Products belong to companies. Batches and batch-level QR codes are created from these records.</div>
                            </div>
                            <button class="btn-share" type="button" data-product-drawer-open data-product-form="create">
                                <svg width="14" height="14"><use href="#i-plus"></use></svg>
                                Create Product
                            </button>
                        </div>

                        <div class="table-toolbar" data-product-table-toolbar>
                            <label class="table-search" aria-label="Search products">
                                <svg width="14" height="14"><use href="#i-search"></use></svg>
                                <input type="search" data-product-search placeholder="Search product, company, SKU, category">
                            </label>
                            <div class="table-filter-row">
                                <select class="font-select" data-product-company-filter aria-label="Filter by company">
                                    <option value="all">All companies</option>
                                    @foreach ($companies as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                <select class="font-select" data-product-status-filter aria-label="Filter by status">
                                    <option value="all">All statuses</option>
                                    <option value="draft">Draft</option>
                                    <option value="active">Active</option>
                                    <option value="discontinued">Discontinued</option>
                                    <option value="recalled">Recalled</option>
                                </select>
                                <select class="font-select" data-product-batch-filter aria-label="Filter by batch presence">
                                    <option value="all">All batch states</option>
                                    <option value="with">With batches</option>
                                    <option value="without">No batches</option>
                                </select>
                                <select class="font-select" data-product-category-filter aria-label="Filter by category">
                                    <option value="all">All categories</option>
                                    @foreach ($categories->sortBy('name') as $category)
                                        <option value="{{ $category->slug }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <form class="table-bulk-bar" method="POST" action="{{ route('products.bulk') }}" data-product-bulk-form data-confirm-delete data-delete-name="selected products" data-delete-match="DELETE">
                            @csrf
                            <div>
                                <div class="table-bulk-count" data-product-bulk-count>0 selected</div>
                                <div class="company-sub-name">Apply lifecycle changes to selected products.</div>
                            </div>
                            <div class="table-bulk-actions">
                                <select class="font-select" name="action" required>
                                    <option value="activate">Activate</option>
                                    <option value="draft">Move to draft</option>
                                    <option value="discontinue">Discontinue</option>
                                    <option value="recall">Recall</option>
                                    <option value="delete">Archive</option>
                                </select>
                                <div data-product-bulk-inputs></div>
                                <button class="btn-share" type="submit">Apply</button>
                            </div>
                        </form>

                        <div class="admin-table-wrap">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th class="fit"><input class="row-check" type="checkbox" data-product-select-all aria-label="Select all visible products"></th>
                                        <th>Product</th>
                                        <th>Company</th>
                                        <th>SKU</th>
                                        <th>Category</th>
                                        <th class="fit">Batches</th>
                                        <th class="fit">Status</th>
                                        <th class="fit">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($products as $product)
                                        @php
                                            $productHealthClass = 'info';
                                            $productHealthLabel = 'Draft';

                                            if ($product->status === 'recalled') {
                                                $productHealthClass = 'danger';
                                                $productHealthLabel = 'Recalled';
                                            } elseif ($product->status === 'discontinued') {
                                                $productHealthClass = 'warning';
                                                $productHealthLabel = 'Discontinued';
                                            } elseif ($product->status === 'active' && $product->batches_count > 0) {
                                                $productHealthClass = 'ready';
                                                $productHealthLabel = 'Ready for scans';
                                            } elseif ($product->status === 'active') {
                                                $productHealthClass = 'warning';
                                                $productHealthLabel = 'No batches';
                                            }
                                        @endphp
                                        <tr class="row-clickable {{ $product->status === 'recalled' ? 'row-danger' : '' }}" data-product-row data-product-id="{{ $product->id }}" data-product-search-text="{{ Str::lower(($product->name ?? '').' '.($product->company?->name ?? '').' '.($product->sku ?? '').' '.($product->category ?? '').' '.($product->manufacturer ?? '').' '.($product->country_of_origin ?? '')) }}" data-product-company="{{ $product->company_id }}" data-product-status="{{ $product->status }}" data-product-batches="{{ $product->batches_count > 0 ? 'with' : 'without' }}" data-product-category="{{ Str::slug($product->category ?? 'uncategorized') }}">
                                            <td class="fit"><input class="row-check" type="checkbox" value="{{ $product->id }}" data-product-select aria-label="Select {{ $product->name }}"></td>
                                            <td>
                                                <div class="company-main-name">{{ $product->name }}</div>
                                                <div style="margin-top:7px"><span class="health-pill {{ $productHealthClass }}">{{ $productHealthLabel }}</span></div>
                                            </td>
                                            <td>
                                                <div class="company-main-name">{{ $product->company?->name ?? 'No company' }}</div>
                                                <div class="company-sub-name">{{ $product->manufacturer ?? 'No manufacturer' }}</div>
                                            </td>
                                            <td>{{ $product->sku ?? 'No SKU' }}</td>
                                            <td>{{ $product->category ?? 'Uncategorized' }}</td>
                                            <td class="fit">{{ number_format($product->batches_count) }}</td>
                                            <td class="fit"><span class="status-pill {{ $product->status }}">{{ $product->status }}</span></td>
                                            <td class="fit">
                                                <div class="table-actions">
                                                    <button class="icon-btn" type="button" data-product-drawer-open data-product-form="details-{{ $product->id }}" aria-label="View product details">
                                                        <svg width="15" height="15"><use href="#i-eye"></use></svg>
                                                    </button>
                                                    <button class="icon-btn" type="button" data-product-drawer-open data-product-form="edit-{{ $product->id }}" aria-label="Edit product">
                                                        <svg width="15" height="15"><use href="#i-edit"></use></svg>
                                                    </button>
                                                    <button class="icon-btn" type="button" data-create-batch-product="{{ $product->id }}" data-create-batch-label="{{ $product->name }} · {{ $product->company?->name }}" aria-label="Create batch for {{ $product->name }}">
                                                        <svg width="15" height="15"><use href="#i-form"></use></svg>
                                                    </button>
                                                    <form method="POST" action="{{ route('products.destroy', $product) }}" data-confirm-delete data-delete-name="{{ $product->name }}" data-delete-match="{{ $product->name }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="icon-btn del" type="submit" aria-label="Archive product">
                                                            <svg width="15" height="15"><use href="#i-trash"></use></svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr data-product-empty><td colspan="8">@include('partials.admin-empty-state', ['icon' => 'i-bag', 'title' => 'No products yet', 'copy' => 'Create a product before adding production batches and QR verification codes.', 'actionLabel' => 'Create Product', 'actionTarget' => 'data-product-drawer-open data-product-form="create"'])</td></tr>
                                    @endforelse
                                    <tr class="filter-empty" data-product-no-results>
                                        <td colspan="8">
                                            <div class="filter-empty-title">No products match those filters.</div>
                                            <div class="filter-empty-copy">Try another product, company, status, or batch state.</div>
                                            <button class="btn-learn" type="button" data-clear-product-filters>Clear filters</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="table-pagination" data-product-pagination>
                            <span data-product-page-summary>Showing products</span>
                            <div class="pager-actions">
                                <button class="pager-btn" type="button" data-product-page-prev aria-label="Previous product page">‹</button>
                                <span data-product-page-current>1</span>
                                <button class="pager-btn" type="button" data-product-page-next aria-label="Next product page">›</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="user-create-drawer" id="product-drawer" aria-hidden="true">
                <div class="drawer-backdrop" data-product-drawer-close></div>
                <div class="drawer-panel" role="dialog" aria-modal="true" aria-labelledby="product-drawer-title">
                    <div class="drawer-header">
                        <div>
                            <div class="admin-card-title" id="product-drawer-title">Create Product</div>
                            <div class="admin-card-sub" id="product-drawer-subtitle">Add a product under a client company. Batches come next.</div>
                        </div>
                        <button class="drawer-close" type="button" data-product-drawer-close aria-label="Close product panel">×</button>
                    </div>
                    <div class="drawer-body">
                        <form class="drawer-user-form active" id="product-form-create" method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" data-form-title="Create Product" data-form-subtitle="Add a product under a client company. Batches come next.">
                            @csrf
                            @include('partials.product-wizard', ['product' => null])
                        </form>

                        @foreach ($products as $product)
                            <form class="drawer-user-form" id="product-form-edit-{{ $product->id }}" method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data" data-form-title="Edit Product" data-form-subtitle="Update {{ $product->name }}.">
                                @csrf
                                @method('PUT')
                                @include('partials.product-wizard', ['product' => $product])
                            </form>
                        @endforeach

                        @foreach ($products as $product)
                            <div class="drawer-user-form activity-panel" id="product-form-details-{{ $product->id }}" data-form-title="Product Details" data-form-subtitle="{{ $product->name }} lifecycle and batch summary.">
                                <div class="activity-hero">
                                    <div class="company-logo-sm" style="width:48px;height:48px;font-size:16px;overflow:hidden">@if($product->image_path)<img src="{{ '/storage/'.ltrim($product->image_path, '/') }}" alt="{{ $product->name }}" style="width:100%;height:100%;object-fit:cover">@else{{ Str::of($product->name)->explode(' ')->filter()->map(fn ($word) => Str::substr($word, 0, 1))->take(2)->implode('') }}@endif</div>
                                    <div style="min-width:0">
                                        <div class="company-main-name">{{ $product->name }}</div>
                                        <div class="company-sub-name">{{ $product->company?->name ?? 'No company' }}</div>
                                    </div>
                                </div>
                                <div class="activity-meta">
                                    <div class="activity-meta-row"><span>Status</span><strong>{{ ucfirst($product->status) }}</strong></div>
                                    <div class="activity-meta-row"><span>SKU</span><strong>{{ $product->sku ?? 'No SKU' }}</strong></div>
                                    <div class="activity-meta-row"><span>Category</span><strong>{{ $product->category ?? 'Uncategorized' }}</strong></div>
                                    <div class="activity-meta-row"><span>Manufacturer</span><strong>{{ $product->manufacturer ?? 'Not recorded' }}</strong></div>
                                    <div class="activity-meta-row"><span>Origin</span><strong>{{ $product->country_of_origin ?? 'Not recorded' }}</strong></div>
                                    <div class="activity-meta-row"><span>Video</span><strong>@if($product->video_url)<a href="{{ $product->video_url }}" target="_blank" rel="noopener">View link</a>@else Not recorded @endif</strong></div>
                                    <div class="activity-meta-row"><span>Batches</span><strong>{{ number_format($product->batches_count) }}</strong></div>
                                </div>
                                @if ($product->description)
                                    <div class="role-preview">
                                        <div class="role-preview-title">Description</div>
                                        <div class="company-sub-name" style="line-height:1.55">{{ $product->description }}</div>
                                    </div>
                                @endif
                                @if ($product->suitabilityItems->isNotEmpty())
                                    <div class="role-preview">
                                        <div class="role-preview-title">Suitability & Warnings</div>
                                        <div class="role-preview-list">
                                            @foreach ($product->suitabilityItems as $item)
                                                <span>{{ $item->short_label }}{{ $item->is_public ? '' : ' (internal)' }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                @if (! empty($product->faq_items))
                                    <div class="role-preview">
                                        <div class="role-preview-title">Product FAQs</div>
                                        <div class="role-preview-list">
                                            @foreach ($product->faq_items as $item)
                                                <span>{{ $item['question'] ?? 'Question' }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                <div class="form-row-2" style="margin-top:14px">
                                    <button class="btn-share" type="button" data-product-drawer-open data-product-form="edit-{{ $product->id }}" style="width:100%;justify-content:center">Edit Product</button>
                                    <button class="btn-learn" type="button" data-create-batch-product="{{ $product->id }}" data-create-batch-label="{{ $product->name }} · {{ $product->company?->name }}" style="width:100%;justify-content:center">Create Batch</button>
                                </div>
                            </div>
                        @endforeach

                        <div class="drawer-guide">
                            <div class="admin-card-title">Product Flow</div>
                            <div class="onboarding-steps" style="grid-template-columns:1fr;border:1px solid var(--gray-200);border-radius:var(--radius);margin:16px 0 0;overflow:hidden">
                                <div class="onboarding-step"><div class="step-num done">1</div><div style="flex:1"><div class="step-title active" style="text-decoration:none;color:var(--gray-900)">Create product</div><div class="step-sub">Attach it to a company profile.</div></div></div>
                                <div class="onboarding-step"><div class="step-num">2</div><div style="flex:1"><div class="step-title active">Create batch</div><div class="step-sub">Manufacture date, expiry, quantity, QA status.</div></div></div>
                                <div class="onboarding-step"><div class="step-num">3</div><div style="flex:1"><div class="step-title active">Generate QR</div><div class="step-sub">One QR code per batch for hologram labels.</div></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
