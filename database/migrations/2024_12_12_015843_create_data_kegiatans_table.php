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
        Schema::create('data_kegiatans', function (Blueprint $table) {
            $table->id();
            $table->integer('id_tim_kerja');
            $table->string('nama_kegiatan');
            $table->integer('kode_kegiatan');
            $table->integer('kode_tim');
            $table->string('objek_kegiatan');
            $table->string('periode_kegiatan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_kegiatans');
    }
};
