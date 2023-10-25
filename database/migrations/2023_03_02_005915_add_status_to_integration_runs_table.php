<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToIntegrationRunsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('integration_runs', function (Blueprint $table) {
            $table->string('status');
            $table->string('summary_msg')->nullable();
            $table->string('error_msg')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('integration_runs', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('summary_msg');
            $table->dropColumn('error_msg');
        });
    }
}
