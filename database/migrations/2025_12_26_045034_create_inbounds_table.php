<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inbounds', function (Blueprint $table) {
            $table->id();

            $table->string('inbound_no')->unique(); // 採番
            $table->unsignedTinyInteger('status')->default(0); // 0:draft 1:received 2:putaway_completed 9:canceled

            $table->date('expected_date')->nullable();
            $table->dateTime('received_at')->nullable();

            //いやここリレーションだろ...
            $table->string('supplier_name')->nullable();
            $table->text('note')->nullable();

            // 既存のマルチログイン(管理者)を活用
            $table->foreignId('created_by_admin_id')->nullable()
                ->constrained('admins')->nullOnDelete();

            $table->timestamps();

            $table->index(['status', 'expected_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbounds');
    }
};
