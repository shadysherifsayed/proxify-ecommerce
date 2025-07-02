<?php

namespace App\Actions;

use App\Models\Category;

class SyncCategory
{
    /**
     * Execute the action.
     *
     * @param array $data Accepts an array of data to create or update a category
     * @return Category
     */
    public function execute(array $data): Category
    {
        return Category::firstOrCreate([
            'name' => $data['name']
        ]);
    }
}
