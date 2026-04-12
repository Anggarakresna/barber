<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('barbers', 'is_active')) {
            Schema::table('barbers', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('experience');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('barbers', 'is_active')) {
            Schema::table('barbers', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
