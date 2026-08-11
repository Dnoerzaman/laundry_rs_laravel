<?php

namespace App\Http\Requests;

use App\Models\TransaksiAset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Request untuk validasi form "Catat Transaksi Aset" (penambahan/pengurangan)
class StoreTransaksiAsetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // sudah dijaga middleware 'auth' di route
    }

    public function rules(): array
    {
        return [
            // exists:asets,id -> aset_id yang dikirim harus benar-benar ada di tabel asets
            'aset_id' => ['required', 'exists:asets,id'],
            // array_keys(...) -> ambil key dari const JENIS_TRANSAKSI ('PENAMBAHAN' dan 'PENGURANGAN'),
            // karena const-nya array asosiatif ['PENAMBAHAN' => 'Penambahan', 'PENGURANGAN' => 'Pengurangan']
            'jenis_transaksi' => ['required', Rule::in(array_keys(TransaksiAset::JENIS_TRANSAKSI))],
            'jumlah' => ['required', 'integer', 'min:1'], // bilangan bulat, minimal 1
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string'],
        ];

        // Catatan: sama seperti modul chemical & stok_linen, validasi "jumlah aset cukup
        // atau tidak untuk dikurangi" TIDAK dilakukan di sini, karena butuh data ter-update
        // dari database. Pengecekan itu dilakukan di dalam TransaksiAsetController, di dalam DB::transaction().
    }
}
