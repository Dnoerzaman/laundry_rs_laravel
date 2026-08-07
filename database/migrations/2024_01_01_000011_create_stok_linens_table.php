<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Setara dengan stok_linen.models.StokLinen
// nama_linen memakai pilihan yang sama dengan ItemLinen.PilihanItem
// ruangan memakai pilihan yang sama dengan PenerimaanLinen.PilihanRuangan
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_linens', function (Blueprint $table) {
            $table->id();
            $table->enum('nama_linen', [
                'Baju Perawat', 'Baju Dokter', 'Baju Pasien', 'Baju Cleaner',
                'Doek Besar', 'Doek Kecil', 'Handuk Kecil', 'Laken',
                'Sarung Bantal', 'Selimut', 'Handuk', 'Keset', 'Gown Scrub',
                'Mukena', 'Sejadah', 'Bedset', 'Gorden', 'Sneli Dokter',
            ]);
            $table->enum('ruangan', [
                'Kamar Bedah', 'Rawat Inap', 'Rawat Jalan', 'IGD',
                'Penunjang Medis', 'Fasilitas Umum', 'Ruang Istirahat Dokter', 'Lainnya',
            ]);
            $table->unsignedInteger('stok_akhir')->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamp('update_terakhir')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['nama_linen', 'ruangan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_linens');
    }
};
