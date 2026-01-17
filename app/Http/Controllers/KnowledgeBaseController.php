<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class KnowledgeBaseController extends Controller
{
    private function parseDbDatetime(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        $appTz = (string) (config('app.timezone') ?: 'UTC');
        $dbTz = (string) (config('database.connections.sqlsrv.timezone') ?: $appTz);

        try {
            if ($value instanceof \DateTimeInterface) {
                return Carbon::instance($value)->setTimezone($appTz);
            }

            return Carbon::parse((string) $value, $dbTz)->setTimezone($appTz);
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeArticleRow(object $row): object
    {
        foreach (['created_at', 'updated_at'] as $field) {
            if (!isset($row->{$field}) || $row->{$field} === null || $row->{$field} === '') {
                continue;
            }

            $parsed = $this->parseDbDatetime($row->{$field});
            if ($parsed !== null) {
                $row->{$field} = $parsed;
            }
        }

        return $row;
    }

    private function normalizeCollection($rows)
    {
        return collect($rows)
            ->map(fn ($r) => $this->normalizeArticleRow($r))
            ->values();
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $categoryId = $request->query('category');
        $categoryId = is_numeric($categoryId) ? (int) $categoryId : null;

        $categories = collect(DB::select('EXEC dbo.sp_read_kb_categories'));

        $featured = collect();
        $latest = collect();
        $results = collect();

        if ($q !== '' || $categoryId !== null) {
            $results = $this->normalizeCollection(DB::select(
                'EXEC dbo.sp_read_kb_articles @q = ?, @category_id = ?, @featured = ?, @take = ?, @order = ?',
                [$q === '' ? null : $q, $categoryId, null, 50, 'latest']
            ));
        } else {
            $featured = $this->normalizeCollection(DB::select(
                'EXEC dbo.sp_read_kb_articles @q = ?, @category_id = ?, @featured = ?, @take = ?, @order = ?',
                [null, null, 1, 6, 'featured']
            ));

            $latest = $this->normalizeCollection(DB::select(
                'EXEC dbo.sp_read_kb_articles @q = ?, @category_id = ?, @featured = ?, @take = ?, @order = ?',
                [null, null, null, 6, 'latest']
            ));
        }

        $activeCategory = null;
        if ($categoryId !== null) {
            $activeCategory = $categories->firstWhere('category_id', $categoryId);
        }

        return view('pages.knowledge', [
            'q' => $q,
            'category_id' => $categoryId,
            'activeCategory' => $activeCategory,
            'categories' => $categories,
            'featured' => $featured,
            'latest' => $latest,
            'results' => $results,
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $rows = DB::select('EXEC dbo.sp_read_kb_article_by_slug @slug = ?', [$slug]);
        $article = $rows[0] ?? null;

        if (!$article) {
            abort(404);
        }

        $article = $this->normalizeArticleRow($article);

        try {
            DB::select('EXEC dbo.sp_increment_kb_article_view @article_id = ?', [$article->article_id]);
        } catch (\Throwable) {
            // best-effort; do not fail the page load
        }

        return view('pages.knowledge-article', [
            'article' => $article,
        ]);
    }
}
