<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExternalSourceToMeterReadingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            $table->string('external_source')->nullable();
            $table->string('external_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            $table->dropColumn('external_source');
            $table->dropColumn('external_id');
        });
    }
}
