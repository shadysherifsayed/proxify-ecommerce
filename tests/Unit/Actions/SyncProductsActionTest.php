<?php

namespace Tests\Unit\Actions;

use App\Actions\SyncProductsAction;
use App\Clients\FakeStore\ProductClient;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SyncProductsActionTest extends TestCase
{
    use RefreshDatabase;

    private SyncProductsAction $syncProductsAction;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the action with a real ProductClient but fake HTTP responses
        $this->syncProductsAction = new SyncProductsAction(new ProductClient());
    }

    public function test_can_be_instantiated_with_product_client(): void
    {
        $this->assertInstanceOf(SyncProductsAction::class, $this->syncProductsAction);
    }

    public function test_throws_exception_when_api_request_fails(): void
    {
        Http::fake([
            '*' => Http::response(null, 500)
        ]);

        $this->expectException(\Exception::class);

        $this->expectExceptionMessage('Failed to fetch products from FakeStore API');

        $this->syncProductsAction->execute();
    }

    public function test_syncs_products_successfully_with_valid_api_response(): void
    {
        $apiProducts = [
            [
                'id' => 1,
                'title' => 'Test Product 1',
                'price' => 99.99,
                'description' => 'Test product description',
                'image' => 'https://example.com/image1.jpg',
                'category' => 'electronics',
                'rating' => [
                    'rate' => 4.5,
                    'count' => 120
                ]
            ],
            [
                'id' => 2,
                'title' => 'Test Product 2',
                'price' => 49.99,
                'description' => 'Another test product description',
                'image' => 'https://example.com/image2.jpg',
                'category' => 'clothing',
                'rating' => [
                    'rate' => 4.0,
                    'count' => 85
                ]
            ]
        ];

        Http::fake([
            '*' => Http::response($apiProducts, 200)
        ]);

        $this->syncProductsAction->execute();

        // Verify categories were created
        $this->assertDatabaseHas('categories', ['name' => 'electronics']);

        $this->assertDatabaseHas('categories', ['name' => 'clothing']);

          // Verify product-category relationships
        $electronicsCategory = Category::where('name', 'electronics')->first();

        $clothingCategory = Category::where('name', 'clothing')->first();

        // Verify products were created
        $this->assertDatabaseHas('products', [
            'external_id' => 1,
            'title' => 'Test Product 1',
            'price' => 99.99,
            'description' => 'Test product description',
            'image' => 'https://example.com/image1.jpg',
            'rating' => 4.5,
            'reviews_count' => 120,
            'category_id' => $electronicsCategory->id
        ]);

        $this->assertDatabaseHas('products', [
            'external_id' => 2,
            'title' => 'Test Product 2',
            'price' => 49.99,
            'description' => 'Another test product description',
            'image' => 'https://example.com/image2.jpg',
            'rating' => 4.0,
            'reviews_count' => 85,
            'category_id' => $clothingCategory->id
        ]);

    }

    public function test_updates_existing_product_when_external_id_exists(): void
    {
        // Create an existing product
        $category = Category::factory()->create(['name' => 'electronics']);
        $existingProduct = Product::factory()->create([
            'external_id' => 1,
            'title' => 'Old Title',
            'price' => 50.00,
            'category_id' => $category->id
        ]);

        $apiProducts = [
            [
                'id' => 1,
                'title' => 'Updated Product Title',
                'price' => 99.99,
                'description' => 'Updated description',
                'image' => 'https://example.com/updated-image.jpg',
                'category' => 'electronics',
                'rating' => [
                    'rate' => 4.8,
                    'count' => 200
                ]
            ]
        ];

        Http::fake([
            '*' => Http::response($apiProducts, 200)
        ]);

        $this->syncProductsAction->execute();

        // Verify the product was updated, not duplicated
        $this->assertEquals(1, Product::where('external_id', 1)->count());

        // Verify the product data was updated
        $updatedProduct = Product::where('external_id', 1)->first();
        $this->assertEquals('Updated Product Title', $updatedProduct->title);
        $this->assertEquals(99.99, $updatedProduct->price);
        $this->assertEquals('Updated description', $updatedProduct->description);
        $this->assertEquals('https://example.com/updated-image.jpg', $updatedProduct->image);
        $this->assertEquals(4.8, $updatedProduct->rating);
        $this->assertEquals(200, $updatedProduct->reviews_count);
    }

    public function test_creates_new_category_if_not_exists(): void
    {
        $apiProducts = [
            [
                'id' => 1,
                'title' => 'Test Product',
                'price' => 99.99,
                'description' => 'Test description',
                'image' => 'https://example.com/image.jpg',
                'category' => 'new-category',
                'rating' => [
                    'rate' => 4.5,
                    'count' => 100
                ]
            ]
        ];

        Http::fake([
            '*' => Http::response($apiProducts, 200)
        ]);

        // Verify category doesn't exist before sync
        $this->assertDatabaseMissing('categories', ['name' => 'new-category']);

        $this->syncProductsAction->execute();

        // Verify category was created
        $this->assertDatabaseHas('categories', ['name' => 'new-category']);

        // Verify product is associated with the new category
        $category = Category::where('name', 'new-category')->first();
        $product = Product::where('external_id', 1)->first();
        $this->assertEquals($category->id, $product->category_id);
    }

    public function test_reuses_existing_category(): void
    {
        // Create an existing category
        $existingCategory = Category::factory()->create(['name' => 'electronics']);

        $apiProducts = [
            [
                'id' => 1,
                'title' => 'Test Product 1',
                'price' => 99.99,
                'description' => 'Test description 1',
                'image' => 'https://example.com/image1.jpg',
                'category' => 'electronics',
                'rating' => [
                    'rate' => 4.5,
                    'count' => 100
                ]
            ],
            [
                'id' => 2,
                'title' => 'Test Product 2',
                'price' => 149.99,
                'description' => 'Test description 2',
                'image' => 'https://example.com/image2.jpg',
                'category' => 'electronics',
                'rating' => [
                    'rate' => 4.2,
                    'count' => 75
                ]
            ]
        ];

        Http::fake([
            '*' => Http::response($apiProducts, 200)
        ]);

        $this->syncProductsAction->execute();

        // Verify only one category exists with this name
        $this->assertEquals(1, Category::where('name', 'electronics')->count());

        // Verify both products are associated with the same category
        $category = Category::where('name', 'electronics')->first();
        $this->assertEquals($existingCategory->id, $category->id);

        $product1 = Product::where('external_id', 1)->first();
        $product2 = Product::where('external_id', 2)->first();

        $this->assertEquals($category->id, $product1->category_id);
        $this->assertEquals($category->id, $product2->category_id);
    }

    public function test_handles_products_with_missing_rating_data(): void
    {
        $apiProducts = [
            [
                'id' => 1,
                'title' => 'Product Without Rating',
                'price' => 99.99,
                'description' => 'Test description',
                'image' => 'https://example.com/image.jpg',
                'category' => 'electronics'
                // No rating data
            ],
            [
                'id' => 2,
                'title' => 'Product With Partial Rating',
                'price' => 149.99,
                'description' => 'Test description',
                'image' => 'https://example.com/image.jpg',
                'category' => 'electronics',
                'rating' => [
                    'rate' => 4.5
                    // Missing count
                ]
            ]
        ];

        Http::fake([
            '*' => Http::response($apiProducts, 200)
        ]);

        $this->syncProductsAction->execute();

        // Verify products were created with default rating values
        $product1 = Product::where('external_id', 1)->first();
        $product2 = Product::where('external_id', 2)->first();

        $this->assertEquals(0, $product1->rating);
        $this->assertEquals(0, $product1->reviews_count);

        $this->assertEquals(4.5, $product2->rating);
        $this->assertEquals(0, $product2->reviews_count);
    }

    public function test_handles_empty_products_response(): void
    {
        $apiProducts = [];

        Http::fake([
            '*' => Http::response($apiProducts, 200)
        ]);

        // Should not throw any exception
        $this->syncProductsAction->execute();

        // Verify no products were created
        $this->assertEquals(0, Product::count());
        $this->assertEquals(0, Category::count());
    }

    public function test_syncs_multiple_products_with_different_categories(): void
    {
        $apiProducts = [
            [
                'id' => 1,
                'title' => 'Electronics Product',
                'price' => 299.99,
                'description' => 'Electronics description',
                'image' => 'https://example.com/electronics.jpg',
                'category' => 'electronics',
                'rating' => ['rate' => 4.5, 'count' => 120]
            ],
            [
                'id' => 2,
                'title' => 'Clothing Product',
                'price' => 49.99,
                'description' => 'Clothing description',
                'image' => 'https://example.com/clothing.jpg',
                'category' => 'clothing',
                'rating' => ['rate' => 4.0, 'count' => 85]
            ],
            [
                'id' => 3,
                'title' => 'Book Product',
                'price' => 19.99,
                'description' => 'Book description',
                'image' => 'https://example.com/book.jpg',
                'category' => 'books',
                'rating' => ['rate' => 4.8, 'count' => 200]
            ]
        ];

        Http::fake([
            '*' => Http::response($apiProducts, 200)
        ]);

        $this->syncProductsAction->execute();

        // Verify all categories were created
        $this->assertEquals(3, Category::count());
        $this->assertDatabaseHas('categories', ['name' => 'electronics']);
        $this->assertDatabaseHas('categories', ['name' => 'clothing']);
        $this->assertDatabaseHas('categories', ['name' => 'books']);

        // Verify all products were created
        $this->assertEquals(3, Product::count());

        // Verify each product is associated with correct category
        $electronicsCategory = Category::where('name', 'electronics')->first();
        $clothingCategory = Category::where('name', 'clothing')->first();
        $booksCategory = Category::where('name', 'books')->first();

        $electronicsProduct = Product::where('external_id', 1)->first();
        $clothingProduct = Product::where('external_id', 2)->first();
        $bookProduct = Product::where('external_id', 3)->first();

        $this->assertEquals($electronicsCategory->id, $electronicsProduct->category_id);
        $this->assertEquals($clothingCategory->id, $clothingProduct->category_id);
        $this->assertEquals($booksCategory->id, $bookProduct->category_id);
    }

    public function test_handles_products_with_numeric_string_prices(): void
    {
        $apiProducts = [
            [
                'id' => 1,
                'title' => 'Test Product',
                'price' => '99.99', // String price
                'description' => 'Test description',
                'image' => 'https://example.com/image.jpg',
                'category' => 'electronics',
                'rating' => [
                    'rate' => '4.5', // String rating
                    'count' => '120' // String count
                ]
            ]
        ];

        Http::fake([
            '*' => Http::response($apiProducts, 200)
        ]);

        $this->syncProductsAction->execute();

        $product = Product::where('external_id', 1)->first();

        // Verify data types are correctly handled
        $this->assertEquals(99.99, $product->price);
        $this->assertEquals(4.5, $product->rating);
        $this->assertEquals(120, $product->reviews_count);
    }

    public function test_preserves_existing_products_not_in_api_response(): void
    {
        // Create existing products
        $category = Category::factory()->create(['name' => 'electronics']);
        $existingProduct1 = Product::factory()->create([
            'external_id' => 100,
            'title' => 'Existing Product 1',
            'category_id' => $category->id
        ]);
        $existingProduct2 = Product::factory()->create([
            'external_id' => 200,
            'title' => 'Existing Product 2',
            'category_id' => $category->id
        ]);

        // API response only contains new products
        $apiProducts = [
            [
                'id' => 1,
                'title' => 'New Product',
                'price' => 99.99,
                'description' => 'New description',
                'image' => 'https://example.com/image.jpg',
                'category' => 'electronics',
                'rating' => ['rate' => 4.5, 'count' => 100]
            ]
        ];

        Http::fake([
            '*' => Http::response($apiProducts, 200)
        ]);

        $this->syncProductsAction->execute();

        // Verify existing products are preserved
        $this->assertDatabaseHas('products', [
            'external_id' => 100,
            'title' => 'Existing Product 1'
        ]);
        $this->assertDatabaseHas('products', [
            'external_id' => 200,
            'title' => 'Existing Product 2'
        ]);

        // Verify new product was added
        $this->assertDatabaseHas('products', [
            'external_id' => 1,
            'title' => 'New Product'
        ]);

        // Total should be 3 products
        $this->assertEquals(3, Product::count());
    }
    public function test_handles_network_errors_gracefully(): void
    {
        Http::fake([
            '*' => Http::response(null, 500)
        ]);

        $this->expectException(\Exception::class);

        $this->expectExceptionMessage('Failed to fetch products from FakeStore API');

        $this->syncProductsAction->execute();
    }
}
