<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // ID del hospital al cual pertenece este carrito
            $table->uuid('hospital_id')->index();

            // Nombre oficial del carrito (ejemplo: "Cart #1")
            $table->string('name', 120);

            // Apodo interno o código que usan en el hospital (ejemplo: "Servicios", "Café", etc.)
            $table->string('code_name', 120);

            // Color opcional para usar en etiquetas UI (ej: "success", "#198754")
            $table->string('color', 20)->nullable();

            // Posición para ordenar carritos en listados
            $table->unsignedSmallInteger('order')->default(0);

            // Activo o inactivo (no se elimina, solo se oculta)
            $table->boolean('status')->default(true);

            // Notas internas / comentarios
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Restricción: no se puede repetir name + hospital
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
