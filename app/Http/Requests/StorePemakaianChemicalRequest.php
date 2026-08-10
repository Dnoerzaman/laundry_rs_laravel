<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Request untuk validasi form "Catat Pemakaian Chemical"
class StorePemakaianChemicalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal' => ['required', 'date'],
            // exists:stok_chemicals,id -> chemical_id yang dikirim harus benar-benar ada di tabel stok_chemicals
            // (mencegah orang mengirim id chemical yang tidak valid lewat request manual)
            'chemical_id' => ['required', 'exists:stok_chemicals,id'],
            'jumlah' => ['required', 'numeric', 'min:0.01'], // minimal 0.01, tidak boleh 0/negatif
            'keterangan' => ['nullable', 'string'],
        ];

        // Catatan: validasi "stok cukup atau tidak" TIDAK dilakukan di sini,
        // karena butuh kalkulasi terhadap data terbaru di database (bisa berubah kapan saja).
        // Validasi itu dilakukan di dalam Controller, di dalam DB::transaction(),
        // supaya datanya benar-benar konsisten (lihat PemakaianChemicalController).
    }
}
