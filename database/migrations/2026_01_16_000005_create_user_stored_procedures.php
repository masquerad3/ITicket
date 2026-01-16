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
            'sp_create_user.sql',
            'sp_read_user_by_id.sql',
            'sp_read_user_by_email.sql',
            'sp_read_all_users.sql',
            'sp_update_user.sql',
            'sp_update_user_profile.sql',
            'sp_update_user_password.sql',
            'sp_deactivate_user.sql',
            'sp_delete_user.sql',
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

        // Drop in reverse order.
        $drops = [
            'sp_delete_user',
            'sp_deactivate_user',
            'sp_update_user_password',
            'sp_update_user_profile',
            'sp_update_user',
            'sp_read_all_users',
            'sp_read_user_by_email',
            'sp_read_user_by_id',
            'sp_create_user',
        ];

        foreach ($drops as $proc) {
            DB::unprepared("DROP PROCEDURE IF EXISTS dbo.$proc");
        }
    }
};
