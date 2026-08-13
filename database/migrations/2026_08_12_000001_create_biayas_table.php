<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration untuk tabel 'biayas' -- modul BARU yang tidak ada di aplikasi Django asli,
// dibuat berdasarkan template Excel "Pengeluaran Laundry" yang diberikan user
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biayas', function (Blueprint $table) {
            $table->id(); // primary key auto-increment, kolom 'id'

            $table->date('tanggal'); // tanggal pengeluaran dicatat

            // Kategori pengeluaran, dibatasi hanya 4 pilihan sesuai permintaan user
            $table->enum('kategori', ['Chemical', 'ATK', 'Perbaikan', 'Dan Lain-Lain']);

            $table->string('nama_barang', 200); // nama barang/jasa yang dibeli

            $table->unsignedInteger('qty'); // jumlah barang, tidak boleh negatif

            // Satuan barang, 5 pilihan sesuai permintaan user
            $table->enum('satuan', ['Unit', 'Pcs', 'Stel', 'Roll', 'Pack']);

            // decimal(12, 2) -> harga satuan, maksimal 12 digit total, 2 di belakang koma (misal 999999999.99)
            $table->decimal('harga', 15, 2)->unsigned();

            // decimal(14, 2) -> hasil kali qty x harga, dibuat lebih besar dari 'harga' karena bisa jadi jauh lebih besar
            // Kolom ini DIHITUNG OTOMATIS oleh model (lihat Biaya.php), bukan diisi manual dari form
            $table->decimal('jumlah', 14, 2)->unsigned();

            // pj_id -> siapa yang mencatat pengeluaran ini, terhubung ke tabel users
            // restrictOnDelete() -> user tidak bisa dihapus kalau masih punya catatan biaya (jaga integritas riwayat keuangan)
            $table->foreignId('pj_id')->constrained('users')->restrictOnDelete();

            $table->text('keterangan')->nullable(); // catatan tambahan, opsional (tidak ada di template asli, tapi berguna)

            // timestamps() -> otomatis bikin kolom created_at & updated_at.
            // Modul ini BARU (tidak ada precedent Django), jadi dipakai konvensi standar Laravel,
            // bukan meniru nama kolom Indonesia (dibuat_pada, dst) seperti modul-modul hasil migrasi Django
            $table->timestamps();

            // Index untuk mempercepat query filter per bulan (WHERE YEAR(tanggal)=... AND MONTH(tanggal)=...)
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biayas');
    }
};
