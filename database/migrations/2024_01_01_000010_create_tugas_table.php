<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Setara dengan schedule.models.Tugas
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tugas', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 200);
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['Belum Dikerjakan', 'Sedang Dikerjakan', 'Selesai'])
                ->default('Belum Dikerjakan');
            $table->foreignId('penanggung_jawab_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->enum('target_waktu', ['Minggu ke-1', 'Minggu ke-2', 'Minggu ke-3', 'Minggu ke-4'])
                ->default('Minggu ke-1');
            $table->string('periode', 50)->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tugas');
    }
};
