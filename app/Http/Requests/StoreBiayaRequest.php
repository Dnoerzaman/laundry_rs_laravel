<?php

namespace App\Http\Requests;

use App\Models\Biaya;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Request untuk validasi form "Catat Pengeluaran Baru"
class StoreBiayaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // sudah dijaga middleware 'auth' di route
    }

    public function rules(): array
    {
        return [
            'tanggal' => ['required', 'date'],
            'kategori' => ['required', Rule::in(Biaya::KATEGORI)],       // wajib salah satu dari 4 kategori yang valid
            'nama_barang' => ['required', 'string', 'max:200'],
            'qty' => ['required', 'integer', 'min:1'],                    // bilangan bulat, minimal 1 (tidak masuk akal beli 0 barang)
            'satuan' => ['required', Rule::in(Biaya::SATUAN)],              // wajib salah satu dari 5 satuan yang valid
            'harga' => ['required', 'numeric', 'min:0.01'],                  // harga satuan, harus lebih dari 0
            'keterangan' => ['nullable', 'string'],

            // PERHATIKAN: TIDAK ADA rule untuk 'jumlah' di sini -- field itu memang
            // sengaja tidak diterima dari form/request sama sekali (lihat $fillable di model Biaya).
            // Nilainya selalu dihitung otomatis oleh model, bukan tugas validasi untuk mengurusnya
        ];
    }
}
