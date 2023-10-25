<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreventiveMaintenanceTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preventive_maintenance_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->foreignId('repair_order_type_id')->constrained();
            $table->boolean('recurring');
            $table->foreignId('system_meter_type_id')->constrained();
            $table->foreignId('system_p_m_due_type_id')->constrained();
            $table->integer('length_meters');
            $table->integer('length_days');
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('desc')->nullable();
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
        Schema::dropIfExists('preventive_maintenance_templates');
    }
}
