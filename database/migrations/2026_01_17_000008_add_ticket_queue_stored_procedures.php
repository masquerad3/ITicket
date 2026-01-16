<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    private function loadProcedureSql(string $filename): string
    {
        $sql = File::get(database_path('stored_procedures/'.$filename));
        $sql = ltrim($sql, "\xEF\xBB\xBF");

        $sql = preg_replace('/^\s*GO\s*$/im', '', $sql) ?? $sql;
        $sql = preg_replace('/^\s*USE\s+[^\r\n;]+;?\s*$/im', '', $sql) ?? $sql;

        return trim($sql);
    }

    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlsrv') {
            return;
        }

        $files = [
            'sp_read_unassigned_tickets.sql',
            'sp_read_tickets_assigned_to_user.sql',
        ];

        foreach ($files as $file) {
            DB::unprepared($this->loadProcedureSql($file));
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlsrv') {
            return;
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS dbo.sp_read_tickets_assigned_to_user');
        DB::unprepared('DROP PROCEDURE IF EXISTS dbo.sp_read_unassigned_tickets');
    }
};
