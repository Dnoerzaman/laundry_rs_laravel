<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemakaianChemical extends Model
{
    protected $fillable = ['chemical_id', 'tanggal', 'jumlah', 'petugas_id', 'keterangan'];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'decimal:2',
    ];

    public function chemical()
    {
        return $this->belongsTo(StokChemical::class, 'chemical_id');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}
