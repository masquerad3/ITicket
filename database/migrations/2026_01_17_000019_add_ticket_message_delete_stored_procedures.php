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

        // Allow the same .sql files to be executed in SSMS (GO/USE) and in Laravel (DB::unprepared).
        // - GO is a client-side batch separator, not valid T-SQL.
        // - USE switches databases, which should be controlled by your Laravel connection.
        $sql = preg_replace('/^\s*GO\s*$/im', '', $sql) ?? $sql;
        $sql = preg_replace('/^\s*USE\s+[^\r\n;]+;?\s*$/im', '', $sql) ?? $sql;

        return trim($sql);
    }

    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlsrv') {
            return;
        }

        $procedures = [
            'sp_read_ticket_message_by_id.sql',
            'sp_delete_ticket_message.sql',
        ];

        foreach ($procedures as $procedure) {
            $path = database_path('stored_procedures/' . $procedure);
            if (File::exists($path)) {
                DB::unprepared($this->loadProcedureSql($procedure));
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlsrv') {
            return;
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS dbo.sp_delete_ticket_message');
        DB::unprepared('DROP PROCEDURE IF EXISTS dbo.sp_read_ticket_message_by_id');
    }
};
