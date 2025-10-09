<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private string $table = 'collects';

    public function up(): void
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }

        $this->convertToBoolean('trays');
        $this->convertToBoolean('disposables');
    }

    public function down(): void
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }

        $this->revertToInteger('trays');
        $this->revertToInteger('disposables');
    }

    private function convertToBoolean(string $column): void
    {
        if (!$this->columnExists($column)) {
            return;
        }

        DB::statement(sprintf(
            'ALTER TABLE `%s` MODIFY `%s` TINYINT(1) NOT NULL DEFAULT 0',
            $this->table,
            $column
        ));
    }

    private function revertToInteger(string $column): void
    {
        if (!$this->columnExists($column)) {
            return;
        }

        DB::statement(sprintf(
            'ALTER TABLE `%s` MODIFY `%s` INT NOT NULL',
            $this->table,
            $column
        ));
    }

    private function columnExists(string $column): bool
    {
        $result = DB::select(
            'SELECT COUNT(*) AS aggregate FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
            [$this->table, $column]
        );

        return isset($result[0]) && (int) $result[0]->aggregate > 0;
    }
};
