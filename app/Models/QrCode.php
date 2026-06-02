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
        'status',
        'generated_at',
        'activated_at',
        'expires_at',
        'revoked_at',
        'revocation_reason',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'activated_at' => 'datetime',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
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

        return (new Writer($renderer))->writeString($this->verification_url);
    }
}
