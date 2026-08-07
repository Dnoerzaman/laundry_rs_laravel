<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    public const STATUS = ['Belum Dikerjakan', 'Sedang Dikerjakan', 'Selesai'];
    public const TARGET_WAKTU = ['Minggu ke-1', 'Minggu ke-2', 'Minggu ke-3', 'Minggu ke-4'];

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    protected $table = 'tugas';

    protected $fillable = [
        'judul', 'deskripsi', 'status', 'penanggung_jawab_id', 'target_waktu', 'periode',
    ];

    public function penanggungJawab()
    {
        return $this->belongsTo(User::class, 'penanggung_jawab_id');
    }
}
