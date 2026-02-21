<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_tugas', function (Blueprint $table) {
            $table->id('id_tugas');
            
            $table->foreignId('id_user_admin')->nullable()->constrained('m_user', 'id_user')->nullOnDelete();
            $table->foreignId('id_user_petugas')->nullable()->constrained('m_user', 'id_user')->nullOnDelete();
            
            $table->foreignId('id_barang')->nullable()->constrained('m_barang', 'id_barang')->nullOnDelete();
            
            $table->string('jenis_tugas');
            $table->text('deskripsi_tugas')->nullable();
            
            $table->dateTime('jadwal_mulai');
            $table->dateTime('jadwal_tenggat');
            
            $table->enum('status', ['Belum Dibaca', 'Sudah Dibaca', 'Proses', 'Selesai'])->default('Belum Dibaca');
            
            $table->json('foto_bukti_tugas')->nullable();
            $table->text('catatan_petugas')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_tugas');
    }
};