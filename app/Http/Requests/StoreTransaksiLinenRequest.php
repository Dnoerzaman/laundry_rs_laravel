<?php

namespace App\Http\Requests;

use App\Models\TransaksiLinen;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Request untuk validasi form "Catat Transaksi Linen" (masuk/keluar)
class StoreTransaksiLinenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // exists:stok_linens,id -> stok_linen_id yang dikirim harus benar-benar ada di tabel stok_linens
            'stok_linen_id' => ['required', 'exists:stok_linens,id'],
            // array_keys(...) -> ambil hanya key dari const JENIS_TRANSAKSI (yaitu 'MASUK' dan 'KELUAR'),
            // karena const-nya berbentuk array asosiatif ['MASUK' => 'Linen Masuk', 'KELUAR' => 'Linen Keluar']
            'jenis_transaksi' => ['required', Rule::in(array_keys(TransaksiLinen::JENIS_TRANSAKSI))],
            'jumlah' => ['required', 'integer', 'min:1'], // bilangan bulat, minimal 1
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string'],
        ];

        // Catatan: sama seperti modul chemical, validasi "stok cukup atau tidak" untuk
        // transaksi KELUAR TIDAK dilakukan di sini, karena butuh data ter-update dari
        // database. Pengecekan itu dilakukan di dalam TransaksiLinenController, di dalam DB::transaction().
    }
}
