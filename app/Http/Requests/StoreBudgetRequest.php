<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,category_id'],
            'maximal' => ['required', 'numeric', 'min:0'],
            'minimal' => ['nullable', 'numeric', 'min:0'],
            'priority' => ['nullable', 'boolean'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }

    /**
     * Custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'Nama Anggaran',
            'category_id' => 'Kategori',
            'maximal' => 'Batas Maksimal',
            'minimal' => 'Batas Minimal',
            'priority' => 'Prioritas',
            'start_date' => 'Tanggal Mulai',
            'end_date' => 'Tanggal Selesai',
        ];
    }

    /**
     * Custom validation error messages.
     */
    public function messages(): array
    {
        return [
            'end_date.after_or_equal' => 'Tanggal Selesai harus sama dengan atau setelah Tanggal Mulai.',
        ];
    }
}
