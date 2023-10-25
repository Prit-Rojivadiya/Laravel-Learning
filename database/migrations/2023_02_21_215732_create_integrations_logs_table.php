<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntegrationsLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integration_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_run_id')->constrained()->cascadeOnDelete();
            $table->string('message')->nullable();
            $table->boolean('is_error')->nullable();
            $table->boolean('is_summary')->nullable();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('integrations_logs');
    }
}
