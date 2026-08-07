<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Setara dengan chemical.models.PenerimaanChemical
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penerimaan_chemicals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chemical_id')->constrained('stok_chemicals')->restrictOnDelete();
            $table->decimal('jumlah', 10, 2);
            $table->date('tanggal');
            $table->foreignId('petugas_id')->constrained('users')->restrictOnDelete();
            $table->text('keterangan')->nullable(); // contoh: No. Faktur
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penerimaan_chemicals');
    }
};
