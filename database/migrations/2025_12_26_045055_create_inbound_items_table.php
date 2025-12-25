<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inbound_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inbound_id')->constrained('inbounds')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();

            $table->unsignedInteger('expected_qty')->default(0);
            $table->unsignedInteger('received_qty')->default(0);

            $table->timestamps();

            $table->index(['inbound_id']);
            $table->index(['product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbound_items');
    }
};
