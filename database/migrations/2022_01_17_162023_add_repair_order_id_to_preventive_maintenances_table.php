<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRepairOrderIdToPreventiveMaintenancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('preventive_maintenances', function (Blueprint $table) {
            $table->foreignId('repair_order_id')->nullable()->constrained();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('preventive_maintenances', function (Blueprint $table) {
            $table->dropColumn('repair_order_id');
        });
    }
}
