<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGeotabPrefsToIntegrationGeotabsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('integration_geotabs', function (Blueprint $table) {
            $table->string('geotab_database')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('integration_geotabs', function (Blueprint $table) {
            $table->dropColumn('geotab_database');
        });
    }
}
