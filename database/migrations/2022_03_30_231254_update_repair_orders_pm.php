<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateRepairOrdersPm extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('repair_orders', function (Blueprint $table) {
            $table->dropForeign('repair_orders_preventive_maintenance_id_foreign');
            $table->dropColumn('preventive_maintenance_id');
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
            $table->foreignId('preventive_maintenance_id')->constrained();
        });
    }
}
