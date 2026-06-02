<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'logo_path',
        'cover_image_path',
        'verification_headline',
        'genuine_message',
        'suspicious_message',
        'invalid_message',
        'support_text',
        'support_email',
        'support_phone',
        'website_url',
        'social_links',
        'faq_items',
        'custom_sections',
        'company_description',
        'manufacturer_name',
        'factory_address',
        'certification_number',
        'certificate_path',
        'layout_style',
        'primary_color',
        'secondary_color',
        'text_color',
        'background_color',
        'button_color',
        'button_text_color',
        'button_style',
        'font_family',
        'badge_style',
        'success_color',
        'warning_color',
        'error_color',
        'show_platform_branding',
        'publication_status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'faq_items' => 'array',
            'custom_sections' => 'array',
            'show_platform_branding' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
