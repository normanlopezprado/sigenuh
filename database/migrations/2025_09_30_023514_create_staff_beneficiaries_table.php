<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('staff_beneficiaries', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // hospital_id como UUID con FK
            $table->uuid('hospital_id')->nullable();
            $table->foreign('hospital_id')
                  ->references('id')->on('hospitals')
                  ->nullOnDelete();

            $table->string('full_name', 150);
            $table->string('job_title', 120)->nullable();
            $table->boolean('status')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['hospital_id', 'status']);
            $table->index('full_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_beneficiaries');
    }
};
