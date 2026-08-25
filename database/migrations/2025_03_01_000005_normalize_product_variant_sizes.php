<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $sizeMap = [
            '29' => 'S',
            '30' => 'M',
            '31' => 'L',
            '32' => 'XL',
            '34' => 'XXL',
        ];

        foreach ($sizeMap as $numericSize => $letterSize) {
            DB::table('product_variants')
                ->where('size', '=', (string) $numericSize)
                ->update(['size' => $letterSize]);
        }
    }

    public function down(): void
    {
        $sizeMap = [
            'S' => '29',
            'M' => '30',
            'L' => '31',
            'XL' => '32',
            'XXL' => '34',
        ];

        foreach ($sizeMap as $letterSize => $numericSize) {
            DB::table('product_variants')
                ->where('size', '=', (string) $letterSize)
                ->update(['size' => $numericSize]);
        }
    }
};
