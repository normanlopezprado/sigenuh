<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('staff_meal_records', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('staff_beneficiary_id');
            $table->foreign('staff_beneficiary_id')
                  ->references('id')->on('staff_beneficiaries')
                  ->cascadeOnDelete();

            $table->uuid('hospital_id')->nullable();
            $table->foreign('hospital_id')
                  ->references('id')->on('hospitals')
                  ->nullOnDelete();

            $table->enum('meal_type', ['desayuno','almuerzo','cena']);

            $table->uuid('menu_id');
            $table->foreign('menu_id')->references('id')->on('menus');

            $table->uuid('delivered_by');
            $table->foreign('delivered_by')->references('id')->on('users');

            $table->timestamp('delivered_at')->useCurrent();
            $table->date('delivery_date');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['hospital_id','meal_type','delivery_date']);
            $table->index('delivered_by');

            $table->unique(['staff_beneficiary_id','meal_type','delivery_date'], 'uniq_staff_beneficiary_meal_day');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_meal_records');
    }
};
