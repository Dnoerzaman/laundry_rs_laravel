<?php

// Namespace sesuai lokasi file
namespace App\Http\Requests;

// Import model untuk ambil daftar pilihan valid.
// PERHATIKAN: StokLinen TIDAK punya konstanta pilihannya sendiri —
// dia sengaja memakai daftar ruangan dari PenerimaanLinen dan daftar nama linen dari ItemLinen,
// supaya nilainya selalu konsisten dengan modul checklist (sesuai catatan di migration Tahap 1)
use App\Models\ItemLinen;
use App\Models\PenerimaanLinen;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Request untuk validasi form "Tambah Stok Linen Baru"
class StoreStokLinenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // sudah dijaga middleware 'auth' di route
    }

    public function rules(): array
    {
        return [
            // Rule::in -> wajib salah satu dari daftar ruangan yang valid
            'ruangan' => ['required', Rule::in(PenerimaanLinen::RUANGAN)],
            // Rule::unique dengan closure where() -> mereplikasi unique_together Django:
            // kombinasi (nama_linen, ruangan) tidak boleh dobel di tabel stok_linens.
            // where(fn ($query) => $query->where('ruangan', $this->ruangan)) artinya:
            // "cek unique nama_linen ini HANYA di antara baris yang ruangan-nya sama dengan input form"
            'nama_linen' => [
                'required',
                Rule::in(ItemLinen::NAMA_ITEM),
                Rule::unique('stok_linens')->where(fn ($query) => $query->where('ruangan', $this->ruangan)),
            ],
            'stok_akhir' => ['required', 'integer', 'min:0'], // stok berupa bilangan bulat, tidak boleh negatif
            'keterangan' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            // Pesan khusus untuk error 'unique' pada nama_linen, karena aturan uniknya gabungan 2 kolom
            'nama_linen.unique' => 'Kombinasi linen dan ruangan ini sudah terdaftar. Gunakan menu "Catat Transaksi" untuk mengubah stoknya.',
        ];
    }
}
