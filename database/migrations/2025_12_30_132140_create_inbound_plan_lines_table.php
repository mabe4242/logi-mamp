<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inbound_plan_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inbound_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();

            $table->unsignedInteger('planned_qty')->default(0);

            // ②検品で増える（スキャン/手入力）
            $table->unsignedInteger('received_qty')->default(0);

            // ③入庫で増える（stocks反映はここではなく putaway 登録時に実行）
            $table->unsignedInteger('putaway_qty')->default(0);

            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // 同一入荷予定内で同一商品を重複させない運用が多いので unique 推奨
            $table->unique(['inbound_plan_id', 'product_id']);

            $table->index(['product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbound_plan_lines');
    }
};
