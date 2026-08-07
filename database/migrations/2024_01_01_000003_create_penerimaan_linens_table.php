<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Setara dengan checklist.models.PenerimaanLinen
// PilihanRuangan dipakai bersama oleh: PenerimaanLinen, BeratLinenHarian, StokLinen
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penerimaan_linens', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->time('jam');
            $table->enum('ruangan', [
                'Kamar Bedah', 'Rawat Inap', 'Rawat Jalan', 'IGD',
                'Penunjang Medis', 'Fasilitas Umum', 'Ruang Istirahat Dokter', 'Lainnya',
            ]);
            $table->foreignId('petugas_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('dibuat_pada')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penerimaan_linens');
    }
};
