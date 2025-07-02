<?php

namespace App\Actions;

use App\Clients\FakeStore\ProductClient;
use App\Models\Product;

class SyncProducts
{
    public function __construct(private ProductClient $productClient, private SyncCategory $syncCategory) {}

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
                    'external_id' => $product['id']
                ],
                [
                    'title' => $product['title'],
                    'price' => $product['price'],
                    'description' => $product['description'],
                    'image' => $product['image'],
                    'rating' => $product['rating']['rate'] ?? 0,
                    'reviews' => $product['rating']['count'] ?? 0,
                    'category_id' => $this->syncCategory->execute(['name' => $product['category'] ?? ''])?->id,
                ]
            );
        }
    }
}
