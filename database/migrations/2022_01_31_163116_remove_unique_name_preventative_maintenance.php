<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveUniqueNamePreventativeMaintenance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('preventive_maintenances', function($table)
        {
            $table->dropUnique(['name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('preventive_maintenances', function($table)
        {
            $table->unique(['name']);
        });
    }
}
