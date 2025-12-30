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
        Schema::create('shipment_plans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();

            $table->date('planned_ship_date')->nullable();

            $table->string('status', 30)->default('DRAFT');
            // DRAFT / PLANNED / ALLOCATED / PICKING / PACKING / SHIPPED / CANCELED

            $table->string('carrier')->nullable();       // ヤマト・佐川など
            $table->string('tracking_no')->nullable();   // 送り状番号

            $table->foreignId('created_by_admin_id')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();

            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status']);
            $table->index(['planned_ship_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipment_plans');
    }
};
