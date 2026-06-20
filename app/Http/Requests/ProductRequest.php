<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'title'          => ['required', 'string', 'min:2', 'max:255'],
            'description'    => ['required', 'string', 'min:10', 'max:65535'],
            'price'          => ['required', 'numeric', 'min:0', 'max:9999999.99', 'decimal:0,2'],
            'date_available' => ['required', 'date', 'date_format:Y-m-d'],
            'category'       => ['nullable', 'string', 'in:fruits,vegetables,dairy,bakery,other'],
            'stock'          => ['nullable', 'integer', 'min:0'],
            'image'          => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'],
            'currency'       => ['nullable', 'string', 'in:USD,INR'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'A product title is required.',
            'title.min' => 'The title must be at least 2 characters.',
            'description.required' => 'A product description is required.',
            'description.min' => 'The description must be at least 10 characters.',
            'price.required' => 'A price is required.',
            'price.numeric' => 'The price must be a valid number.',
            'price.min' => 'The price cannot be negative.',
            'price.decimal' => 'The price may have at most 2 decimal places.',
            'date_available.required' => 'An availability date is required.',
            'date_available.date' => 'The availability date must be a valid date.',
            'date_available.date_format' => 'The availability date must be in YYYY-MM-DD format.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('description')) {
            // Strip dangerous tags while preserving formatting tags used by the rich-text editor
            $this->merge([
                'description' => strip_tags(
                    $this->input('description'),
                    '<p><br><b><i><u><strong><em><ul><ol><li><h1><h2><h3><blockquote><a>'
                ),
            ]);
        }
    }
}
