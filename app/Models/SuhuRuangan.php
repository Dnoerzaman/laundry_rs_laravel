<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuhuRuangan extends Model
{
    public const RUANGAN = ['Ruang Linen (Bersih)', 'Area Pencucian', 'Lainnya'];
    public const WAKTU_UKUR = [
        'Pagi' => 'Pagi (Shift 1)',
        'Siang' => 'Siang (Shift 2)',
        'Sore' => 'Sore (Shift 3)',
    ];

    public $timestamps = false;

    protected $fillable = [
        'tanggal', 'jam', 'ruangan', 'waktu_ukur', 'suhu', 'kelembaban', 'petugas_id', 'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'suhu' => 'decimal:1',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->dibuat_pada = now();
        });
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}
