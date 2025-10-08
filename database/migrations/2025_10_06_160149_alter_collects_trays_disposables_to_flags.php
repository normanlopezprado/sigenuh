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
        Schema::table('collects', function (Blueprint $table) {
            $table->tinyInteger('has_disposable')->default(0)->after('notes');
            $table->tinyInteger('companion_has_disposable')->default(0)->after('has_disposable');
            if (Schema::hasColumn('collects', 'trays_count')) {
                $table->dropColumn('trays_count');
            }
            if (Schema::hasColumn('collects', 'disposables_count')) {
                $table->dropColumn('disposables_count');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collects', function (Blueprint $table) {
            $table->unsignedInteger('trays_count')->default(0)->after('diet_type');
            $table->unsignedInteger('disposables_count')->default(0)->after('trays_count');
            if (Schema::hasColumn('collects', 'has_disposable')) {
                $table->dropColumn('has_disposable');
            }
            if (Schema::hasColumn('collects', 'companion_has_disposable')) {
                $table->dropColumn('companion_has_disposable');
            }
        });
    }
};
