<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Batch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'batch_number',
        'manufacture_date',
        'expiry_date',
        'quantity',
        'production_location',
        'qa_status',
        'certificate_path',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'manufacture_date' => 'date',
            'expiry_date' => 'date',
            'quantity' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function qrCode(): HasOne
    {
        return $this->hasOne(QrCode::class);
    }

    public function labelOrders(): HasMany
    {
        return $this->hasMany(LabelOrder::class);
    }
}
