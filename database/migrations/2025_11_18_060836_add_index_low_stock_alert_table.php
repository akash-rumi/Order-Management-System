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
        Schema::table('low_stock_alerts', function (Blueprint $table) {
            $table->date('notified_date')->nullable()->after('notified_at');
            $table->unique(['variant_id', 'notified_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('low_stock_alerts', function (Blueprint $table) {
            $table->dropUnique(['variant_id', 'notified_date']);
            $table->dropColumn('notified_date');
        });
    }
};
