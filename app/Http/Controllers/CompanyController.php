<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\ActivityLog;
use App\Models\Batch;
use App\Models\QrCode;
use App\Models\ScanLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function show(Request $request, Company $company): View
    {
        $company->load('profile');

        $products = $company->products()
            ->with('productCategory')
            ->withCount('batches')
            ->latest()
            ->get();
        $batches = $company->batches()
            ->with(['product', 'qrCode', 'labelOrders'])
            ->latest('batches.created_at')
            ->get();
        $labelOrders = $company->labelOrders()
            ->with('batch.product')
            ->latest()
            ->get();
        $scanLogs = ScanLog::query()
            ->whereHas('qrCode.batch.product', fn ($query) => $query->where('company_id', $company->id))
            ->with('qrCode.batch.product')
            ->latest('scanned_at')
            ->get();
        $qrCodeCount = QrCode::query()
            ->whereHas('batch.product', fn ($query) => $query->where('company_id', $company->id))
            ->count();
        $genuineScans = $scanLogs->where('status_result', 'genuine')->count();
        $flaggedScans = $scanLogs->whereIn('status_result', ['suspicious', 'invalid', 'revoked', 'expired', 'recalled'])->count();
        $activityLogs = Schema::hasTable('activity_logs')
            ? ActivityLog::query()
                ->with('user')
                ->where('subject_type', Batch::class)
                ->whereIn('subject_id', $batches->pluck('id'))
                ->latest()
                ->take(20)
                ->get()
            : collect();
        $tab = in_array($request->query('tab'), ['overview', 'products', 'batches', 'profile', 'analytics', 'activity'], true)
            ? $request->query('tab')
            : 'overview';

        return view('companies.show', [
            'company' => $company,
            'profile' => $company->profile,
            'products' => $products,
            'batches' => $batches,
            'labelOrders' => $labelOrders,
            'scanLogs' => $scanLogs,
            'recentScans' => $scanLogs->take(8),
            'recentOrders' => $labelOrders->take(5),
            'recentBatches' => $batches->take(5),
            'activityLogs' => $activityLogs,
            'qrCodeCount' => $qrCodeCount,
            'genuineScans' => $genuineScans,
            'flaggedScans' => $flaggedScans,
            'genuineRate' => $scanLogs->count() > 0 ? round(($genuineScans / $scanLogs->count()) * 100, 1) : 0,
            'tab' => $tab,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $profileData = $this->validatedProfileData($request);
        $storedPaths = $this->storeProfileImages($request, $data['slug']);
        $profileData = array_merge($profileData, $storedPaths);

        try {
            $company = DB::transaction(function () use ($data, $profileData): Company {
                $company = Company::query()->create($data);
                $company->profile()->create($profileData);

                return $company;
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete(array_values($storedPaths));

            throw $exception;
        }

        return redirect()
            ->route('active8.page', ['page' => 'company-profile', 'company' => $company->id])
            ->with('status', $company->status === 'pending' ? 'Company draft saved successfully.' : 'Company created successfully.');
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['slug'] = $this->uniqueSlug($data['name'], $company->id);

        $company->update($data);

        return redirect()->route('active8.page', 'companies')->with('status', 'Company updated successfully.');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $company->delete();

        return redirect()->route('active8.page', 'companies')->with('status', 'Company archived successfully.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['activate', 'suspend', 'delete'])],
            'company_ids' => ['required', 'array', 'min:1'],
            'company_ids.*' => ['integer', 'exists:companies,id'],
        ]);

        $companies = Company::query()->whereIn('id', $data['company_ids']);

        match ($data['action']) {
            'activate' => $companies->update(['status' => 'active']),
            'suspend' => $companies->update(['status' => 'suspended']),
            'delete' => $companies->delete(),
        };

        return redirect()->route('active8.page', 'companies')->with('status', 'Company bulk action applied successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'status' => ['required', Rule::in(['active', 'pending', 'suspended'])],
            'notes' => ['nullable', 'string'],
        ]);

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedProfileData(Request $request): array
    {
        $data = $request->validate([
            'profile.verification_headline' => ['nullable', 'string', 'max:255'],
            'profile.genuine_message' => ['nullable', 'string'],
            'profile.suspicious_message' => ['nullable', 'string'],
            'profile.invalid_message' => ['nullable', 'string'],
            'profile.support_text' => ['nullable', 'string'],
            'profile.support_email' => ['nullable', 'email', 'max:255'],
            'profile.support_phone' => ['nullable', 'string', 'max:255'],
            'profile.website_url' => ['nullable', 'url', 'max:255'],
            'profile.social_links' => ['nullable', 'array', 'max:8'],
            'profile.social_links.*.label' => ['required', Rule::in(['Instagram', 'Facebook', 'X', 'LinkedIn', 'YouTube', 'TikTok', 'WhatsApp'])],
            'profile.social_links.*.url' => ['required', 'url', 'max:255'],
            'profile.company_description' => ['nullable', 'string'],
            'profile.manufacturer_name' => ['nullable', 'string', 'max:255'],
            'profile.factory_address' => ['nullable', 'string'],
            'profile.certification_number' => ['nullable', 'string', 'max:255'],
            'profile.layout_style' => ['required', Rule::in(['default', 'product-first', 'full-cover'])],
            'profile.primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'profile.secondary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'profile.button_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'profile.font_family' => ['required', Rule::in(['DM Sans', 'Inter', 'Google Sans'])],
            'profile.badge_style' => ['required', Rule::in(['official', 'minimal', 'bold'])],
            'profile.show_platform_branding' => ['nullable', 'boolean'],
            'profile.logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'profile.cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ])['profile'];

        unset($data['logo'], $data['cover_image']);

        return $data;
    }

    /**
     * @return array<string, string>
     */
    private function storeProfileImages(Request $request, string $slug): array
    {
        $paths = [];

        if ($request->hasFile('profile.logo')) {
            $paths['logo_path'] = $request->file('profile.logo')->store('company-profiles/'.$slug, 'public');
        }

        if ($request->hasFile('profile.cover_image')) {
            $paths['cover_image_path'] = $request->file('profile.cover_image')->store('company-profiles/'.$slug, 'public');
        }

        return $paths;
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: 'company';
        $slug = $baseSlug;
        $suffix = 2;

        while (
            Company::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        return $slug;
    }
}
