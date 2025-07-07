<?php

namespace App\Clients\FakeStore;

class ProductClient extends AbstractClient
{
    private string $resource = 'products';

    /**
     * Get a list of products.
     *
     * @param  array  $query  Optional query parameters for filtering or pagination.
     * @return \Illuminate\Http\Client\Response The response containing the list of products.
     */
    public function list(): \Illuminate\Http\Client\Response
    {
        return $this->get($this->resource);
    }
}
