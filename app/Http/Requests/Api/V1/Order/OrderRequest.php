<?php

namespace App\Http\Requests\Api\V1\Order;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'filters' => [
                'sometimes',
                'array',
            ],
            'filters.status' => [
                'sometimes',
                Rule::enum(OrderStatus::class),
            ],
            'filters.min_price' => [
                'sometimes',
                'numeric',
                'min:0',
            ],
            'filters.max_price' => [
                'sometimes',
                'numeric',
                'min:0',
            ],
            'filters.date_from' => [
                'sometimes',
                'date',
            ],
            'filters.date_to' => [
                'sometimes',
                'date',
            ],
            'sort' => [
                'sometimes',
                'array',
            ],
            'sort.field' => [
                'sometimes',
                'string',
                Rule::in((new Order)->sortableFields()),
            ],
            'sort.direction' => [
                'sometimes',
                'string',
                Rule::in(['asc', 'desc']),
            ],
        ];
    }
}
