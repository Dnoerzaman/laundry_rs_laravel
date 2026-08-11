<?php

namespace App\Http\Requests;

use App\Models\ItemLinen;
use App\Models\PenerimaanLinen;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Request untuk validasi form "Ubah Stok Linen"
class UpdateStokLinenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ruangan' => ['required', Rule::in(PenerimaanLinen::RUANGAN)],
            'nama_linen' => [
                'required',
                Rule::in(ItemLinen::NAMA_ITEM),
                // ->where(...) tetap cek kombinasi ruangan yang SEDANG diketik di form (bukan yang lama)
                // ->ignore($this->route('stokLinen')) -> kecualikan baris ini sendiri dari pengecekan unique
                Rule::unique('stok_linens')
                    ->where(fn ($query) => $query->where('ruangan', $this->ruangan))
                    ->ignore($this->route('stokLinen')),
            ],
            'stok_akhir' => ['required', 'integer', 'min:0'],
            'keterangan' => ['nullable', 'string'],
        ];
    }
}
