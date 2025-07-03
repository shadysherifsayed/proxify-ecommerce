<?php

namespace App\Http\Requests\Api\V1\Order;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::enum(OrderStatus::class),
                // Ensure the status can transition from the current status
                function ($attribute, $value, $fail) {
                    $order = $this->route('order');
                    if (!$order->status->canTransitionTo($value)) {
                        $fail("The order cannot transition to the status '$value'.");
                    }
                },
            ],
        ];
    }
}
