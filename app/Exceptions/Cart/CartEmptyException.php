<?php

namespace App\Exceptions\Cart;

use Exception;

class CartEmptyException extends Exception
{
    protected $message = 'Cannot perform operation on empty cart.';
    
    public function __construct(string $message = null, int $code = 0, Exception $previous = null)
    {
        parent::__construct($message ?? $this->message, $code, $previous);
    }
}
