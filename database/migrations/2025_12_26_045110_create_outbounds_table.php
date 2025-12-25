<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('outbounds', function (Blueprint $table) {
            $table->id();

            $table->string('outbound_no')->unique();
            $table->unsignedTinyInteger('status')->default(0); // 0:draft 1:picking 2:packed 3:shipped 9:canceled

            $table->date('ship_date')->nullable();
            $table->dateTime('shipped_at')->nullable();

            $table->string('order_no')->nullable()->index();
            $table->string('customer_name')->nullable();
            $table->text('note')->nullable();

            $table->foreignId('created_by_admin_id')->nullable()
                ->constrained('admins')->nullOnDelete();

            $table->timestamps();

            $table->index(['status', 'ship_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbounds');
    }
};
