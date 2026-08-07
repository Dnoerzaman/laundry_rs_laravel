<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Setara dengan log_pekerjaan.models.LogPekerjaan
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_pekerjaans', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->text('keterangan');
            $table->foreignId('pj_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('dibuat_pada')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_pekerjaans');
    }
};
