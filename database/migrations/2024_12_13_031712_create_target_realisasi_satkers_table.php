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
        Schema::create('target_realisasi_satkers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_monitoring_kegiatan');
            $table->integer('kode_satuan_kerja');
            $table->integer('target_satker');
            $table->timestamps();


            // Relasi dengan tabel penilaian
            $table->foreign('id_monitoring_kegiatan')->references('id')->on('monitoring_kegiatans')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('target_realisasi_satkers');
    }
};
