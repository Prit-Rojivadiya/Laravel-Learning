<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToRepairOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('repair_orders', function (Blueprint $table) {
            $table->foreignId('vehicle_id')->constrained();
            $table->foreignId('vendor_id')->constrained();
            $table->foreignId('repair_order_type_id')->constrained();
            $table->foreignId('repair_order_status_id')->constrained();
            $table->boolean('needs_approval')->nullable();
            $table->timestamp('approval_received_date')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('completed_date')->nullable();
            $table->integer('meter_reading')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('copy_of_purchase_order')->nullable();
            $table->decimal('total_price',12,4)->nullable();
            $table->string('desc');
            $table->string('notes')->nullable();
            $table->foreignId('preventive_maintenance_id')->nullable()->constrained();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
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
        Schema::table('repair_orders', function (Blueprint $table) {
            $table->dropColumn('vehicle_id');
            $table->dropColumn('vendor_id');
            $table->dropColumn('repair_order_type_id');
            $table->dropColumn('repair_order_status_id');
            $table->dropColumn('needs_approval');
            $table->dropColumn('approval_received_date');
            $table->dropColumn('start_date');
            $table->dropColumn('completed_date');
            $table->dropColumn('meter_reading');
            $table->dropColumn('invoice_number');
            $table->dropColumn('copy_of_purchase_order');
            $table->dropColumn('total_price');
            $table->dropColumn('desc');
            $table->dropColumn('notes');
            $table->dropColumn('preventive_maintenance_id');
            $table->dropColumn('tenant_id');
        });
    }
}
