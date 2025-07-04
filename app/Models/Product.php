<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'price', 'description', 'image', 'rating', 'reviews_count', 'category_id', 'external_id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'float',
        'rating' => 'float',
        'reviews_count' => 'integer',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function getImageAttribute(string $image): string
    {
        // If the image is a URL, return it as is
        if (filter_var($image, FILTER_VALIDATE_URL)) {
            return $image;
        }

        // Otherwise, return the local storage path
        return asset("storage/{$image}");
    }
}
