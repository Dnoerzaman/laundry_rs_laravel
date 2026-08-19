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

    public function messages(): array
                {
                    return [
                        'tanggal.required' =>
                            'Tanggal penerimaan wajib diisi.',

                        'tanggal.date' =>
                            'Format tanggal tidak valid.',

                        'tanggal.before_or_equal' =>
                            'Tanggal penerimaan tidak boleh melebihi hari ini.',

                        'jam.required' =>
                            'Jam penerimaan wajib diisi.',

                        'jam.date_format' =>
                            'Format jam harus HH:MM.',

                        'ruangan.required' =>
                            'Ruangan wajib dipilih.',

                        'items.required' =>
                            'Minimal harus ada 1 item linen.',

                        'items.min' =>
                            'Minimal harus ada 1 item linen.',

                        'items.*.nama_item.required' =>
                            'Nama item linen wajib dipilih.',

                        'items.*.nama_item.distinct' =>
                            'Item linen yang sama tidak boleh dimasukkan lebih dari satu kali.',

                        'items.*.jumlah.required' =>
                            'Jumlah linen wajib diisi.',

                        'items.*.jumlah.integer' =>
                            'Jumlah linen harus berupa angka.',

                        'items.*.jumlah.min' =>
                            'Jumlah linen minimal 1.',

                        'items.*.jumlah.max' =>
                            'Jumlah linen tidak boleh lebih dari 10.000.',

                        'items.*.kondisi.required' =>
                            'Kondisi linen wajib dipilih.',

                        'items.*.keterangan.max' =>
                            'Keterangan maksimal 255 karakter.',
                    ];
}
}