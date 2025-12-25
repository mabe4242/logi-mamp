<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            // 出荷先コード（任意）
            $table->string('code')->nullable()->unique();

            // 出荷先名（=届け先名 / 取引先名）
            $table->string('name');

            $table->string('contact_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable()->index();

            $table->string('postal_code', 20)->nullable();
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();

            // 小売の出荷でよくある情報
            $table->string('shipping_method')->nullable(); // 例：ヤマト/佐川/日本郵便など
            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
