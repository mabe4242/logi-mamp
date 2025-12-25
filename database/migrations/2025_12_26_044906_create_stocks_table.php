<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();

            $table->unsignedInteger('on_hand_qty')->default(0);
            $table->unsignedInteger('reserved_qty')->default(0);

            $table->timestamps();

            $table->unique(['product_id', 'location_id']);
            $table->index(['product_id']);
            $table->index(['location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
