<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hospital_floors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('hospital_id');
            $table->uuid('nivel_id');
            $table->timestamps();

            $table->unique(['hospital_id', 'nivel_id']); 
            $table->foreign('hospital_id')->references('id')->on('hospitals')->onDelete('cascade');
            $table->foreign('nivel_id')->references('id')->on('nivels')->onDelete('cascade'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospital_floors');
    }
};
