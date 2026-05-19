<?php

namespace App\Models;

use App\Enums\ProductStatus;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'sku',
    'category_id',
    'description',
    'price',
    'stock_quantity',
    'committed_quantity',
    'image_url',
    'status',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'category_id',
        'shop_id',
        'description',
        'price',
        'stock_quantity',
        'committed_quantity',
        'image_url',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'committed_quantity' => 'integer',
            'status' => ProductStatus::class,
        ];
    }

    public function scopeVisibleOnStorefront(Builder $query): Builder
    {
        return $query->whereIn('status', [
            ProductStatus::APPROVED->value,
            ProductStatus::OUT_OF_STOCK->value,
        ]);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function getImageUrlAttribute($value)
    {
        // 1. If there is a value in the DB and it starts with http (third-party image)
        if ($value && str_starts_with($value, 'http')) {
            return $value;
        }

        // 2. If there is a value in the DB and the file exists in the public/storage directory
        if ($value && file_exists(public_path('storage/'.$value))) {
            return asset('storage/'.$value);
        }

        // 3. Return the default image if the above conditions are not met
        return asset('images/no-image.png'); // Or link placeholder
    }

    public function isHidden(): bool
    {
        return $this->status === ProductStatus::HIDDEN;
    }

    public function canBeHidden(): bool
    {
        return in_array($this->status, [ProductStatus::APPROVED, ProductStatus::OUT_OF_STOCK]);
    }

    public function isRejected(): bool
    {
        return $this->status === ProductStatus::REJECTED;
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(ProductStatusLog::class);
    }
}
