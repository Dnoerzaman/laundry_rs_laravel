<?php

namespace App\Http\Requests;

use App\Models\ItemLinen;
use App\Models\PenerimaanLinen;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePenerimaanLinenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            /*
            |--------------------------------------------------------------------------
            | Data Utama Penerimaan
            |--------------------------------------------------------------------------
            */

            'tanggal' => [
                'required',
                'date',
                'before_or_equal:today',
            ],

            'jam' => [
                'required',
                'date_format:H:i',
            ],

            'ruangan' => [
                'required',
                Rule::in(PenerimaanLinen::RUANGAN),
            ],

            /*
            |--------------------------------------------------------------------------
            | Detail Linen
            |--------------------------------------------------------------------------
            */

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.nama_item' => [
                'required',
                Rule::in(ItemLinen::NAMA_ITEM),
                'distinct',
            ],

            'items.*.jumlah' => [
                'required',
                'integer',
                'min:1',
                'max:10000',
            ],

            'items.*.kondisi' => [
                'required',
                Rule::in(ItemLinen::KONDISI),
            ],

            'items.*.keterangan' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * Additional business validation.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->input('items', []) as $index => $item) {
                $kondisi = $item['kondisi'] ?? null;
                $keterangan = trim($item['keterangan'] ?? '');

                /*
                 * Kondisi Noda dan Rusak wajib memiliki keterangan.
                 */
                if (
                    in_array(
                        $kondisi,
                        ItemLinen::KONDISI_WAJIB_KETERANGAN,
                        true
                    )
                    && $keterangan === ''
                ) {
                    $validator->errors()->add(
                        "items.$index.keterangan",
                        'Keterangan wajib diisi untuk linen dengan kondisi Noda atau Rusak.'
                    );
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            /*
            |--------------------------------------------------------------------------
            | Tanggal
            |--------------------------------------------------------------------------
            */

            'tanggal.required' =>
                'Tanggal penerimaan wajib diisi.',

            'tanggal.date' =>
                'Format tanggal penerimaan tidak valid.',

            'tanggal.before_or_equal' =>
                'Tanggal penerimaan tidak boleh melebihi hari ini.',

            /*
            |--------------------------------------------------------------------------
            | Jam
            |--------------------------------------------------------------------------
            */

            'jam.required' =>
                'Jam penerimaan wajib diisi.',

            'jam.date_format' =>
                'Format jam penerimaan harus HH:MM.',

            /*
            |--------------------------------------------------------------------------
            | Ruangan
            |--------------------------------------------------------------------------
            */

            'ruangan.required' =>
                'Ruangan wajib dipilih.',

            'ruangan.in' =>
                'Ruangan yang dipilih tidak valid.',

            /*
            |--------------------------------------------------------------------------
            | Items
            |--------------------------------------------------------------------------
            */

            'items.required' =>
                'Data linen wajib diisi.',

            'items.array' =>
                'Format data linen tidak valid.',

            'items.min' =>
                'Minimal harus ada 1 item linen.',

            /*
            |--------------------------------------------------------------------------
            | Nama Item
            |--------------------------------------------------------------------------
            */

            'items.*.nama_item.required' =>
                'Nama item linen wajib dipilih.',

            'items.*.nama_item.in' =>
                'Item linen yang dipilih tidak valid.',

            'items.*.nama_item.distinct' =>
                'Item linen yang sama tidak boleh dimasukkan lebih dari satu kali.',

            /*
            |--------------------------------------------------------------------------
            | Jumlah
            |--------------------------------------------------------------------------
            */

            'items.*.jumlah.required' =>
                'Jumlah linen wajib diisi.',

            'items.*.jumlah.integer' =>
                'Jumlah linen harus berupa angka bulat.',

            'items.*.jumlah.min' =>
                'Jumlah linen minimal 1.',

            'items.*.jumlah.max' =>
                'Jumlah linen tidak boleh lebih dari 10.000.',

            /*
            |--------------------------------------------------------------------------
            | Kondisi
            |--------------------------------------------------------------------------
            */

            'items.*.kondisi.required' =>
                'Kondisi linen wajib dipilih.',

            'items.*.kondisi.in' =>
                'Kondisi linen yang dipilih tidak valid.',

            /*
            |--------------------------------------------------------------------------
            | Keterangan
            |--------------------------------------------------------------------------
            */

            'items.*.keterangan.string' =>
                'Keterangan harus berupa teks.',

            'items.*.keterangan.max' =>
                'Keterangan maksimal 255 karakter.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'tanggal' => 'tanggal penerimaan',
            'jam' => 'jam penerimaan',
            'ruangan' => 'ruangan',
            'items' => 'data linen',
            'items.*.nama_item' => 'nama item linen',
            'items.*.jumlah' => 'jumlah linen',
            'items.*.kondisi' => 'kondisi linen',
            'items.*.keterangan' => 'keterangan',
        ];
    }
}