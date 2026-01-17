<!DOCTYPE html>
<html lang="en-US">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ITicket - {{ $article->title ?? 'Knowledge Article' }}</title>

  <!-- Global/base -->
  <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
  <!-- Shared components -->
  <link rel="stylesheet" href="{{ asset('assets/css/components/topbar.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/components/sidebar.css') }}">
  <!-- Article page styles -->
  <link rel="stylesheet" href="{{ asset('assets/css/pages/knowledge-article.css') }}">

  <!-- Icons -->
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
  @php
    $updated = ($article->updated_at ?? null) instanceof \Illuminate\Support\Carbon
      ? $article->updated_at
      : null;
    $author = trim(((string) ($article->author_first_name ?? '')) . ' ' . ((string) ($article->author_last_name ?? '')));
    if ($author === '') {
      $author = 'IT Support';
    }

    $text = strip_tags((string) ($article->content_html ?? ''));
    $words = str_word_count($text);
    $readingMinutes = max(1, (int) ceil($words / 200));
    $articleCode = 'KB-' . str_pad((string) ($article->article_id ?? 0), 4, '0', STR_PAD_LEFT);
  @endphp
  <div class="page">
    <!-- Topbar -->
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

    <!-- Sidebar -->
    @include('partials.sidebar')
    <div class="backdrop" aria-hidden="true"></div>

    <!-- Content -->
    <main class="content">
      <!-- Breadcrumb -->
      <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('knowledge') }}"><i class='bx bx-book'></i> Knowledge Base</a>
        <i class='bx bx-chevron-right'></i>
        <a href="#" aria-current="page">{{ $article->title }}</a>
      </nav>

      <!-- Article header -->
      <header class="article-head">
        <h1>{{ $article->title }}</h1>
        <div class="meta-row">
          <span class="meta"><i class='bx bx-time-five'></i> Updated: {{ $updated ? $updated->toDateString() : 'N/A' }}</span>
          <span class="meta"><i class='bx bx-user'></i> Author: {{ $author }}</span>
          <span class="meta"><i class='bx bx-bookmark'></i> Article ID: {{ $articleCode }}</span>
          <span class="meta"><i class='bx bx-stopwatch'></i> Reading time: {{ $readingMinutes }} min</span>
        </div>
        @if (!empty($article->summary))
          <p class="summary">{{ $article->summary }}</p>
        @endif
        <div class="chips">
          @if (!empty($article->category_name))
            <span class="chip">{{ $article->category_name }}</span>
          @endif
          @if (!empty($article->is_featured))
            <span class="chip">Featured</span>
          @endif
          <span class="chip chip-soft">Views: {{ (int) ($article->view_count ?? 0) }}</span>
        </div>
      </header>

      {!! $article->content_html !!}

      <!-- Feedback + CTA -->
      <section class="feedback">
        <div class="feedback-box">
          <p>Was this article helpful?</p>
          <div class="feedback-actions">
            <button class="btn-outlined" id="fbYes"><i class='bx bx-like'></i> Yes</button>
            <button class="btn-outlined" id="fbNo"><i class='bx bx-dislike'></i> No</button>
            <a class="btn-primary" href="{{ route('tickets.create') }}"><i class='bx bx-help-circle'></i> Still need help? Create a ticket</a>
          </div>
          <p class="muted small" id="fbMsg" aria-live="polite"></p>
        </div>
      </section>
    </main>
  </div>

  <script src="{{ asset('assets/js/components/sidebar.js') }}"></script>
  <script src="{{ asset('assets/js/pages/kb-article.js') }}"></script>
</body>
</html>