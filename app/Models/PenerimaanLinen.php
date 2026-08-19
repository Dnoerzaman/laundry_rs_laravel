<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenerimaanLinen extends Model
{
    public const RUANGAN = [
        'Kamar Bedah',
        'Rawat Inap',
        'Rawat Jalan',
        'IGD',
        'Penunjang Medis',
        'Fasilitas Umum',
        'Ruang Istirahat Dokter',
        'Lainnya',
    ];

    /*
    |--------------------------------------------------------------------------
    | Laravel Timestamps
    |--------------------------------------------------------------------------
    |
    | Sekarang kita menggunakan created_at dan updated_at
    | sebagai audit timestamp.
    |
    */

    public $timestamps = true;

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'tanggal',
        'jam',
        'ruangan',
        'petugas_id',
        'created_by',
        'updated_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'tanggal' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'dibuat_pada' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Petugas lama.
     *
     * Dipertahankan agar modul yang sudah ada tidak rusak.
     */
    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    /**
     * User yang membuat data.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * User yang terakhir mengubah data.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Detail item linen.
     */
    public function items()
    {
        return $this->hasMany(ItemLinen::class, 'penerimaan_id');
    }
}