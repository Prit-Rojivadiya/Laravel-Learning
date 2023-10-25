<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreventiveMaintenancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preventive_maintenances', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('preventive_maintenance_template_id')->constrained()->index('pm_template_id');
            $table->foreignId('repair_order_type_id')->constrained();
            $table->boolean('recurring');
            $table->foreignId('system_meter_type_id')->constrained();
            $table->foreignId('system_p_m_due_type_id')->constrained();
            $table->integer('length_meters');
            $table->integer('length_days');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamp('completed_date')->nullable();
            $table->integer('start_at_meter')->nullable();
            $table->integer('due_at_meter')->nullable();
            $table->integer('completed_at_meter')->nullable();
            $table->string('desc')->nullable();
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
        Schema::dropIfExists('preventive_maintenances');
    }
}
