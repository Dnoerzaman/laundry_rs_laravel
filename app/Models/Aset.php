<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aset extends Model
{
    public const SATUAN = ['Unit', 'Pcs', 'Set', 'Buah'];

    protected $fillable = [
        'nama_barang', 'jumlah', 'satuan', 'merk', 'serial_number',
        'tahun_pengadaan', 'keterangan', 'tanggal_input',
    ];

    protected $casts = [
        'tanggal_input' => 'date',
    ];

    public function transaksi()
    {
        return $this->hasMany(TransaksiAset::class);
    }
}
