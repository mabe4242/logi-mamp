<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('putaway_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inbound_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inbound_plan_line_id')->constrained('inbound_plan_lines')->cascadeOnDelete();

            $table->foreignId('location_id')->constrained()->restrictOnDelete();

            $table->unsignedInteger('qty')->default(0);

            $table->foreignId('putaway_by_admin_id')->nullable()
                ->constrained('admins')->nullOnDelete();

            $table->timestamps();

            $table->index(['inbound_plan_id', 'created_at']);
            $table->index(['location_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('putaway_lines');
    }
};
