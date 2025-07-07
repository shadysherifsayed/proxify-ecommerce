<?php

namespace App\Jobs;

use App\Actions\CheckoutCartAction;
use App\Models\Cart;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckoutCart implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Cart $cart)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(CheckoutCartAction $checkoutCartAction): void
    {
        $checkoutCartAction->execute($this->cart);
    }
}
