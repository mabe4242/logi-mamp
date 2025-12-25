<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();

            // 仕入先コード（任意。あると運用が楽）
            $table->string('code')->nullable()->unique();

            $table->string('name'); // 仕入先名（必須）
            $table->string('contact_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable()->index();

            $table->string('postal_code', 20)->nullable();
            $table->string('address1')->nullable(); // 都道府県・市区町村など
            $table->string('address2')->nullable(); // 番地・建物名など

            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
