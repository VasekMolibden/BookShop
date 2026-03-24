<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class BookIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'genre' => ['sometimes', 'nullable', 'string', 'max:255'],
            'author' => ['sometimes', 'nullable', 'string', 'max:255'],
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'year' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:' . (date('Y') + 1)],
            'min_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'max_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'min_rating' => ['sometimes', 'nullable', 'numeric', 'min:1', 'max:5'],
            'in_stock' => ['sometimes', 'boolean'],
            'published_after' => ['sometimes', 'nullable', 'date'],
            'published_before' => ['sometimes', 'nullable', 'date'],
            'sort' => ['sometimes', 'string', 'in:price_asc,price_desc,rating_desc,reviews_desc,newest,popularity,title'],
        ];
    }
}
