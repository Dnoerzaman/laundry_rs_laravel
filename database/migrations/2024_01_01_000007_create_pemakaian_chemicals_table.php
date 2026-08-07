<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Setara dengan chemical.models.PemakaianChemical
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemakaian_chemicals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chemical_id')->constrained('stok_chemicals')->restrictOnDelete();
            $table->date('tanggal');
            $table->decimal('jumlah', 10, 2);
            $table->foreignId('petugas_id')->constrained('users')->restrictOnDelete();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemakaian_chemicals');
    }
};
