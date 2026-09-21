<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();

            // Có đăng nhập -> gắn với user_id. Khách vãng lai -> user_id = null.
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();

            // Khách vãng lai được nhận diện qua mã lưu trong Cookie trình duyệt.
            $table->string('guest_token')->nullable()->unique();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};