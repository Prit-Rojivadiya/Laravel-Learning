<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UniqueIndexPerTenant extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->unique(['name', 'tenant_id']);
        });
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->dropUnique(['abbrv']);
            $table->unique(['name', 'tenant_id']);
            $table->unique(['abbrv', 'tenant_id']);
        });
        Schema::table('engine_manufacturers', function (Blueprint $table) {
            $table->unique(['name', 'tenant_id']);
        });
        Schema::table('fleets', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->dropUnique(['fleet_number']);
            $table->unique(['name', 'tenant_id']);
            $table->unique(['fleet_number', 'tenant_id']);
        });
        Schema::table('fuel_types', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->unique(['name', 'tenant_id']);
        });
        Schema::table('fuel_unit_types', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->unique(['name', 'tenant_id']);
        });
        Schema::table('line_item_categories', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->dropUnique(['code']);
            $table->unique(['name', 'tenant_id']);
            $table->unique(['code', 'tenant_id']);
        });
        Schema::table('line_item_types', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->dropUnique(['code']);
            $table->unique(['name', 'tenant_id']);
            $table->unique(['code', 'tenant_id']);
        });
        Schema::table('repair_order_statuses', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->unique(['name', 'tenant_id']);
        });
        Schema::table('repair_order_types', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->dropUnique(['code']);
            $table->unique(['name', 'tenant_id']);
            $table->unique(['code', 'tenant_id']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->unique(['email', 'tenant_id']);
        });
        Schema::table('vehicle_types', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->unique(['name', 'tenant_id']);
        });
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->dropUnique(['abbrv']);
            $table->unique(['name', 'tenant_id']);
            $table->unique(['abbrv', 'tenant_id']);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropUnique(['name', 'tenant_id']);
        });
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique(['name', 'tenant_id']);
            $table->dropUnique(['abbrv', 'tenant_id']);
        });
        Schema::table('engine_manufacturers', function (Blueprint $table) {
            $table->dropUnique(['name', 'tenant_id']);
        });
        Schema::table('fleets', function (Blueprint $table) {
            $table->dropUnique(['name', 'tenant_id']);
            $table->dropUnique(['fleet_number', 'tenant_id']);
        });
        Schema::table('fuel_types', function (Blueprint $table) {
            $table->dropUnique(['name', 'tenant_id']);
        });
        Schema::table('fuel_unit_types', function (Blueprint $table) {
            $table->dropUnique(['name', 'tenant_id']);
        });
        Schema::table('line_item_categories', function (Blueprint $table) {
            $table->dropUnique(['name', 'tenant_id']);
            $table->dropUnique(['code', 'tenant_id']);
        });
        Schema::table('line_item_types', function (Blueprint $table) {
            $table->dropUnique(['name', 'tenant_id']);
            $table->dropUnique(['code', 'tenant_id']);
        });
        Schema::table('repair_order_statuses', function (Blueprint $table) {
            $table->dropUnique(['name', 'tenant_id']);
        });
        Schema::table('repair_order_types', function (Blueprint $table) {
            $table->dropUnique(['name', 'tenant_id']);
            $table->dropUnique(['code', 'tenant_id']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email', 'tenant_id']);
        });
        Schema::table('vehicle_types', function (Blueprint $table) {
            $table->dropUnique(['name', 'tenant_id']);
        });
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropUnique(['name', 'tenant_id']);
            $table->dropUnique(['abbrv', 'tenant_id']);
        });

    }
}
