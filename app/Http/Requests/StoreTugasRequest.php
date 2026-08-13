<?php

// Namespace sesuai lokasi file di app/Http/Requests
namespace App\Http\Requests;

// Import model Tugas untuk ambil daftar pilihan STATUS & TARGET_WAKTU yang valid
use App\Models\Tugas;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Request untuk validasi form "Tambah Rencana Kerja Baru"
class StoreTugasRequest extends FormRequest
{
    // authorize(): true -> semua user yang lolos middleware 'auth' boleh submit form ini
    public function authorize(): bool
    {
        return true;
    }

    // rules(): daftar aturan validasi untuk tiap field form
    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:200'],   // wajib diisi, maksimal 200 karakter (sesuai kolom di migration)
            'deskripsi' => ['nullable', 'string'],             // opsional, teks bebas panjang

            // Rule::in(Tugas::STATUS) -> wajib salah satu dari 3 pilihan status yang valid
            // (const Tugas::STATUS ada di model, isinya: 'Belum Dikerjakan', 'Sedang Dikerjakan', 'Selesai')
            'status' => ['required', Rule::in(Tugas::STATUS)],

            // 'nullable' -> boleh dikosongkan (artinya belum ada penanggung jawab ditentukan)
            // 'exists:users,id' -> KALAU diisi, id yang dikirim harus benar-benar ada di tabel users
            'penanggung_jawab_id' => ['nullable', 'exists:users,id'],

            // Rule::in(Tugas::TARGET_WAKTU) -> wajib salah satu dari 4 pilihan minggu yang valid
            'target_waktu' => ['required', Rule::in(Tugas::TARGET_WAKTU)],

            'periode' => ['nullable', 'string', 'max:50'], // opsional, misal isinya "Agustus 2026"
        ];
    }
}
