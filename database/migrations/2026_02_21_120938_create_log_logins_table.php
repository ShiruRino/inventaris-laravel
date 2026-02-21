<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_log_login', function (Blueprint $table) {
            $table->id('id_log');
            $table->foreignId('id_user')->constrained('m_user', 'id_user')->cascadeOnDelete();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('waktu_login');
            $table->timestamp('waktu_logout')->nullable();
            $table->enum('status_sesi', ['Aktif', 'Selesai'])->default('Aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_log_login');
    }
};