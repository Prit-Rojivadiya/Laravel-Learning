<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PmTemplateUniqueIndexPerTenant extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('preventive_maintenance_templates', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->unique(['name', 'tenant_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique(['name', 'tenant_id']);
        });
    }
}
