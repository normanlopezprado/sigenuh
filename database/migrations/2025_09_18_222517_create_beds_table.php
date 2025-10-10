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
        Schema::create('beds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('hospital_floor_service_id');

            $table->foreign('hospital_floor_service_id')
                ->references('id')->on('hospital_floor_services')
                ->onDelete('cascade');

            $table->string('code', 50)->unique();

            $table->enum('status', ['Disponible','Ocupada'])
                ->default('Disponible');
                
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('hospital_floor_service_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beds');
    }
};
