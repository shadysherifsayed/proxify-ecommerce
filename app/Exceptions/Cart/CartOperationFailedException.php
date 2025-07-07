<?php

namespace App\Exceptions\Cart;

use Exception;

class CartOperationFailedException extends Exception
{
    protected $message = 'Cart operation failed.';
    
    public function __construct(string $message = null, int $code = 0, Exception $previous = null)
    {
        parent::__construct($message ?? $this->message, $code, $previous);
    }
}
