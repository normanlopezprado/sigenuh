<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cart_service', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('cart_id')->index();
            $table->uuid('hospital_floor_service_id')->unique();
            $table->unsignedSmallInteger('order')->default(0);
            $table->uuid('assigned_by')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();
            $table->foreign('cart_id')->references('id')->on('carts')->cascadeOnDelete();
            $table->foreign('hospital_floor_service_id')->references('id')->on('hospital_floor_services')->cascadeOnDelete();
            $table->foreign('assigned_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_service');
    }
};
