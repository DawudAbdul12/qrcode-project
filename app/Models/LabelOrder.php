<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabelOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'batch_id',
        'label_type',
        'label_size',
        'print_quantity',
        'printer_vendor',
        'status',
        'delivery_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
            'print_quantity' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function labelCodes(): HasMany
    {
        return $this->hasMany(LabelCode::class);
    }
}
