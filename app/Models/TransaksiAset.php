<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiAset extends Model
{
    public const JENIS_TRANSAKSI = [
        'PENAMBAHAN' => 'Penambahan',
        'PENGURANGAN' => 'Pengurangan',
    ];

    public $timestamps = true;

    protected $fillable = [
        'aset_id', 'jenis_transaksi', 'jumlah', 'tanggal', 'petugas_id', 'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function aset()
    {
        return $this->belongsTo(Aset::class);
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function getJenisTransaksiLabelAttribute(): string
    {
        return self::JENIS_TRANSAKSI[$this->jenis_transaksi] ?? $this->jenis_transaksi;
    }
}
