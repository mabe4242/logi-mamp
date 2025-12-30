<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('receiving_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inbound_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inbound_plan_line_id')->constrained('inbound_plan_lines')->cascadeOnDelete();

            // バーコード or SKU など入力された生値を保持（後で監査に効く）
            $table->string('scanned_code')->nullable();

            // 基本1、手入力加算に備えてqtyを持つ
            $table->unsignedInteger('qty')->default(1);

            $table->foreignId('scanned_by_admin_id')->nullable()
                ->constrained('admins')->nullOnDelete();

            // 将来 user 作業に広げたいならここも追加できる（今は不要なので入れない）
            // $table->foreignId('scanned_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['inbound_plan_id', 'created_at']);
            $table->index(['inbound_plan_line_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receiving_logs');
    }
};
