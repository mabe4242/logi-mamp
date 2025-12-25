<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();

            $table->string('adjustment_no')->unique();
            $table->unsignedTinyInteger('type')->default(0);   // 0:cycle_count 1:manual 2:damage etc
            $table->unsignedTinyInteger('status')->default(0); // 0:draft 1:applied 9:canceled

            $table->dateTime('performed_at')->nullable();
            $table->text('note')->nullable();

            $table->foreignId('created_by_admin_id')->nullable()
                ->constrained('admins')->nullOnDelete();

            $table->timestamps();

            $table->index(['status', 'type', 'performed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
