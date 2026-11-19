<?php

namespace App\Http\Requests;

use App\Models\Currencies;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCurrenciesRequest extends FormRequest
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
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper(trim((string) $this->input('code'))),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $currencyParam = $this->route('currency') ?? $this->route('currencies');
        $currencyId = $currencyParam instanceof Currencies ? $currencyParam->id : $currencyParam;

        return [
            'code' => ['required', 'string', 'size:3', 'alpha', Rule::unique('currencies', 'code')->ignore($currencyId)],
            'name' => ['required', 'string', 'max:50'],
            'symbol' => ['required', 'string', 'max:10'],
            'symbol_position' => ['required', 'in:prefix,suffix'],
            'decimal_places' => ['required', 'integer', 'min:0', 'max:6'],
            'thousand_separator' => ['required', 'string', 'size:1'],
            'decimal_separator' => ['required', 'string', 'size:1', 'different:thousand_separator'],
            'exchange_rate' => ['required', 'numeric', 'gt:0'],
            'is_base' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'code' => 'Kode Mata Uang',
            'name' => 'Nama Mata Uang',
            'symbol' => 'Simbol',
            'symbol_position' => 'Posisi Simbol',
            'decimal_places' => 'Jumlah Desimal',
            'thousand_separator' => 'Pemisah Ribuan',
            'decimal_separator' => 'Pemisah Desimal',
            'exchange_rate' => 'Nilai Tukar (Kurs)',
            'is_base' => 'Mata Uang Utama',
            'is_active' => 'Status Aktif',
        ];
    }

    /**
     * Custom validation error messages.
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Kode Mata Uang wajib diisi.',
            'code.size' => 'Kode Mata Uang harus terdiri dari 3 huruf (contoh: IDR, USD).',
            'code.alpha' => 'Kode Mata Uang hanya boleh berisi huruf.',
            'code.unique' => 'Kode Mata Uang tersebut sudah terdaftar.',
            'decimal_separator.different' => 'Pemisah Desimal tidak boleh sama dengan Pemisah Ribuan.',
            'exchange_rate.gt' => 'Nilai Tukar (Kurs) harus lebih besar dari 0.',
        ];
    }
}
