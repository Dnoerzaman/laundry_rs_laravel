<?php

namespace App\Http\Requests;

use App\Models\BeratLinenHarian;
use App\Models\PenerimaanLinen; // dipakai untuk daftar ruangan (ruangan sama dengan PenerimaanLinen)
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Request untuk validasi form "Catat Berat Linen Kotor Harian"
class UpdateBeratLinenHarianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // sudah dijaga middleware 'auth' di route
    }

    public function rules(): array
    {
        return [
            'tanggal' => ['required', 'date'],                              // wajib tanggal valid
            'ruangan' => ['required', Rule::in(PenerimaanLinen::RUANGAN)],   // wajib salah satu ruangan yang valid
            'shift' => ['required', Rule::in(BeratLinenHarian::SHIFT)],       // wajib 'Shift 1' atau 'Shift 2'
            // 'numeric' izinkan angka desimal (misal 15.5 kg), min:0.1 supaya tidak boleh 0 atau negatif
            'total_berat' => ['required', 'numeric', 'min:0.1'],
        ];
    }
}
