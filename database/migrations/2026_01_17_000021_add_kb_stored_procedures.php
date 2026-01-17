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
            'sp_create_kb_category.sql',
            'sp_read_kb_categories.sql',
            'sp_create_kb_article.sql',
            'sp_update_kb_article.sql',
            'sp_delete_kb_article.sql',
            'sp_read_kb_articles.sql',
            'sp_read_kb_article_by_slug.sql',
            'sp_increment_kb_article_view.sql',
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
            'sp_increment_kb_article_view',
            'sp_read_kb_article_by_slug',
            'sp_read_kb_articles',
            'sp_delete_kb_article',
            'sp_update_kb_article',
            'sp_create_kb_article',
            'sp_read_kb_categories',
            'sp_create_kb_category',
        ];

        foreach ($drops as $proc) {
            DB::unprepared("DROP PROCEDURE IF EXISTS dbo.$proc");
        }
    }
};
