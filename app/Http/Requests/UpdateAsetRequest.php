<?php

// Namespace sesuai lokasi file
namespace App\Http\Requests;

// Import model untuk ambil daftar pilihan satuan yang valid
use App\Models\Aset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Request untuk validasi form "Ubah Data Aset"
class UpdateAsetRequest extends FormRequest
{
    // authorize(): true -> semua user yang lolos middleware 'auth' boleh submit form ini
    public function authorize(): bool
    {
        return true;
    }

    // rules(): daftar aturan validasi untuk tiap field
    public function rules(): array
    {
        return [
            'nama_barang' => ['required', 'string', 'max:200'],       // wajib diisi, teks, maksimal 200 karakter
            'jumlah' => ['required', 'integer', 'min:0'],                // bilangan bulat, tidak boleh negatif
            'satuan' => ['required', Rule::in(Aset::SATUAN)],             // wajib salah satu dari daftar satuan yang valid (Unit/Pcs/Set/Buah)
            'merk' => ['nullable', 'string', 'max:100'],                    // opsional
            'serial_number' => ['nullable', 'string', 'max:100'],           // opsional
            // 'digits:4' -> kalau diisi, wajib tepat 4 digit angka (format tahun)
            'tahun_pengadaan' => ['nullable', 'integer', 'digits:4'],
            'keterangan' => ['nullable', 'string'],                          // opsional, teks bebas
            'tanggal_input' => ['required', 'date'],                          // wajib diisi, format tanggal valid
        ];
    }
}
