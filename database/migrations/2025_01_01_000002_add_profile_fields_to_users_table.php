<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Laravel đã tạo sẵn bảng "users" cơ bản (name, email, password).
     * Migration này KHÔNG tạo bảng mới, mà thêm cột cần thiết vào bảng users có sẵn:
     * - phone: số điện thoại
     * - avatar: đường dẫn ảnh đại diện
     * - role_id: khóa ngoại trỏ tới bảng roles (biết user này là admin/staff/customer)
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 15)->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->foreignId('role_id')->nullable()->after('avatar')
                  ->constrained('roles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['phone', 'avatar', 'role_id']);
        });
    }
};
