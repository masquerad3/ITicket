<!DOCTYPE html>
<html lang="en-US">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ITicket - Manage Knowledge Base</title>

  <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/components/topbar.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/components/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/pages/knowledge.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/pages/knowledge-manage.css') }}">

  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
  <div class="page">
    <header class="topbar">
      <div class="topbar-left">
        <button class="menu-button" aria-label="Open menu">
          <i class='bx bx-menu'></i>
        </button>
      </div>
      <div class="topbar-right">
        <button class="notif-button" aria-label="Notifications"><i class='bx bx-bell'></i></button>
        @include('partials.profile-chip')
      </div>
    </header>

    @include('partials.sidebar')
    <div class="backdrop" aria-hidden="true"></div>

    <main class="content">
      <section class="panel kb-panel">
        <div class="panel-head kb-manage-head">
          <div>
            <h3>Manage Knowledge Base</h3>
            <div class="muted small">Create, edit, publish, and feature articles.</div>
          </div>
          <div class="kb-manage-actions">
            <a class="btn-primary" href="{{ route('knowledge.manage.create') }}"><i class='bx bx-plus'></i> New Article</a>
            <a class="btn-outlined" href="{{ route('knowledge') }}"><i class='bx bx-book'></i> View KB</a>
          </div>
        </div>

        @if (session('success'))
          <div class="alert success">{{ session('success') }}</div>
        @endif

        <div class="panel-body">
          <div class="kb-filters-card">
            <div class="kb-filters-top">
              <div class="kb-results">
                <span class="muted">Results</span>
                <span class="count">{{ is_countable($articles) ? count($articles) : 0 }}</span>
              </div>
              <a class="kb-reset" href="{{ route('knowledge.manage') }}"><i class='bx bx-refresh'></i> Reset</a>
            </div>

            <form method="GET" action="{{ route('knowledge.manage') }}" class="kb-filters">
              <div class="field">
                <label>Search</label>
                <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Title, summary, slug…">
              </div>

              <div class="field">
                <label>Category</label>
                <select name="category">
                  <option value="">All</option>
                  @foreach ($categories as $c)
                    <option value="{{ $c->category_id }}" @selected(!empty($category_id) && (int)$category_id === (int)$c->category_id)>{{ $c->name }}</option>
                  @endforeach
                </select>
              </div>

              <div class="field">
                <label>Published</label>
                <select name="published">
                  <option value="all" @selected(($published ?? 'all') === 'all')>All</option>
                  <option value="published" @selected(($published ?? '') === 'published' || ($published ?? '') === '1')>Published</option>
                  <option value="draft" @selected(($published ?? '') === 'draft' || ($published ?? '') === '0')>Draft</option>
                </select>
              </div>

              <div class="field">
                <label>Featured</label>
                <select name="featured">
                  <option value="all" @selected(($featured ?? 'all') === 'all')>All</option>
                  <option value="1" @selected(($featured ?? '') === '1')>Featured</option>
                  <option value="0" @selected(($featured ?? '') === '0')>Not featured</option>
                </select>
              </div>

              <div class="field actions">
                <label>&nbsp;</label>
                <button class="btn-outlined" type="submit"><i class='bx bx-filter'></i> Apply</button>
              </div>
            </form>
          </div>

          <div class="kb-table">
            <div class="kb-table-head">
              <div>Title</div>
              <div>Status</div>
              <div>Category</div>
              <div>Updated</div>
              <div>Actions</div>
            </div>

            @forelse ($articles as $a)
              <div class="kb-row">
                <div class="kb-title">
                  <div class="title">{{ $a->title }}</div>
                  <div class="muted small">/{{ $a->slug }}</div>
                </div>

                <div class="kb-status">
                  @if (!empty($a->is_published))
                    <span class="pill pill-green">Published</span>
                  @else
                    <span class="pill pill-gray">Draft</span>
                  @endif
                  @if (!empty($a->is_featured))
                    <span class="pill pill-purple">Featured</span>
                  @endif
                </div>

                <div class="kb-cat">{{ $a->category_name ?? '—' }}</div>

                <div class="kb-updated">
                  {{ !empty($a->updated_at) ? (string) $a->updated_at : '—' }}
                </div>

                <div class="kb-actions">
                  <a class="btn-outlined small" href="{{ route('knowledge.manage.edit', ['article' => $a->article_id]) }}"><i class='bx bx-edit'></i> Edit</a>

                  <form method="POST" action="{{ route('knowledge.manage.featured', ['article' => $a->article_id]) }}" class="inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="is_featured" value="{{ !empty($a->is_featured) ? 0 : 1 }}">
                    <button class="btn-outlined small" type="submit"><i class='bx bx-star'></i> {{ !empty($a->is_featured) ? 'Unfeature' : 'Feature' }}</button>
                  </form>

                  <form method="POST" action="{{ route('knowledge.manage.publish', ['article' => $a->article_id]) }}" class="inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="is_published" value="{{ !empty($a->is_published) ? 0 : 1 }}">
                    <button class="btn-primary small" type="submit"><i class='bx bx-upload'></i> {{ !empty($a->is_published) ? 'Unpublish' : 'Publish' }}</button>
                  </form>

                  <form method="POST" action="{{ route('knowledge.manage.delete', ['article' => $a->article_id]) }}" class="inline" onsubmit="return confirm('Delete this article?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn-danger small" type="submit"><i class='bx bx-trash'></i> Delete</button>
                  </form>

                  @if (!empty($a->is_published))
                    <a class="btn-outlined small" href="{{ route('knowledge.show', ['slug' => $a->slug]) }}"><i class='bx bx-link-external'></i> View</a>
                  @endif
                </div>
              </div>
            @empty
              <div class="kb-empty">
                <div class="icon"><i class='bx bx-file-find'></i></div>
                <div class="title">No articles found</div>
                <div class="muted">Try clearing filters, or create your first article.</div>
                <div class="actions">
                  <a class="btn-primary" href="{{ route('knowledge.manage.create') }}"><i class='bx bx-plus'></i> New Article</a>
                  <a class="btn-outlined" href="{{ route('knowledge.manage') }}"><i class='bx bx-refresh'></i> Reset filters</a>
                </div>
              </div>
            @endforelse
          </div>
        </div>
      </section>
    </main>
  </div>

  <script src="{{ asset('assets/js/components/sidebar.js') }}"></script>
</body>
</html>
