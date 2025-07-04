<?php

namespace App\Clients\FakeStore;

use App\Enums\HttpMethod;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

abstract class AbstractClient
{
    /**
     * The base URL for the FakeStore API.
     */
    private string $baseUrl;

    /**
     * AbstractClient constructor.
     *
     * Initialize the base URL for the FakeStore API client.
     *
     * @throws \InvalidArgumentException if the base URL is not set in the configuration.
     *
     * @see config/services.php for the 'fakestore.base_url' configuration.
     */
    public function __construct()
    {
        if (! config()->has('services.fakestore.base_url')) {
            throw new \InvalidArgumentException('The base URL for the FakeStore API is not set in the configuration.');
        }

        $this->baseUrl = config('services.fakestore.base_url');
    }

    /**
     * Get the full URL for the given endpoint.
     *
     * @param  string  $endpoint  The API endpoint to append to the base URL.
     * @return string The full URL for the API request.
     */
    private function getFullUrl(string $endpoint): string
    {
        return rtrim($this->baseUrl, '/').'/'.ltrim($endpoint, '/');
    }

    /**
     * Send an HTTP request to the FakeStore API.
     *
     * @param  HttpMethod  $method  The HTTP method (e.g., 'get', 'post').
     * @param  string  $endpoint  The API endpoint to send the request to.
     * @param  array  $data  Optional data to send with the request.
     * @return \Illuminate\Http\Client\Response The response from the API.
     */
    private function send(HttpMethod $method, string $endpoint, array $data = []): Response
    {
        $url = $this->getFullUrl($endpoint);

        return Http::send($method->value, $url, $data);
    }

    /**
     * Send a GET request to the specified endpoint.
     *
     * @param  string  $endpoint  The API endpoint to send the GET request to.
     * @param  array  $params  Optional query parameters to include in the request.
     * @return \Illuminate\Http\Client\Response The response from the API.
     */
    protected function get($endpoint, $params = [])
    {
        return $this->send(HttpMethod::GET, $endpoint, $params);
    }

    /**
     * Send a POST request to the specified endpoint.
     *
     * @param  string  $endpoint  The API endpoint to send the POST request to.
     * @param  array  $data  Optional data to include in the request body.
     * @return \Illuminate\Http\Client\Response The response from the API.
     */
    protected function post($endpoint, $data = [])
    {
        return $this->send(HttpMethod::POST, $endpoint, $data);
    }
}
