<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipment_plan_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shipment_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();

            $table->unsignedInteger('planned_qty')->default(0);
            $table->unsignedInteger('picked_qty')->default(0);
            $table->unsignedInteger('shipped_qty')->default(0);

            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['shipment_plan_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipment_plan_lines');
    }
};
