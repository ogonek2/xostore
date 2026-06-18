<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:48'],
            'sort' => ['sometimes', 'string', Rule::in(['newest', 'price_asc', 'price_desc', 'featured'])],
            'q' => ['sometimes', 'nullable', 'string', 'max:120'],
            'brands' => ['sometimes', 'array'],
            'brands.*' => ['integer', 'exists:brands,id'],
            'sizes' => ['sometimes', 'array'],
            'sizes.*' => ['string', 'max:64'],
            'colors' => ['sometimes', 'array'],
            'colors.*' => ['integer', 'exists:attribute_values,id'],
            'price_min' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'price_max' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'sale' => ['sometimes', 'boolean'],
            'new' => ['sometimes', 'boolean'],
        ];
    }

    public function filters(): array
    {
        return [
            'sort' => $this->input('sort', 'newest'),
            'q' => $this->filled('q') ? trim($this->input('q')) : null,
            'brands' => array_map('intval', $this->input('brands', [])),
            'sizes' => collect($this->input('sizes', []))
                ->map(fn (mixed $value) => is_numeric($value) ? (int) $value : trim((string) $value))
                ->filter(fn (mixed $value) => $value !== '' && $value !== 0)
                ->values()
                ->all(),
            'colors' => array_map('intval', $this->input('colors', [])),
            'price_min' => $this->filled('price_min') ? (float) $this->input('price_min') : null,
            'price_max' => $this->filled('price_max') ? (float) $this->input('price_max') : null,
            'sale' => $this->boolean('sale'),
            'new' => $this->boolean('new'),
        ];
    }

    public function perPage(): int
    {
        return (int) $this->input('per_page', config('shop.listing.per_page', 24));
    }
}
