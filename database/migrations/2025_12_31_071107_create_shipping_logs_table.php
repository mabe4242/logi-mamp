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
        Schema::create('shipping_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shipment_plan_id')->constrained()->cascadeOnDelete();

            $table->string('carrier')->nullable();
            $table->string('tracking_no')->nullable();

            $table->timestamp('shipped_at')->nullable();

            $table->foreignId('shipped_by_admin_id')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['shipment_plan_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipping_logs');
    }
};
