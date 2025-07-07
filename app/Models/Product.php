<?php

namespace App\Models;

use App\Contracts\Filterable;
use App\Contracts\Sortable;
use App\Filters\ProductFilters;
use App\Traits\Sortable as TraitsSortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Product Model
 *
 * Represents a product in the e-commerce system with category association,
 * pricing information, ratings, and image handling capabilities.
 *
 * @property int $id The unique identifier for the product
 * @property string $title The product title/name
 * @property float $price The product price in decimal format
 * @property string $description The detailed product description
 * @property string $image The product image path or URL
 * @property float $rating The average product rating (0-5 scale)
 * @property int $reviews_count The total number of reviews for this product
 * @property int $category_id The foreign key referencing the product's category
 * @property string|null $external_id External system identifier for product synchronization
 * @property \Illuminate\Support\Carbon $created_at Timestamp when the product was created
 * @property \Illuminate\Support\Carbon $updated_at Timestamp when the product was last updated
 * @property-read \App\Models\Category $category The category this product belongs to
 *
 * @method static \Database\Factories\ProductFactory factory() Create a new factory instance for the model
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 */
class Product extends Model implements Filterable, Sortable
{
    use HasFactory, TraitsSortable;

    /**
     * The attributes that are mass assignable.
     *
     * These fields can be filled using mass assignment methods like create() or fill().
     * Includes product details, pricing, rating information, and category association.
     *
     * @var array<string> List of fillable attribute names
     */
    protected $fillable = [
        'title',
        'price',
        'description',
        'image',
        'rating',
        'reviews_count',
        'category_id',
        'external_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * Automatically converts database values to appropriate PHP types:
     * - price: Stored as decimal, cast to float for calculations
     * - rating: Stored as decimal, cast to float for precision
     * - reviews_count: Stored as integer for counting purposes
     *
     * @var array<string, string> Attribute name to cast type mapping
     */
    protected $casts = [
        'price' => 'float',
        'rating' => 'float',
        'reviews_count' => 'integer',
    ];

    /**
     * Get the category that owns the product.
     *
     * Defines a many-to-one relationship where each product belongs to exactly one category.
     * This relationship is used for product categorization and filtering.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Category, \App\Models\Product>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get the formatted image URL for the product.
     *
     * This accessor automatically formats the image attribute to provide a complete URL.
     * If the stored value is already a full URL (external image), it returns as-is.
     * If it's a local path, it prepends the storage URL to create an accessible asset URL.
     *
     * @param  string  $image  The raw image path or URL from the database
     * @return string The formatted, accessible image URL
     */
    public function getImageAttribute(string $image): string
    {
        return filter_var($image, FILTER_VALIDATE_URL)
            ? $image
            : asset("storage/{$image}");
    }


    /**
     * Apply filters to the product query.
     *
     * This method uses the ProductFilters class to apply various filtering criteria
     * based on the provided filters array. It allows dynamic filtering of products
     * based on attributes like category, price range, rating, etc.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder instance
     * @param array $filters Associative array of filter criteria (e.g., ['category' => 1, 'min_price' => 100])
     * @return \Illuminate\Database\Eloquent\Builder The modified query with filters applied
     */
    public function scopeFilter($query, array $filters = [])
    {
        (new ProductFilters())->applyFilters($query, $filters);

        return $query;
    }

    /**
     * Get the fields that can be used for sorting.
     *
     * Returns an array of field names that are valid for sorting operations.
     * This is used to validate sort parameters in the Sortable trait.
     *
     * @return array<string> List of sortable field names
     */
    public function sortableFields(): array
    {
        return [
            'id',
            'price',
            'rating',
            'category_id',
            'reviews_count',
            'created_at',
            'updated_at',
        ];
    }
}
