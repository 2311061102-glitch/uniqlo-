<?php

use Illuminate\Database\Migrations\Migration;

/**
 * This migration was empty after the branch merge. Gateway identifiers are
 * already supplied by later payment migrations, so this historical migration
 * remains a no-op for imported databases.
 */
class AddRequestIdToPaymentsTable extends Migration
{
    public function up(): void
    {
        // Intentionally empty.
    }

    public function down(): void
    {
        // Intentionally empty.
    }
}
