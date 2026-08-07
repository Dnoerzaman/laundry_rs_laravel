<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Setara dengan chemical.models.StokChemical
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_chemicals', function (Blueprint $table) {
            $table->id();
            $table->enum('nama_chemical', [
                'Alkali', 'Emulsifier', 'Oxygen', 'Softener', 'Pelicin', 'Chemical Lainnya',
            ])->unique();
            $table->decimal('jumlah_stok', 10, 2)->default(0);
            $table->enum('unit', ['Liter', 'Kg', 'Pcs']);
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('update_terakhir')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_chemicals');
    }
};
