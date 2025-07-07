<?php

namespace App\Exceptions\Checkout;

use Exception;

class CheckoutFailedException extends Exception
{
    protected $message = 'Checkout process failed.';
    
    public function __construct(string $message = null, int $code = 0, Exception $previous = null)
    {
        parent::__construct($message ?? $this->message, $code, $previous);
    }
}
