<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('hospital_id')->index();
            $table->string('name', 120);
            $table->string('code_name', 120);
            $table->string('color', 20)->nullable();

            $table->unsignedSmallInteger('order')->default(0);
            $table->boolean('status')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['hospital_id','name']);
            $table->unique(['hospital_id','code_name']);
            $table->foreign('hospital_id')->references('id')->on('hospitals')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
