<?php

namespace App\Http\Requests\Api\V1\Order;

use App\Enums\OrderStatus;
use App\Rules\Order\OrderStatusTransitionRule;
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
                new OrderStatusTransitionRule($this->order),
            ],
        ];
    }
}
