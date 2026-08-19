<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemLinen extends Model
{
    public const NAMA_ITEM = [
        'Baju Perawat', 'Baju Dokter', 'Baju Pasien', 'Baju Cleaner',
        'Doek Besar', 'Doek Kecil', 'Handuk Kecil', 'Laken',
        'Sarung Bantal', 'Selimut', 'Handuk', 'Keset', 'Gown Scrub',
        'Mukena', 'Sejadah', 'Bedset', 'Gorden', 'Sneli Dokter',
    ];

    public const KONDISI = [
    'Baik',
    'Noda',
    'Rusak',
    ];

    public const KONDISI_WAJIB_KETERANGAN = [
        'Noda',
        'Rusak',
    ];

    public $timestamps = false;

    protected $fillable = ['penerimaan_id', 'nama_item', 'jumlah', 'kondisi', 'keterangan'];

    public function penerimaan()
    {
        return $this->belongsTo(PenerimaanLinen::class, 'penerimaan_id');
    }
}
