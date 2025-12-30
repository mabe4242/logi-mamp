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
        Schema::create('picking_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shipment_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipment_plan_line_id')
                ->constrained('shipment_plan_lines')
                ->cascadeOnDelete();

            $table->string('scanned_code')->nullable();
            $table->unsignedInteger('qty')->default(1);

            $table->foreignId('picked_by_admin_id')
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
        Schema::dropIfExists('picking_logs');
    }
};
