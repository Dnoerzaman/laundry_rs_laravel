<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Setara dengan suhu.models.SuhuRuangan
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suhu_ruangans', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->time('jam');
            $table->enum('ruangan', ['Ruang Linen (Bersih)', 'Area Pencucian', 'Lainnya'])
                ->default('Ruang Linen (Bersih)');
            $table->enum('waktu_ukur', ['Pagi', 'Siang', 'Sore'])->default('Pagi');
            $table->decimal('suhu', 4, 1);
            $table->integer('kelembaban');
            $table->foreignId('petugas_id')->constrained('users')->restrictOnDelete();
            $table->string('keterangan', 255)->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();

            $table->index(['tanggal', 'ruangan', 'waktu_ukur']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suhu_ruangans');
    }
};
