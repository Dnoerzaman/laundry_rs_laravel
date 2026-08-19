<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan field audit trail ke tabel penerimaan_linens.
     */
    public function up(): void
    {
        Schema::table('penerimaan_linens', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Audit User
            |--------------------------------------------------------------------------
            */

            $table->foreignId('created_by')
                ->nullable()
                ->after('petugas_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->after('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Laravel Timestamps
            |--------------------------------------------------------------------------
            */

            $table->timestamp('created_at')
                ->nullable()
                ->after('dibuat_pada');

            $table->timestamp('updated_at')
                ->nullable()
                ->after('created_at');

            /*
            |--------------------------------------------------------------------------
            | Index
            |--------------------------------------------------------------------------
            */

            $table->index('created_by');
            $table->index('updated_by');
        });
    }

    /**
     * Rollback migration.
     */
    public function down(): void
    {
        Schema::table('penerimaan_linens', function (Blueprint $table) {

            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);

            $table->dropIndex(['created_by']);
            $table->dropIndex(['updated_by']);

            $table->dropColumn([
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
            ]);
        });
    }
};