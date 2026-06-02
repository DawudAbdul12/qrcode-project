<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class CompanyProfileController extends Controller
{
    public function update(Request $request, Company $company): RedirectResponse
    {
        $data = $request->validate([
            'company.name' => ['required', 'string', 'max:255'],
            'company.registration_number' => ['nullable', 'string', 'max:255'],
            'company.industry' => ['nullable', 'string', 'max:255'],
            'company.country' => ['nullable', 'string', 'max:255'],
            'company.region' => ['nullable', 'string', 'max:255'],
            'company.city' => ['nullable', 'string', 'max:255'],
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
            'profile.custom_sections' => ['nullable', 'array', 'max:8'],
            'profile.custom_sections.*.title' => ['required', 'string', 'max:255'],
            'profile.custom_sections.*.body' => ['required', 'string', 'max:3000'],
            'profile.company_description' => ['nullable', 'string', 'max:3000'],
            'profile.manufacturer_name' => ['nullable', 'string', 'max:255'],
            'profile.factory_address' => ['nullable', 'string', 'max:1000'],
            'profile.certification_number' => ['nullable', 'string', 'max:255'],
            'profile.layout_style' => ['required', Rule::in(['default', 'product-first', 'full-cover'])],
            'profile.primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'profile.secondary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'profile.text_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'profile.background_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'profile.button_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'profile.button_text_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'profile.success_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'profile.warning_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'profile.error_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'profile.font_family' => ['required', Rule::in(['DM Sans', 'Inter', 'Google Sans'])],
            'profile.badge_style' => ['required', Rule::in(['official', 'minimal', 'bold'])],
            'profile.button_style' => ['required', Rule::in(['solid', 'outline', 'soft'])],
            'profile.show_platform_branding' => ['required', 'boolean'],
            'profile.logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'profile.cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'profile.certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_cover_image' => ['nullable', 'boolean'],
            'remove_certificate' => ['nullable', 'boolean'],
            'publish_action' => ['required', Rule::in(['draft', 'publish'])],
        ]);

        $profile = $company->profile()->firstOrCreate();
        $profileData = $data['profile'];
        unset($profileData['logo'], $profileData['cover_image'], $profileData['certificate']);
        $newPaths = [];
        $oldPaths = [];

        foreach ([
            'logo' => 'logo_path',
            'cover_image' => 'cover_image_path',
            'certificate' => 'certificate_path',
        ] as $upload => $column) {
            if ($request->boolean('remove_'.$upload) || $request->hasFile('profile.'.$upload)) {
                if ($profile->{$column}) {
                    $oldPaths[] = $profile->{$column};
                }
                $profileData[$column] = null;
            }

            if ($request->hasFile('profile.'.$upload)) {
                $newPaths[$column] = $request->file('profile.'.$upload)->store('company-profiles/'.$company->slug, 'public');
                $profileData[$column] = $newPaths[$column];
            }
        }

        $profileData['publication_status'] = $data['publish_action'] === 'publish' ? 'published' : 'draft';
        $profileData['published_at'] = $data['publish_action'] === 'publish' ? now() : $profile->published_at;

        try {
            DB::transaction(function () use ($company, $data, $profile, $profileData): void {
                $company->update($data['company']);
                $profile->update($profileData);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete(array_values($newPaths));
            throw $exception;
        }

        Storage::disk('public')->delete($oldPaths);

        return redirect()
            ->route('active8.page', ['page' => 'company-profile', 'company' => $company->id])
            ->with('status', $data['publish_action'] === 'publish' ? 'Verification profile published successfully.' : 'Verification profile draft saved.');
    }
}
