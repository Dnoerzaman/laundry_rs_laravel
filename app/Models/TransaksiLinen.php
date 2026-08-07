<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiLinen extends Model
{
    public const JENIS_TRANSAKSI = [
        'MASUK' => 'Linen Masuk',
        'KELUAR' => 'Linen Keluar',
    ];

    protected $fillable = ['stok_linen_id', 'jenis_transaksi', 'jumlah', 'tanggal', 'petugas_id', 'keterangan'];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function stokLinen()
    {
        return $this->belongsTo(StokLinen::class, 'stok_linen_id');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}
