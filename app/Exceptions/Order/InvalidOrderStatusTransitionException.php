<?php

namespace App\Exceptions\Order;

use Exception;

class InvalidOrderStatusTransitionException extends Exception
{
    protected $message = 'Invalid order status transition attempted.';
    
    public function __construct(string $message = null, int $code = 0, Exception $previous = null)
    {
        parent::__construct($message ?? $this->message, $code, $previous);
    }
}
