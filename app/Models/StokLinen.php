<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokLinen extends Model
{
    public $timestamps = false;

    protected $fillable = ['nama_linen', 'ruangan', 'stok_akhir', 'keterangan'];

    protected static function booted()
    {
        static::saving(function ($model) {
            $model->update_terakhir = now();
        });
    }

    public function transaksi()
    {
        return $this->hasMany(TransaksiLinen::class, 'stok_linen_id');
    }
}
