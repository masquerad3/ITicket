<!DOCTYPE html>
<html lang="en-US">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>
    ITicket -
    {{ ($mode ?? 'create') === 'edit' ? 'Edit Article' : 'New Article' }}
  </title>

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
            <h3>{{ ($mode ?? 'create') === 'edit' ? 'Edit Article' : 'Create Article' }}</h3>
            <div class="muted small">HTML is allowed (basic safe allow-list).</div>
          </div>
          <div class="kb-manage-actions">
            <a class="btn-outlined" href="{{ route('knowledge.manage') }}"><i class='bx bx-left-arrow-alt'></i> Back</a>
            @if (($mode ?? 'create') === 'edit' && !empty($article?->is_published))
              <a class="btn-outlined" href="{{ route('knowledge.show', ['slug' => $article->slug]) }}"><i class='bx bx-link-external'></i> View</a>
            @endif
          </div>
        </div>

        @if (session('success'))
          <div class="alert success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
          <div class="alert danger">
            <div><strong>Fix the following:</strong></div>
            <ul>
              @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <div class="panel-body">
          @php
            $isEdit = ($mode ?? 'create') === 'edit';
            $action = $isEdit
              ? route('knowledge.manage.update', ['article' => $article->article_id])
              : route('knowledge.manage.store');
          @endphp

          <form method="POST" action="{{ $action }}" class="kb-editor">
            @csrf
            @if ($isEdit)
              @method('PATCH')
            @endif

            <div class="kb-editor-grid">
              <div class="field">
                <label>Title</label>
                <input type="text" name="title" value="{{ old('title', $article->title ?? '') }}" maxlength="200" required>
              </div>

              <div class="field">
                <label>Category</label>
                <select name="category_id" required>
                  @foreach ($categories as $c)
                    <option value="{{ $c->category_id }}" @selected((int) old('category_id', $article->category_id ?? 0) === (int) $c->category_id)>
                      {{ $c->name }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="field">
                <label>Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $article->slug ?? '') }}" maxlength="200" placeholder="auto-generated if empty">
              </div>

              <div class="field">
                <label>Summary</label>
                <input type="text" name="summary" value="{{ old('summary', $article->summary ?? '') }}" maxlength="500" placeholder="Optional">
              </div>

              <div class="field checkbox">
                <label>
                  <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', !empty($article?->is_featured)))>
                  Featured
                </label>
              </div>

              <div class="field checkbox">
                <label>
                  <input type="checkbox" name="is_published" value="1" @checked(old('is_published', !empty($article?->is_published)))>
                  Published
                </label>
              </div>
            </div>

            <div class="field">
              <label>Content (HTML)</label>
              <textarea name="content_html" rows="16" required>{{ old('content_html', $article->content_html ?? '') }}</textarea>
              <div class="muted small">Tip: use sections like <code>&lt;section class="steps"&gt;</code> to match the article styling.</div>
            </div>

            <div class="kb-editor-actions">
              <button class="btn-primary" type="submit"><i class='bx bx-save'></i> Save</button>
              <a class="btn-outlined" href="{{ route('knowledge.manage') }}">Cancel</a>
            </div>
          </form>
        </div>
      </section>
    </main>
  </div>

  <script src="{{ asset('assets/js/components/sidebar.js') }}"></script>
</body>
</html>
