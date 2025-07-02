<?php

use App\Http\Controllers\ProductController;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Pagination\CursorPaginator;
use Mockery;

describe('ProductController Unit Tests', function () {

    beforeEach(function () {
        $this->productService = Mockery::mock(ProductService::class);
        $this->controller = new ProductController($this->productService);
        $this->request = Mockery::mock(Request::class);
    });

    afterEach(function () {
        Mockery::close();
    });

    describe('constructor', function () {
        it('can be instantiated with ProductService dependency', function () {
            // Arrange & Act
            $productService = Mockery::mock(ProductService::class);
            $controller = new ProductController($productService);

            // Assert
            expect($controller)->toBeInstanceOf(ProductController::class);
        });
    });
    describe('index method', function () {
        it('calls ProductService listProducts method', function () {
            // Arrange
            $expectedResult = Mockery::mock(CursorPaginator::class);

            $this->productService
                ->shouldReceive('listProducts')
                ->once()
                ->withNoArgs()
                ->andReturn($expectedResult);

            // Act
            $result = $this->controller->index($this->request);

            // Assert
            expect($result)->toBe($expectedResult);
        });

        it('returns the result from ProductService without modification', function () {
            // Arrange
            $mockPaginator = Mockery::mock(CursorPaginator::class);
            $mockPaginator->shouldReceive('toArray')->andReturn([
                'data' => [
                    ['id' => 1, 'title' => 'Test Product', 'price' => 99.99]
                ]
            ]);

            $this->productService
                ->shouldReceive('listProducts')
                ->once()
                ->withNoArgs()
                ->andReturn($mockPaginator);

            // Act
            $result = $this->controller->index($this->request);

            // Assert
            expect($result)->toBe($mockPaginator);
        });

        it('ignores request parameters as service does not use them', function () {
            // Arrange
            $request = new Request(['search' => 'test', 'category' => 'electronics']);
            $expectedResult = Mockery::mock(CursorPaginator::class);

            $this->productService
                ->shouldReceive('listProducts')
                ->once()
                ->withNoArgs()
                ->andReturn($expectedResult);

            // Act
            $result = $this->controller->index($request);

            // Assert
            expect($result)->toBe($expectedResult);
        });

        it('handles empty request gracefully', function () {
            // Arrange
            $emptyRequest = new Request();
            $expectedResult = Mockery::mock(CursorPaginator::class);

            $this->productService
                ->shouldReceive('listProducts')
                ->once()
                ->withNoArgs()
                ->andReturn($expectedResult);

            // Act
            $result = $this->controller->index($emptyRequest);

            // Assert
            expect($result)->toBe($expectedResult);
        });
    });

    describe('dependency injection', function () {
        it('uses constructor property promotion correctly', function () {
            // Arrange & Act
            $productService = Mockery::mock(ProductService::class);
            $controller = new ProductController($productService);

            // Use reflection to verify the private property is set
            $reflection = new ReflectionClass($controller);
            $property = $reflection->getProperty('productService');
            $property->setAccessible(true);

            // Assert
            expect($property->getValue($controller))->toBe($productService);
        });
    });
});
