<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Setara dengan checklist.models.ItemLinen
// PilihanItem dipakai bersama oleh: ItemLinen, StokLinen
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_linens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penerimaan_id')->constrained('penerimaan_linens')->cascadeOnDelete();
            $table->enum('nama_item', [
                'Baju Perawat', 'Baju Dokter', 'Baju Pasien', 'Baju Cleaner',
                'Doek Besar', 'Doek Kecil', 'Handuk Kecil', 'Laken',
                'Sarung Bantal', 'Selimut', 'Handuk', 'Keset', 'Gown Scrub',
                'Mukena', 'Sejadah', 'Bedset', 'Gorden', 'Sneli Dokter',
            ]);
            $table->unsignedInteger('jumlah')->default(1);
            $table->enum('kondisi', ['Baik', 'Noda', 'Rusak'])->default('Baik');
            $table->string('keterangan', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_linens');
    }
};
