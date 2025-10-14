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
        Schema::create('collects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('bed_id');
            $table->date('date');
            $table->enum('meal', ['Desayuno','Almuerzo','Cena']);
            $table->enum('diet_type', [
                'Libre',
                'Blanda',
                'Hiposódica',
                'Diabético 1,200',
                'Diabético 1,500',
                'Renal',
                'Licuada',
                'Blanda 8m',
                'Papilla',
                'Especial',
            ])->nullable();

            $table->unsignedInteger('trays_count')->default(0);
            $table->unsignedInteger('disposables_count')->default(0);
            $table->uuid('user_id');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['bed_id','date','meal'], 'uq_collect_unique_bed_date_meal');

            $table->foreign('bed_id')->references('id')->on('beds')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');

            $table->index(['date','meal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collects');
    }
};
