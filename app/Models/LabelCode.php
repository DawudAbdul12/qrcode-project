<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabelCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'label_order_id',
        'company_id',
        'batch_id',
        'code',
        'status',
        'verification_count',
        'first_verified_at',
        'last_verified_at',
    ];

    protected function casts(): array
    {
        return [
            'verification_count' => 'integer',
            'first_verified_at' => 'datetime',
            'last_verified_at' => 'datetime',
        ];
    }

    public function labelOrder(): BelongsTo
    {
        return $this->belongsTo(LabelOrder::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function scanLogs(): HasMany
    {
        return $this->hasMany(ScanLog::class);
    }

    public function formattedCode(): string
    {
        $code = strtoupper($this->code);

        return substr($code, 0, 3).'-'.substr($code, 3, 4).'-'.substr($code, 7);
    }
}
