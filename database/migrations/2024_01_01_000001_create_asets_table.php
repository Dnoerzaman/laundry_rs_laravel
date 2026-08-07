<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Setara dengan aset_laundry.models.Aset
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asets', function (Blueprint $table) {
            $table->id();
            $table->string('nama_barang', 200);
            $table->unsignedInteger('jumlah')->default(1);
            $table->enum('satuan', ['Unit', 'Pcs', 'Set', 'Buah'])->default('Unit');
            $table->string('merk', 100)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->unsignedSmallInteger('tahun_pengadaan')->nullable();
            $table->text('keterangan')->nullable();
            $table->date('tanggal_input');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asets');
    }
};
