<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class StoreBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('slug') && $this->input('slug') === '') {
            $this->merge(['slug' => null]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, Unique|string>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:500'],
            'slug' => ['nullable', 'string', 'max:500', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:books,slug'],
            'author_id' => ['required', 'integer', 'exists:authors,id'],
            'description' => ['nullable', 'string', 'max:20000'],
            'price' => ['required', 'numeric', 'min:0'],
            'isbn' => ['nullable', 'string', 'max:32', Rule::unique('books', 'isbn')],
            'stock' => ['required', 'integer', 'min:0'],
            'published_at' => ['nullable', 'date'],
            'genre_ids' => ['required', 'array', 'min:1'],
            'genre_ids.*' => ['integer', 'exists:genres,id'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validationData(): array
    {
        $data = parent::validationData();

        if (isset($data['isbn']) && $data['isbn'] === '') {
            $data['isbn'] = null;
        }

        return $data;
    }
}
