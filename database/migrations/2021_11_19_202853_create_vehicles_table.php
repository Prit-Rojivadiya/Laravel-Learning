<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_number')->unique();
            $table->decimal('purchase_price',12,4)->nullable();
            $table->timestamp('in_service_date')->nullable();
            $table->integer('year')->nullable();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->string('vin')->nullable();
            $table->string('tire_size')->nullable();
            $table->string('license_plate_number')->nullable();
            $table->string('license_state')->nullable();
            $table->string('engine_serial_number')->nullable();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fleet_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicles');
    }
}
