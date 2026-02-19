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
        Schema::create('m_kontrak', function (Blueprint $table) {
            $table->id('id_kontrak');
            $table->string('no_kontrak')->unique();
            $table->year('tahun_kontrak');
            $table->string('nama_vendor');
            $table->string('pihak_pengada');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_kontrak');
    }
};
