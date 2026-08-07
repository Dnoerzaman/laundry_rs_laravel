<?php

// Namespace sesuai lokasi file di app/Http/Requests
namespace App\Http\Requests;

// Model dipakai untuk ambil daftar pilihan valid (Rule::in), bukan untuk query data
use App\Models\ItemLinen;
use App\Models\PenerimaanLinen;
// FormRequest: class dasar Laravel untuk validasi + otorisasi sebuah request
use Illuminate\Foundation\Http\FormRequest;
// Rule: helper untuk aturan validasi yang lebih kompleks, seperti "harus salah satu dari daftar ini"
use Illuminate\Validation\Rule;

// Request khusus untuk validasi form "Checklist Penerimaan Linen" (header + daftar item sekaligus)
class StorePenerimaanLinenRequest extends FormRequest
{
    // authorize(): true berarti semua user yang lolos middleware 'auth' boleh submit form ini
    public function authorize(): bool
    {
        return true;
    }

    // rules(): daftar aturan validasi untuk tiap field yang dikirim dari form
    public function rules(): array
    {
        return [
            // --- Validasi data header (setara PenerimaanLinenForm di Django) ---
            'tanggal' => ['required', 'date'],                         // wajib diisi, harus format tanggal valid
            'jam' => ['required'],                                       // wajib diisi (format HH:MM dari <input type="time">)
            'ruangan' => ['required', Rule::in(PenerimaanLinen::RUANGAN)], // wajib salah satu dari daftar ruangan yang valid

            // --- Validasi array item linen (setara ItemLinenFormSet di Django) ---
            // 'items' harus berupa array, dan minimal ada 1 baris (setara min_num=1, validate_min=True)
            'items' => ['required', 'array', 'min:1'],

            // Aturan ini berlaku untuk SETIAP elemen di dalam array items (items.0.nama_item, items.1.nama_item, dst)
            'items.*.nama_item' => ['required', Rule::in(ItemLinen::NAMA_ITEM)], // wajib salah satu dari daftar nama item
            'items.*.jumlah' => ['required', 'integer', 'min:1'],                 // wajib angka bulat, minimal 1
            'items.*.kondisi' => ['required', Rule::in(ItemLinen::KONDISI)],       // wajib salah satu: Baik/Noda/Rusak
            'items.*.keterangan' => ['nullable', 'string', 'max:255'],              // opsional, teks bebas
        ];
    }

    // messages(): pesan error custom berbahasa Indonesia (opsional tapi bikin UX lebih enak)
    public function messages(): array
    {
        return [
            'items.required' => 'Minimal harus ada 1 item linen yang dicatat.',
            'items.min' => 'Minimal harus ada 1 item linen yang dicatat.',
            'items.*.nama_item.required' => 'Nama item wajib dipilih.',
            'items.*.jumlah.required' => 'Jumlah wajib diisi.',
            'items.*.jumlah.min' => 'Jumlah minimal 1.',
        ];
    }
}
