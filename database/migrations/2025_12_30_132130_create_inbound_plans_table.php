<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inbound_plans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();

            $table->date('planned_date')->nullable();

            // 状態（最初はこれで十分。増やしたければ後で追加）
            $table->string('status', 30)->default('DRAFT'); 
            // DRAFT / RECEIVING / WAITING_PUTAWAY / COMPLETED / CANCELED など

            // MVPは admin 作業前提
            $table->foreignId('created_by_admin_id')->nullable()
                ->constrained('admins')->nullOnDelete();

            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['supplier_id', 'planned_date']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbound_plans');
    }
};
