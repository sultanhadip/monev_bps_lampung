<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('update_target_realisasis', function (Blueprint $table) {
            $table->string('kondisi')->nullable()->after('keterangan');
        });
    }

    public function down()
    {
        Schema::table('update_target_realisasis', function (Blueprint $table) {
            $table->dropColumn('kondisi');
        });
    }
};
