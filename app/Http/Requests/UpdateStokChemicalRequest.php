<?php

namespace App\Http\Requests;

use App\Models\StokChemical;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Request untuk validasi form "Ubah Stok Chemical"
class UpdateStokChemicalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ignore($this->route('stokChemical')) -> abaikan baris ini sendiri saat cek unique,
            // supaya tidak dianggap "sudah dipakai" oleh dirinya sendiri saat diedit
            'nama_chemical' => [
                'required',
                Rule::in(StokChemical::NAMA_CHEMICAL),
                Rule::unique('stok_chemicals', 'nama_chemical')->ignore($this->route('stokChemical')),
            ],
            'jumlah_stok' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', Rule::in(StokChemical::UNIT)],
        ];
    }
}
