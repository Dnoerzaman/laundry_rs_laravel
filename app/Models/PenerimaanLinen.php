<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenerimaanLinen extends Model
{
    public const RUANGAN = [
        'Kamar Bedah', 'Rawat Inap', 'Rawat Jalan', 'IGD',
        'Penunjang Medis', 'Fasilitas Umum', 'Ruang Istirahat Dokter', 'Lainnya',
    ];

    public $timestamps = false; // pakai kolom dibuat_pada manual (setara auto_now_add)

    protected $fillable = ['tanggal', 'jam', 'ruangan', 'petugas_id'];

    protected $casts = [
        'tanggal' => 'date',
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

    public function items()
    {
        return $this->hasMany(ItemLinen::class, 'penerimaan_id');
    }
}
