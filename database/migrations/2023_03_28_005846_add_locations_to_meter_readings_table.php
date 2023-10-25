<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationsToMeterReadingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            $table->string('location_state')->nullable();
            $table->string('location_country')->nullable();
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
            $table->dropColumn('location_country');
            $table->dropColumn('location_state');
        });
    }
}
