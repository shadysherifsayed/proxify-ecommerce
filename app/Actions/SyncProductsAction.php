<?php

namespace App\Actions;

use App\Clients\FakeStore\ProductClient;
use App\Models\Category;
use App\Models\Product;

class SyncProductsAction
{
    public function __construct(private ProductClient $productClient) {}

    public function execute(): void
    {
        $productsResponse = $this->productClient->list();

        if ($productsResponse->failed()) {
            // Handle the error, e.g., log it or throw an exception
            throw new \Exception('Failed to fetch products from FakeStore API');
        }

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
    }
}
