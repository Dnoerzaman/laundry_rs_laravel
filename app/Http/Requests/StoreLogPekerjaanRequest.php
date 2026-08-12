<?php

// Namespace sesuai lokasi file di app/Http/Requests
namespace App\Http\Requests;

// FormRequest: class dasar Laravel untuk menggabungkan validasi + otorisasi dalam satu tempat,
// terpisah dari Controller supaya Controller tetap ringkas dan validasi bisa dipakai ulang
use Illuminate\Foundation\Http\FormRequest;

// Request khusus untuk validasi form "Tambah Log Pekerjaan"
class StoreLogPekerjaanRequest extends FormRequest
{
    // authorize(): menentukan apakah user BOLEH mengakses request ini sama sekali.
    // return true -> semua user yang sudah lolos middleware 'auth' di route boleh submit form ini
    // (tidak ada pembatasan tambahan berdasarkan role, karena role belum diimplementasikan)
    public function authorize(): bool
    {
        return true;
    }

    // rules(): daftar aturan validasi untuk tiap field yang dikirim dari form.
    // Kalau ada field yang tidak lolos, Laravel otomatis redirect balik ke form
    // dengan pesan error yang bisa dibaca lewat form.errors di Vue (lewat Inertia)
    public function rules(): array
    {
        return [
            // 'required' -> wajib diisi, tidak boleh kosong
            // 'date' -> harus berupa format tanggal yang valid (misal '2026-08-12')
            'tanggal' => ['required', 'date'],

            // 'required' -> wajib diisi
            // 'string' -> harus berupa teks (bukan angka/array/dsb)
            // TIDAK ada max length di sini, karena kolom di database bertipe TEXT (bebas panjang),
            // sengaja mengikuti field Django yang juga tidak dibatasi panjangnya
            'keterangan' => ['required', 'string'],
        ];
    }
}
