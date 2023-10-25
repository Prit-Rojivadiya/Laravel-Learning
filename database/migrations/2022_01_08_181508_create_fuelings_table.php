<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFuelingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fuelings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained();
            $table->foreignId('fuel_unit_type_id')->constrained();
            $table->decimal('price_per_unit',12,4);
            $table->decimal('total_units',12,4);
            $table->decimal('total_price',12,4);
            $table->integer('meter_reading');
            $table->foreignId('meter_reading_id')->nullable();
            $table->timestamp('fueling_date');
            $table->foreignId('fuel_type_id')->constrained();
            $table->string('location_country');
            $table->string('location_state');
            $table->string('notes')->nullable();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
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
        Schema::dropIfExists('fuelings');
    }
}
