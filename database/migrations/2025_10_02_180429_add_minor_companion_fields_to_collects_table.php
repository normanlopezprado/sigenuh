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
        Schema::table('collects', function (Blueprint $table) {
            $table->boolean('has_minor')->default(false)->after('notes');
            $table->boolean('has_companion')->default(false)->after('has_minor');
            $table->enum('companion_diet_type', [
                'Libre','Blanda','Hiposódica','Diabético 1,200','Diabético 1,500','Renal','Licuada','Especial',
            ])->nullable()->after('has_companion');
            $table->text('companion_notes')->nullable()->after('companion_diet_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collects', function (Blueprint $table) {
            $table->dropColumn(['has_minor','has_companion','companion_diet_type', 'companion_notes']);
        });
    }
};
