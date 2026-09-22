<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('fulfillment_branch_code', 20)->nullable()->after('address_detail');
            $table->string('fulfillment_branch_name')->nullable()->after('fulfillment_branch_code');
            $table->string('fulfillment_branch_address')->nullable()->after('fulfillment_branch_name');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'fulfillment_branch_code',
                'fulfillment_branch_name',
                'fulfillment_branch_address',
            ]);
        });
    }
};
