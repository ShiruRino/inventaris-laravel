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
        Schema::create('m_barang', function (Blueprint $table) {
            $table->id('id_barang');
            $table->foreignId('id_kontrak')->constrained('m_kontrak', 'id_kontrak')->cascadeOnDelete();
            $table->string('kode_barcode')->unique();
            $table->string('nama_barang');
            $table->text('spesifikasi');
            $table->integer('jumlah_barang');
            $table->string('lokasi_fisik')->nullable();
            $table->foreignId('id_karyawan_pemegang')->nullable()->constrained('m_karyawan', "id_karyawan")->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_barang');
    }
};
