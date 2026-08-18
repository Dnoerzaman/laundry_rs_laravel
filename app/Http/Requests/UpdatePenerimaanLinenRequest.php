<?php

namespace App\Http\Requests;

use App\Models\ItemLinen;
use App\Models\PenerimaanLinen;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePenerimaanLinenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal' => [
                'required',
                'date',
            ],

            'jam' => [
                'required',
            ],

            'ruangan' => [
                'required',
                Rule::in(PenerimaanLinen::RUANGAN),
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.nama_item' => [
                'required',
                Rule::in(ItemLinen::NAMA_ITEM),
            ],

            'items.*.jumlah' => [
                'required',
                'integer',
                'min:1',
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

    public function messages(): array
    {
        return [
            'tanggal.required' => 'Tanggal wajib diisi.',
            'jam.required' => 'Jam wajib diisi.',
            'ruangan.required' => 'Ruangan wajib dipilih.',

            'items.required' => 'Minimal harus ada 1 item linen.',
            'items.min' => 'Minimal harus ada 1 item linen.',

            'items.*.nama_item.required' => 'Nama item wajib dipilih.',
            'items.*.jumlah.required' => 'Jumlah wajib diisi.',
            'items.*.jumlah.integer' => 'Jumlah harus berupa angka.',
            'items.*.jumlah.min' => 'Jumlah minimal 1.',
            'items.*.kondisi.required' => 'Kondisi wajib dipilih.',
        ];
    }
}