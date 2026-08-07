<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeratLinenHarian extends Model
{
    public const SHIFT = ['Shift 1', 'Shift 2'];

    public $timestamps = false;

    protected $fillable = ['tanggal', 'ruangan', 'shift', 'total_berat', 'petugas_id'];

    protected $casts = [
        'tanggal' => 'date',
        'total_berat' => 'decimal:2',
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
