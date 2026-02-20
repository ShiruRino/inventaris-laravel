<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        
        Schema::create('m_karyawan', function (Blueprint $table) {
            $table->id('id_karyawan');
            $table->string('nip')->unique();
            $table->string('nama_karyawan');
            $table->string('divisi');
            $table->string('jabatan');
            $table->string('kontak')->nullable();
            $table->timestamps();
        });
        Schema::create('m_user', function (Blueprint $table) {
            $table->id('id_user');
            $table->foreignId('id_karyawan')->unique()->constrained('m_karyawan', 'id_karyawan')->cascadeOnDelete();
            $table->string('username')->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'lapangan']);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_karyawan');
        Schema::dropIfExists('m_user');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
