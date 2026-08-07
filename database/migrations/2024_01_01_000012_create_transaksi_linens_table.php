<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Setara dengan stok_linen.models.TransaksiLinen
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_linens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stok_linen_id')->constrained('stok_linens')->restrictOnDelete();
            $table->enum('jenis_transaksi', ['MASUK', 'KELUAR']);
            $table->unsignedInteger('jumlah');
            $table->date('tanggal');
            $table->foreignId('petugas_id')->constrained('users')->restrictOnDelete();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_linens');
    }
};
