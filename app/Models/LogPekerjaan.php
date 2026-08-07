<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogPekerjaan extends Model
{
    public $timestamps = false;

    protected $fillable = ['tanggal', 'keterangan', 'pj_id'];

    protected $casts = [
        'tanggal' => 'date',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->dibuat_pada = now();
        });
    }

    public function pj()
    {
        return $this->belongsTo(User::class, 'pj_id');
    }
}
