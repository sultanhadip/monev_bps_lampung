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
        Schema::create('update_target_realisasis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_target_realisasi');
            $table->integer('realisasi_satker');
            $table->string('bukti_dukung_realisasi')->nullable();
            $table->string('keterangan')->nullable();
            $table->string('pesan')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();

            // Relasi dengan tabel penilaian
            $table->foreign('id_target_realisasi')->references('id')->on('target_realisasi_satkers')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('update_target_realisasis');
    }
};
