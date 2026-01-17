<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

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

        if (!Schema::hasColumn('USERS', 'profile_photo_path')) {
            Schema::table('USERS', function (Blueprint $table) {
                $table->string('profile_photo_path', 255)->nullable()->after('is_active');
            });
        }

        // Install/upgrade procs that reference the new column + hard delete support.
        $files = [
            'sp_update_user_photo.sql',
            'sp_read_user_by_id_v2.sql',
            'sp_read_user_by_email_v2.sql',
            'sp_read_ticket_by_id_v2.sql',
            'sp_read_ticket_messages_by_ticket_v2.sql',
            'sp_hard_delete_ticket.sql',
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

        // Restore original procs first (they don't reference profile_photo_path).
        foreach ([
            'sp_read_user_by_id.sql',
            'sp_read_user_by_email.sql',
            'sp_read_ticket_by_id.sql',
            'sp_read_ticket_messages_by_ticket.sql',
        ] as $file) {
            if (File::exists(database_path('stored_procedures/'.$file))) {
                DB::unprepared($this->loadProcedureSql($file));
            }
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS dbo.sp_update_user_photo');
        DB::unprepared('DROP PROCEDURE IF EXISTS dbo.sp_hard_delete_ticket');

        if (Schema::hasColumn('USERS', 'profile_photo_path')) {
            Schema::table('USERS', function (Blueprint $table) {
                $table->dropColumn('profile_photo_path');
            });
        }
    }
};
