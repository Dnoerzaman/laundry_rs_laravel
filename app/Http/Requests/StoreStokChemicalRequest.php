<?php

namespace App\Http\Requests;

use App\Models\StokChemical;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Request untuk validasi form "Tambah Chemical Baru" (master data)
class StoreStokChemicalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Rule::in -> harus salah satu dari daftar nama chemical yang valid
            // 'unique:stok_chemicals' -> setara kolom unique=True di Django, tidak boleh ada 2 baris chemical yang sama
            'nama_chemical' => ['required', Rule::in(StokChemical::NAMA_CHEMICAL), 'unique:stok_chemicals,nama_chemical'],
            'jumlah_stok' => ['required', 'numeric', 'min:0'], // boleh 0 (misal chemical baru yang stoknya belum diisi)
            'unit' => ['required', Rule::in(StokChemical::UNIT)],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_chemical.unique' => 'Chemical ini sudah terdaftar di master data. Gunakan menu "Tambah Stok Masuk" untuk menambah stoknya.',
        ];
    }
}
