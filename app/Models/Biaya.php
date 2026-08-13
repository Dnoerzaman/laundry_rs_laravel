<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Biaya extends Model
{
    // Daftar pilihan kategori yang valid -- dipakai untuk dropdown di Vue & validasi Rule::in() di FormRequest
    public const KATEGORI = ['Chemical', 'ATK', 'Perbaikan', 'Dan Lain-Lain'];

    // Daftar pilihan satuan yang valid
    public const SATUAN = ['Unit', 'Pcs', 'Stel', 'Roll', 'Pack'];

    // fillable -> kolom yang boleh diisi lewat create()/update() massal.
    // 'jumlah' SENGAJA TIDAK dimasukkan di sini -- nilainya selalu dihitung otomatis
    // lewat event 'saving' di bawah, tidak boleh diisi langsung dari form manapun
    protected $fillable = [
        'tanggal', 'kategori', 'nama_barang', 'qty', 'satuan', 'harga', 'pj_id', 'keterangan',
    ];

    // casts -> otomatis konversi tipe data saat baca/tulis dari & ke database
    protected $casts = [
        'tanggal' => 'date',        // jadi objek Carbon saat diakses di PHP
        'qty' => 'integer',
        'harga' => 'decimal:2',       // selalu 2 angka di belakang koma
        'jumlah' => 'decimal:2',
    ];

    // booted() -> tempat mendaftarkan event listener bawaan Eloquent untuk model ini
    protected static function booted()
    {
        // 'saving' terpicu SEBELUM data disimpan ke database, baik saat create() MAUPUN update().
        // Ini satu-satunya tempat logika "Jumlah = Qty x Harga" perlu ditulis --
        // otomatis berlaku di semua jalur penyimpanan, tidak perlu diulang manual di tiap Controller
        static::saving(function (Biaya $biaya) {
            // Kalkulasi ulang jumlah setiap kali baris ini disimpan, mengabaikan nilai 'jumlah'
            // apa pun yang mungkin terlanjur ada -- memastikan datanya SELALU konsisten dengan qty & harga saat ini
            $biaya->jumlah = $biaya->qty * $biaya->harga;
        });
    }

    // Relasi ke model User -- siapa petugas/PJ yang mencatat baris pengeluaran ini
    public function pj()
    {
        return $this->belongsTo(User::class, 'pj_id');
    }
}
