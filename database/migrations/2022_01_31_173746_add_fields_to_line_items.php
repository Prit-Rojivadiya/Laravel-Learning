<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToLineItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('line_items', function (Blueprint $table) {
            $table->foreignId('repair_order_id')->constrained();
            $table->foreignId('line_item_category_id')->constrained();
            $table->decimal('price',12,4);
            $table->decimal('quantity',12,4);
            $table->decimal('total_price',12,4);
            $table->string('desc');
            $table->string('notes')->nullable();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('line_items', function (Blueprint $table) {
            $table->dropColumn('repair_order_id');
            $table->dropColumn('line_item_category_id');
            $table->dropColumn('price');
            $table->dropColumn('amount');
            $table->dropColumn('total_price');
            $table->dropColumn('notes');
            $table->dropColumn('tenant_id');
        });
    }
}
