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
        Schema::table('hospitals', function (Blueprint $table) {
            // Breakfast
            $table->time('breakfast_collection_start')->nullable()->after('status');
            $table->time('breakfast_collection_end')->nullable()->after('breakfast_collection_start');
            $table->time('breakfast_time')->nullable()->after('breakfast_collection_end');

            // Lunch
            $table->time('lunch_collection_start')->nullable()->after('breakfast_time');
            $table->time('lunch_collection_end')->nullable()->after('lunch_collection_start');
            $table->time('lunch_time')->nullable()->after('lunch_collection_end');

            // Dinner
            $table->time('dinner_collection_start')->nullable()->after('lunch_time');
            $table->time('dinner_collection_end')->nullable()->after('dinner_collection_start');
            $table->time('dinner_time')->nullable()->after('dinner_collection_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hospitals', function (Blueprint $table) {
            $table->dropColumn([
                'breakfast_collection_start',
                'breakfast_collection_end',
                'breakfast_time',
                'lunch_collection_start',
                'lunch_collection_end',
                'lunch_time',
                'dinner_collection_start',
                'dinner_collection_end',
                'dinner_time',
            ]);
        });
    }
};
