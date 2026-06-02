<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'sku',
        'category',
        'product_category_id',
        'image_path',
        'gallery_paths',
        'video_url',
        'description',
        'ingredients',
        'usage_instructions',
        'faq_items',
        'manufacturer',
        'country_of_origin',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'faq_items' => 'array',
            'gallery_paths' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function suitabilityItems(): HasMany
    {
        return $this->hasMany(ProductSuitabilityItem::class)->orderBy('sort_order');
    }

    public function videoEmbedUrl(): ?string
    {
        if (! $this->video_url) {
            return null;
        }

        $host = Str::lower(parse_url($this->video_url, PHP_URL_HOST) ?? '');
        $path = trim(parse_url($this->video_url, PHP_URL_PATH) ?? '', '/');

        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
            parse_str(parse_url($this->video_url, PHP_URL_QUERY) ?? '', $query);
            $videoId = $query['v'] ?? Str::afterLast($path, '/');

            return preg_match('/^[A-Za-z0-9_-]{6,20}$/', $videoId) ? 'https://www.youtube-nocookie.com/embed/'.$videoId : null;
        }

        if ($host === 'youtu.be') {
            return preg_match('/^[A-Za-z0-9_-]{6,20}$/', $path) ? 'https://www.youtube-nocookie.com/embed/'.$path : null;
        }

        if (in_array($host, ['vimeo.com', 'www.vimeo.com', 'player.vimeo.com'], true)) {
            $videoId = Str::afterLast($path, '/');

            return ctype_digit($videoId) ? 'https://player.vimeo.com/video/'.$videoId : null;
        }

        return null;
    }
}
