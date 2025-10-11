<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collects', function (Blueprint $table) {
            $table->string('minor_age', 5)
                  ->nullable()
                  ->after('has_minor')
                  ->comment('Edad del paciente menor en formato ej: 8m = 8 meses, 1a = 1 año');
        });
    }

    public function down(): void
    {
        Schema::table('collects', function (Blueprint $table) {
            $table->dropColumn('minor_age');
        });
    }
};
