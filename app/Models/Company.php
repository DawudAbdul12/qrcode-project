<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'registration_number',
        'industry',
        'contact_person',
        'email',
        'phone',
        'country',
        'region',
        'city',
        'website',
        'status',
        'notes',
    ];

    public function profile(): HasOne
    {
        return $this->hasOne(CompanyProfile::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function productCategories(): HasMany
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function batches(): HasManyThrough
    {
        return $this->hasManyThrough(Batch::class, Product::class);
    }

    public function labelOrders(): HasMany
    {
        return $this->hasMany(LabelOrder::class);
    }
}
