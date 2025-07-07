<?php

namespace App\Http\Requests\Api\V1\Product;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
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
            'filters.search' => [
                'sometimes',
                'string',
            ],
            'filters.categories' => [
                'sometimes',
                'array',
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
            'filters.min_rating' => [
                'sometimes',
                'numeric',
                'min:0',
            ],
            'sort' => [
                'sometimes',
                'array',
            ],
            'sort.field' => [
                'sometimes',
                'string',
                Rule::in((new Product)->sortableFields()),
            ],
            'sort.direction' => [
                'sometimes',
                'string',
                Rule::in(['asc', 'desc']),
            ],
            'cursor' => [
                'sometimes',
                'nullable',
                'string',
            ],
        ];
    }
}
