<?php

// Namespace sesuai lokasi file
namespace App\Http\Requests;

// Import model untuk ambil daftar pilihan valid (ruangan & waktu ukur)
use App\Models\SuhuRuangan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Request untuk validasi form "Catat Suhu & Kelembaban Harian"
class StoreSuhuRequest extends FormRequest
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
            'tanggal' => ['required', 'date'],                              // wajib diisi, format tanggal valid
            'jam' => ['required'],                                            // wajib diisi (format HH:MM dari <input type="time">)
            'ruangan' => ['required', Rule::in(SuhuRuangan::RUANGAN)],           // wajib salah satu dari daftar ruangan yang valid
            // array_keys(...) -> ambil hanya key dari const WAKTU_UKUR ('Pagi', 'Siang', 'Sore'),
            // karena const-nya array asosiatif ['Pagi' => 'Pagi (Shift 1)', ...] -- yang disimpan ke DB adalah key-nya
            'waktu_ukur' => ['required', Rule::in(array_keys(SuhuRuangan::WAKTU_UKUR))],
            // 'numeric' izinkan angka desimal (misal 24.5), between(-50,100) sekadar batas akal sehat
            // (BUKAN validasi "standar 22-27°C" -- itu cuma indikator visual di tabel, bukan aturan wajib,
            // karena suhu di luar standar tetap harus BISA dicatat sebagai bukti adanya masalah)
            'suhu' => ['required', 'numeric', 'between:-50,100'],
            'kelembaban' => ['required', 'integer', 'between:0,100'], // persentase, wajar antara 0-100
            'keterangan' => ['nullable', 'string', 'max:255'],
        ];
    }
}
