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
            'sp_read_kb_articles_admin.sql',
            'sp_read_kb_article_by_id_admin.sql',
            'sp_read_kb_article_by_slug_admin.sql',
            'sp_set_kb_article_publish.sql',
            'sp_set_kb_article_featured.sql',
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

        $drops = [
            'sp_set_kb_article_featured',
            'sp_set_kb_article_publish',
            'sp_read_kb_article_by_slug_admin',
            'sp_read_kb_article_by_id_admin',
            'sp_read_kb_articles_admin',
        ];

        foreach ($drops as $proc) {
            DB::unprepared("DROP PROCEDURE IF EXISTS dbo.$proc");
        }
    }
};
