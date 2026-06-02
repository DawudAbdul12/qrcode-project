<?php

namespace App\Models;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QrCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'token',
        'verification_url',
        'custom_path',
        'status',
        'generated_at',
        'activated_at',
        'expires_at',
        'revoked_at',
        'revocation_reason',
        'foreground_color',
        'background_color',
        'label_text',
        'show_label',
        'show_logo',
        'external_link_label',
        'external_link_url',
        'show_external_link',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'activated_at' => 'datetime',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
            'show_label' => 'boolean',
            'show_logo' => 'boolean',
            'show_external_link' => 'boolean',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function scanLogs(): HasMany
    {
        return $this->hasMany(ScanLog::class);
    }

    public function isRevoked(): bool
    {
        return $this->status === 'revoked' || $this->revoked_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || ($this->expires_at && $this->expires_at->isPast());
    }

    public function svg(int $size = 160): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size, 2),
            new SvgImageBackEnd()
        );

        $svg = (new Writer($renderer))->writeString($this->publicUrl());
        $foreground = $this->validColor($this->foreground_color, '#111827');
        $background = $this->validColor($this->background_color, '#ffffff');

        $svg = str_replace('fill="#ffffff"', 'fill="'.$background.'"', $svg);
        $svg = str_replace('fill="#000000"', 'fill="'.$foreground.'"', $svg);

        if ($this->show_logo) {
            $svg = $this->withCenterBadge($svg, $size);
        }

        if ($this->show_label) {
            $svg = $this->withLabel($svg, $size);
        }

        return $svg;
    }

    public function publicUrl(): string
    {
        return url('/verify/'.$this->publicPath());
    }

    public function publicPath(): string
    {
        return $this->custom_path ?: $this->token;
    }

    private function validColor(?string $value, string $fallback): string
    {
        return preg_match('/^#[0-9a-fA-F]{6}$/', (string) $value) ? (string) $value : $fallback;
    }

    private function withCenterBadge(string $svg, int $size): string
    {
        $badgeSize = max(28, (int) round($size * 0.18));
        $badgeX = (int) round(($size - $badgeSize) / 2);
        $badgeY = $badgeX;
        $fontSize = max(9, (int) round($badgeSize * 0.36));
        $companyName = (string) $this->batch?->product?->company?->name;
        $initials = collect(explode(' ', $companyName))
            ->filter()
            ->map(fn (string $word) => substr($word, 0, 1))
            ->take(2)
            ->implode('') ?: 'A8';
        $initials = e(strtoupper(substr($initials, 0, 2)));
        $badge = '<rect x="'.$badgeX.'" y="'.$badgeY.'" width="'.$badgeSize.'" height="'.$badgeSize.'" rx="'.max(6, (int) round($badgeSize * 0.22)).'" fill="#ffffff" stroke="'.$this->validColor($this->foreground_color, '#111827').'" stroke-width="2"/>';
        $badge .= '<text x="'.($size / 2).'" y="'.($size / 2 + $fontSize / 3).'" text-anchor="middle" font-family="Arial, sans-serif" font-size="'.$fontSize.'" font-weight="700" fill="'.$this->validColor($this->foreground_color, '#111827').'">'.$initials.'</text>';

        return str_replace('</svg>', $badge.'</svg>', $svg);
    }

    private function withLabel(string $svg, int $size): string
    {
        $label = trim((string) ($this->label_text ?: $this->batch?->batch_number));

        if ($label === '') {
            return $svg;
        }

        $label = e(\Illuminate\Support\Str::limit($label, 42));
        $labelHeight = 54;
        $newHeight = $size + $labelHeight;
        $background = $this->validColor($this->background_color, '#ffffff');
        $foreground = $this->validColor($this->foreground_color, '#111827');
        $labelMarkup = '<rect x="0" y="'.$size.'" width="'.$size.'" height="'.$labelHeight.'" fill="'.$background.'"/>';
        $labelMarkup .= '<text x="'.($size / 2).'" y="'.($size + 24).'" text-anchor="middle" font-family="Arial, sans-serif" font-size="15" font-weight="700" fill="'.$foreground.'">'.$label.'</text>';
        $labelMarkup .= '<text x="'.($size / 2).'" y="'.($size + 42).'" text-anchor="middle" font-family="Arial, sans-serif" font-size="10" font-weight="600" fill="#64748b">Active8 verified batch</text>';

        $svg = preg_replace('/height="\d+"/', 'height="'.$newHeight.'"', $svg, 1);
        $svg = preg_replace('/viewBox="0 0 \d+ \d+"/', 'viewBox="0 0 '.$size.' '.$newHeight.'"', $svg, 1);

        return str_replace('</svg>', $labelMarkup.'</svg>', $svg);
    }
}
