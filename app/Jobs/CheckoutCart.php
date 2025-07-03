<?php

namespace App\Jobs;

use App\Models\Cart;
use App\Actions\CheckoutCartAction;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class CheckoutCart implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private Cart $cart)
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
