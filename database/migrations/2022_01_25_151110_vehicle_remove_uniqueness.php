<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class VehicleRemoveUniqueness extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicles', function($table)
        {
            $table->dropUnique(['vehicle_number']);
            $table->unique(['fleet_id', 'vehicle_number']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicles', function($table)
        {
            $table->dropUnique(['fleet_id','vehicle_number']);
            $table->unique(['vehicle_number']);
        });
    }
}
