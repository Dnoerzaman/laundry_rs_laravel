<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokChemical extends Model
{
    public const NAMA_CHEMICAL = ['Alkali', 'Emulsifier', 'Oxygen', 'Softener', 'Pelicin', 'Chemical Lainnya'];
    public const UNIT = ['Liter', 'Kg', 'Pcs'];

    public $timestamps = false;

    protected $fillable = ['nama_chemical', 'jumlah_stok', 'unit'];

    protected $casts = [
        'jumlah_stok' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->dibuat_pada = now();
            $model->update_terakhir = now();
        });
        static::updating(function ($model) {
            $model->update_terakhir = now();
        });
    }

    public function pemakaian()
    {
        return $this->hasMany(PemakaianChemical::class, 'chemical_id');
    }

    public function penerimaan()
    {
        return $this->hasMany(PenerimaanChemical::class, 'chemical_id');
    }
}
