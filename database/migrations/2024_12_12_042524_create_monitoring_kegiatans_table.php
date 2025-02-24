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
        Schema::create('monitoring_kegiatans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_data_kegiatan');
            $table->foreign('id_data_kegiatan')->references('id')->on('data_kegiatans')->onDelete('cascade');
            $table->integer('kode_tim');
            $table->integer('kode_kegiatan');
            $table->integer('tahun_kegiatan');
            $table->string('bulan')->nullable();
            $table->string('triwulan')->nullable();
            $table->string('semester')->nullable();
            $table->date('waktu_mulai');
            $table->date('waktu_selesai');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoring_kegiatans');
    }
};
