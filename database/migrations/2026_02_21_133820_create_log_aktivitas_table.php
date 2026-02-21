<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_log_aktivitas', function (Blueprint $table) {
            $table->id('id_log');
            $table->foreignId('id_user')->nullable()->constrained('m_user', 'id_user')->nullOnDelete();
            $table->string('modul');
            $table->string('aksi');
            $table->text('keterangan');
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_log_aktivitas');
    }
};