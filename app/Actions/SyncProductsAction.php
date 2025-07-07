<?php

namespace App\Actions;

use App\Clients\FakeStore\ProductClient;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class SyncProductsAction
{
    public function __construct(private ProductClient $productClient) {}

    public function execute(): void
    {
        try {
            $productsResponse = $this->productClient->list()->throw();

            $products = $productsResponse->json();

            foreach ($products as $product) {
                Product::updateOrCreate(
                    [
                        'external_id' => $product['id'],
                    ],
                    [
                        'title' => $product['title'],
                        'price' => $product['price'],
                        'description' => $product['description'],
                        'image' => $product['image'],
                        'rating' => $product['rating']['rate'] ?? 0,
                        'reviews_count' => $product['rating']['count'] ?? 0,
                        'category_id' => Category::firstOrCreate(['name' => $product['category']])->id,
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::error($e);
            throw new \Exception('Failed to fetch products from FakeStore API');
        }
    }
}
