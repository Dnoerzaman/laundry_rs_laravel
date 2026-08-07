<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Setara dengan aset_laundry.models.TransaksiAset
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_asets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aset_id')->constrained('asets')->restrictOnDelete();
            $table->enum('jenis_transaksi', ['PENAMBAHAN', 'PENGURANGAN']);
            $table->unsignedInteger('jumlah');
            $table->date('tanggal');
            $table->foreignId('petugas_id')->constrained('users')->restrictOnDelete();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_asets');
    }
};
