<?php

namespace App\Rules\Order;

use App\Enums\OrderStatus;
use App\Models\Order;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class OrderStatusTransitionRule implements ValidationRule
{
    public function __construct(private Order $order)
    {
        // Ensure the order is an instance of Order and has a valid status
        if (! $this->order instanceof Order || ! $this->order->status instanceof OrderStatus) {
            throw new \InvalidArgumentException('Invalid order or order status.');
        }
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTransl        atedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        if (! $this->order->status->canTransitionTo($value)) {
            $fail("The order cannot transition to the status '$value'.");
        }
    }
}
