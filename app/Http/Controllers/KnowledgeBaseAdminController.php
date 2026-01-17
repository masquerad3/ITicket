<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KnowledgeBaseAdminController extends Controller
{
    private function isAdmin(): bool
    {
        $role = strtolower((string) (auth()->user()?->role ?? 'user'));

        return $role === 'admin';
    }

    private function sanitizeHtml(string $html): string
    {
        $html = trim($html);

        if ($html === '') {
            return '';
        }

        // Minimal allow-list sanitizer (keeps it dependency-free).
        // If you want stronger sanitization, we can swap to HTMLPurifier.
        $allowedTags = '<p><br><ul><ol><li><strong><b><em><i><a><h2><h3><h4><pre><code><blockquote><hr>';
        $clean = strip_tags($html, $allowedTags);

        // Remove all attributes except href on <a>
        $clean = preg_replace_callback(
            '/<a\s+([^>]+)>/i',
            function ($m) {
                $attrs = $m[1] ?? '';
                if (preg_match('/href\s*=\s*(["\"])(.*?)\1/i', $attrs, $mm)) {
                    $href = $mm[2];
                    // block javascript: and data:
                    if (preg_match('/^(javascript|data):/i', $href)) {
                        $href = '#';
                    }
                    $href = e($href);
                    return '<a href="'.$href.'" rel="nofollow noopener" target="_blank">';
                }

                return '<a>';
            },
            $clean
        ) ?? $clean;

        return $clean;
    }

    private function generateUniqueSlug(string $title, ?int $ignoreArticleId = null): string
    {
        $base = Str::slug($title);
        if ($base === '') {
            $base = 'article';
        }

        $slug = $base;
        $suffix = 2;

        while (true) {
            $rows = DB::select('EXEC dbo.sp_read_kb_article_by_slug_admin @slug = ?', [$slug]);
            $hit = $rows[0] ?? null;

            if (!$hit) {
                return $slug;
            }

            $hitId = (int) ($hit->article_id ?? 0);
            if ($ignoreArticleId !== null && $hitId === $ignoreArticleId) {
                return $slug;
            }

            $slug = $base . '-' . $suffix;
            $suffix++;
            if ($suffix > 200) {
                // extremely unlikely; fall back to a random suffix
                return $base . '-' . Str::lower(Str::random(6));
            }
        }
    }

    public function index(Request $request)
    {
        abort_unless($this->isAdmin(), 403);

        $q = trim((string) $request->query('q', ''));
        $categoryId = $request->query('category');
        $categoryId = is_numeric($categoryId) ? (int) $categoryId : null;

        $published = $request->query('published', 'all');
        $publishedBit = null;
        if ($published === '1' || $published === 'published') {
            $publishedBit = 1;
        } elseif ($published === '0' || $published === 'draft') {
            $publishedBit = 0;
        }

        $featured = $request->query('featured', 'all');
        $featuredBit = null;
        if ($featured === '1') {
            $featuredBit = 1;
        } elseif ($featured === '0') {
            $featuredBit = 0;
        }

        $categories = collect(DB::select('EXEC dbo.sp_read_kb_categories'));
        $articles = collect(DB::select(
            'EXEC dbo.sp_read_kb_articles_admin @q = ?, @category_id = ?, @featured = ?, @published = ?, @take = ?, @order = ?',
            [$q === '' ? null : $q, $categoryId, $featuredBit, $publishedBit, 200, 'latest']
        ));

        return view('pages.knowledge-manage', [
            'q' => $q,
            'category_id' => $categoryId,
            'published' => $published,
            'featured' => $featured,
            'categories' => $categories,
            'articles' => $articles,
        ]);
    }

    public function create()
    {
        abort_unless($this->isAdmin(), 403);

        $categories = collect(DB::select('EXEC dbo.sp_read_kb_categories'));

        return view('pages.knowledge-editor', [
            'mode' => 'create',
            'categories' => $categories,
            'article' => null,
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($this->isAdmin(), 403);

        $data = $request->validate([
            'category_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:200'],
            'summary' => ['nullable', 'string', 'max:500'],
            'content_html' => ['required', 'string'],
            'is_featured' => ['nullable'],
            'is_published' => ['nullable'],
        ]);

        $slug = $this->generateUniqueSlug($data['title']);
        $html = $this->sanitizeHtml((string) $data['content_html']);

        $rows = DB::select(
            'EXEC dbo.sp_create_kb_article @category_id=?, @title=?, @slug=?, @summary=?, @content_html=?, @is_featured=?, @is_published=?, @created_by=?',
            [
                (int) $data['category_id'],
                $data['title'],
                $slug,
                $data['summary'] ?? null,
                $html,
                $request->boolean('is_featured') ? 1 : 0,
                $request->boolean('is_published') ? 1 : 0,
                auth()->id(),
            ]
        );

        $articleId = (int) (($rows[0]->article_id ?? 0) ?: 0);

        return redirect()
            ->route('knowledge.manage.edit', ['article' => $articleId])
            ->with('success', 'Article saved.');
    }

    public function edit(int $articleId)
    {
        abort_unless($this->isAdmin(), 403);

        $categories = collect(DB::select('EXEC dbo.sp_read_kb_categories'));
        $rows = DB::select('EXEC dbo.sp_read_kb_article_by_id_admin @article_id = ?', [$articleId]);
        $article = $rows[0] ?? null;
        if (!$article) {
            abort(404);
        }

        return view('pages.knowledge-editor', [
            'mode' => 'edit',
            'categories' => $categories,
            'article' => $article,
        ]);
    }

    public function update(Request $request, int $articleId)
    {
        abort_unless($this->isAdmin(), 403);

        $rows = DB::select('EXEC dbo.sp_read_kb_article_by_id_admin @article_id = ?', [$articleId]);
        $existing = $rows[0] ?? null;
        if (!$existing) {
            abort(404);
        }

        $data = $request->validate([
            'category_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:200'],
            'summary' => ['nullable', 'string', 'max:500'],
            'content_html' => ['required', 'string'],
            'is_featured' => ['nullable'],
            'is_published' => ['nullable'],
        ]);

        $slugIn = trim((string) ($data['slug'] ?? ''));
        $slug = $slugIn !== '' ? Str::slug($slugIn) : $this->generateUniqueSlug($data['title'], $articleId);
        $slug = $this->generateUniqueSlug($slug, $articleId);

        $html = $this->sanitizeHtml((string) $data['content_html']);

        DB::select(
            'EXEC dbo.sp_update_kb_article @article_id=?, @category_id=?, @title=?, @slug=?, @summary=?, @content_html=?, @is_featured=?, @is_published=?, @updated_by=?',
            [
                $articleId,
                (int) $data['category_id'],
                $data['title'],
                $slug,
                $data['summary'] ?? null,
                $html,
                $request->boolean('is_featured') ? 1 : 0,
                $request->boolean('is_published') ? 1 : 0,
                auth()->id(),
            ]
        );

        return redirect()
            ->route('knowledge.manage.edit', ['article' => $articleId])
            ->with('success', 'Article updated.');
    }

    public function setPublish(Request $request, int $articleId)
    {
        abort_unless($this->isAdmin(), 403);

        $request->validate([
            'is_published' => ['required'],
        ]);

        DB::select(
            'EXEC dbo.sp_set_kb_article_publish @article_id=?, @is_published=?, @updated_by=?',
            [$articleId, $request->boolean('is_published') ? 1 : 0, auth()->id()]
        );

        return back()->with('success', $request->boolean('is_published') ? 'Article published.' : 'Article unpublished.');
    }

    public function setFeatured(Request $request, int $articleId)
    {
        abort_unless($this->isAdmin(), 403);

        $request->validate([
            'is_featured' => ['required'],
        ]);

        DB::select(
            'EXEC dbo.sp_set_kb_article_featured @article_id=?, @is_featured=?, @updated_by=?',
            [$articleId, $request->boolean('is_featured') ? 1 : 0, auth()->id()]
        );

        return back()->with('success', $request->boolean('is_featured') ? 'Marked as featured.' : 'Removed from featured.');
    }

    public function destroy(int $articleId)
    {
        abort_unless($this->isAdmin(), 403);

        DB::select('EXEC dbo.sp_delete_kb_article @article_id=?', [$articleId]);

        return redirect()->route('knowledge.manage')->with('success', 'Article deleted.');
    }
}
