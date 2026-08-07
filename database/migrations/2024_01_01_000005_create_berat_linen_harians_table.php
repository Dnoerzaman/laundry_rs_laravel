<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Setara dengan checklist.models.BeratLinenHarian
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berat_linen_harians', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->enum('ruangan', [
                'Kamar Bedah', 'Rawat Inap', 'Rawat Jalan', 'IGD',
                'Penunjang Medis', 'Fasilitas Umum', 'Ruang Istirahat Dokter', 'Lainnya',
            ]);
            $table->enum('shift', ['Shift 1', 'Shift 2'])->default('Shift 1');
            $table->decimal('total_berat', 7, 2);
            $table->foreignId('petugas_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('dibuat_pada')->useCurrent();

            $table->index(['tanggal', 'ruangan', 'shift']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berat_linen_harians');
    }
};
