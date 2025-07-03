<?php

namespace App\Http\Requests\Api\V1\Product;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'price' => [
                'sometimes',
                'numeric',
                'min:1',
            ],
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'sometimes',
                'string',
                'max:1000',
            ],
            'category_id' => [
                'sometimes',
                'exists:categories,id',
            ],
        ];
    }
}
