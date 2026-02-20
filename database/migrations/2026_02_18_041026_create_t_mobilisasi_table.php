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
        Schema::create('t_mobilisasi', function (Blueprint $table) {
            $table->id('id_mobilisasi');
            $table->foreignId('id_barang')->constrained('m_barang', 'id_barang')->cascadeOnDelete();
            $table->string('asal');
            $table->foreignId('id_penerima')->nullable()->constrained('m_karyawan', 'id_karyawan')->nullOnDelete();
            $table->string('lokasi_tujuan')->nullable();
            $table->foreignId('id_user_operator')->constrained('m_user', 'id_user')->cascadeOnDelete();
            $table->string('bukti_serah_terima')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_mobilisasi');
    }
};
