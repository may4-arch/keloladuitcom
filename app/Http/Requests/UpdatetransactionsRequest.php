<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdatetransactionsRequest extends FormRequest
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
            'type' => ['required', 'in:normal,hutang,piutang'],
            'date' => ['required', 'date'],
            'todate' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
            'details' => ['nullable', 'array'],
            'details.*.item_name' => ['required_with:details', 'string', 'max:255'],
            'details.*.quantity' => ['required_with:details', 'integer', 'min:1'],
            'details.*.price' => ['required_with:details', 'numeric', 'min:0'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $type = $this->input('type');
            $categoryId = $this->input('category_id');

            if ($type && $categoryId) {
                $category = Category::find($categoryId);
                if ($category) {
                    if ($type === 'hutang' && (int) $category->type !== 2) {
                        $validator->errors()->add('category_id', 'Untuk transaksi hutang, kategori harus berupa kategori pengeluaran.');
                    } elseif ($type === 'piutang' && (int) $category->type !== 1) {
                        $validator->errors()->add('category_id', 'Untuk transaksi piutang, kategori harus berupa kategori pemasukan/pendapatan.');
                    }
                }
            }
        });
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'Kategori',
            'type' => 'Tipe Transaksi',
            'date' => 'Tanggal Transaksi',
            'todate' => 'Tanggal Selesai / Jatuh Tempo',
            'notes' => 'Catatan',
            'details' => 'Detail Item',
            'details.*.item_name' => 'Nama Item',
            'details.*.quantity' => 'Jumlah',
            'details.*.price' => 'Harga',
        ];
    }
}
