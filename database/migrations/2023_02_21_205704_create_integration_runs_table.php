<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntegrationRunsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integration_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_id')->constrained()->cascadeOnDelete();
            $table->string('task')->nullable();
            $table->timestamp('started')->nullable();
            $table->timestamp('completed')->nullable();
            $table->integer('total')->nullable();
            $table->integer('success_count')->nullable();
            $table->integer('failed_count')->nullable();
            $table->json('results')->nullable();
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
        Schema::dropIfExists('integration_runs');
    }
}
