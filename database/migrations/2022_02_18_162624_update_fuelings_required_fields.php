<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFuelingsRequiredFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fuelings', function($table)
        {
            $table->foreignId('vendor_id')->nullable()->change();
            $table->foreignId('fuel_type_id')->nullable()->change();
            $table->string('location_country')->nullable()->change();
            $table->string('location_state')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fuelings', function($table)
        {
            $table->foreignId('vendor_id')->nullable(false)->change();
            $table->foreignId('fuel_type_id')->nullable(false)->change();
            $table->string('location_country')->nullable(false)->change();
            $table->string('location_state')->nullable(false)->change();
        });
    }
}
