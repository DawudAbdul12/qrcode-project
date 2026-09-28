<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'qr_code_id',
        'label_code_id',
        'token_scanned',
        'status_result',
        'scanned_at',
        'ip_address',
        'user_agent',
        'country',
        'city',
        'device_type',
        'browser',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function qrCode(): BelongsTo
    {
        return $this->belongsTo(QrCode::class);
    }

    public function labelCode(): BelongsTo
    {
        return $this->belongsTo(LabelCode::class);
    }
}
