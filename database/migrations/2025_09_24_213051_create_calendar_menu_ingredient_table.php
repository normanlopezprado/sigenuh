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
        Schema::create('calendar_menu_ingredient', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('calendar_id');
            $table->uuid('menu_ingredient_id');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign('calendar_id')
                ->references('id')->on('calendars')
                ->onDelete('cascade');
            $table->foreign('menu_ingredient_id')
                ->references('id')->on('menu_ingredient')
                ->onDelete('cascade');
            $table->unique(['calendar_id','menu_ingredient_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_menu_ingredient');
    }
};
