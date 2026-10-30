<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoretransactionsRequest extends FormRequest
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
            'category_id' => ['required', 'exists:categories,category_id'],
            'date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
            'details' => ['nullable', 'array'],
            'details.*.item_name' => ['required_with:details', 'string', 'max:255'],
            'details.*.quantity' => ['required_with:details', 'integer', 'min:1'],
            'details.*.price' => ['required_with:details', 'numeric', 'min:0'],
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'Kategori',
            'date' => 'Tanggal Transaksi',
            'notes' => 'Catatan',
            'details' => 'Detail Item',
            'details.*.item_name' => 'Nama Item',
            'details.*.quantity' => 'Jumlah',
            'details.*.price' => 'Harga',
        ];
    }
}
