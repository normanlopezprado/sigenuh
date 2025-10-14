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
        Schema::table('menus', function (Blueprint $table) {
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
            ])->nullable()->after('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn('diet_type');
        });
    }
};
