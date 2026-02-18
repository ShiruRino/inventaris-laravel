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
        Schema::create('t_kondisi', function (Blueprint $table) {
            $table->id('id_kondisi');
            $table->foreignId('id_barang')->constrained('m_barang', 'id_barang')->cascadeOnDelete();
            $table->foreignId('id_user_operator')->constrained('m_user', 'id_user')->cascadeOnDelete();
            $table->enum('status_kondisi', ['Baik', 'Rusak Ringan', 'Rusak Berat', 'Hilang']);
            $table->text('catatan')->nullable();
            $table->string('foto_kondisi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_kondisi');
    }
};
