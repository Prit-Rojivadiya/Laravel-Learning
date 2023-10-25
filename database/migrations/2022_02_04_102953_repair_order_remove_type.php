<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RepairOrderRemoveType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('repair_orders', 'repair_order_type_id')) {
            Schema::table('repair_orders', function (Blueprint $table) {
                $table->dropForeign(['repair_order_type_id']);
                $table->dropColumn('repair_order_type_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('repair_orders', function (Blueprint $table) {
            $table->foreignId('repair_order_type_id')->constrained();
        });
    }
}
