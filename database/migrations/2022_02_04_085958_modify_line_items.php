<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyLineItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('line_items', 'desc')) {
            Schema::table('line_items', function (Blueprint $table) {
                $table->dropColumn('desc');
            });
        }
        if (Schema::hasColumn('line_items', 'notes')) {
            Schema::table('line_items', function (Blueprint $table) {
                $table->dropColumn('notes');
            });
        }

        if (!Schema::hasColumn('repair_orders', 'ro_number')) {
            Schema::table('repair_orders', function (Blueprint $table) {
                $table->string('ro_number');
                $table->unique(['tenant_id', 'ro_number']);
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
        Schema::table('line_items', function (Blueprint $table) {
            $table->string('desc');
            $table->string('notes')->nullable();
        });

        Schema::table('repair_orders', function (Blueprint $table) {
            $table->dropColumn('ro_number');
            $table->dropUnique(['tenant_id', 'ro_number']);
        });

    }
}
