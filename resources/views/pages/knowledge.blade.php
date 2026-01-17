<!DOCTYPE html>
<html lang="en-US">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ITicket - Knowledge Base</title>

  <!-- Global/base -->
  <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
  <!-- Shared components -->
  <link rel="stylesheet" href="{{ asset('assets/css/components/topbar.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/components/sidebar.css') }}">
  <!-- Page-specific -->
  <link rel="stylesheet" href="{{ asset('assets/css/pages/knowledge.css') }}">

  <!-- Icons -->
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
  @php
    use Illuminate\Support\Str;

    $catColors = ['cat--blue', 'cat--indigo', 'cat--teal', 'cat--green', 'cat--orange', 'cat--pink'];
    $catIcons = ['bx bx-user-check', 'bx bx-envelope', 'bx bx-plug', 'bx bx-desktop', 'bx bx-paint', 'bx bx-shield-quarter'];
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
      <!-- Colorful hero with search -->
      <section class="kb-hero">
        <div class="hero-inner">
          <div class="hero-text">
            <h2>Knowledge Base</h2>
            <p class="muted-light">Search guides and troubleshooting articles.</p>
          </div>
          @php
            $role = strtolower((string) (auth()->user()?->role ?? 'user'));
            $is_staff = in_array($role, ['admin', 'it'], true);
          @endphp

          <div class="hero-actions">
            <a class="hero-cta" href="{{ route('tickets.create') }}">
              <i class='bx bx-plus'></i> Create Ticket
            </a>

            @if ($is_staff)
              <a class="hero-cta hero-cta-secondary" href="{{ route('knowledge.manage') }}">
                <i class='bx bx-edit'></i> Manage Articles
              </a>
            @endif
          </div>

          <form method="GET" action="{{ route('knowledge') }}" class="searchbar kb-search">
            <i class='bx bx-search'></i>
            <input
              type="text"
              name="q"
              value="{{ $q ?? '' }}"
              placeholder="Search articles (e.g., reset password, VPN, Outlook)"
            >
            @if (!empty($category_id))
              <input type="hidden" name="category" value="{{ $category_id }}">
            @endif
            <button type="button" class="btn-clear" title="Clear"><i class='bx bx-x'></i></button>
          </form>
        </div>
      </section>

      <!-- Categories with LEFT accent bar -->
      <section class="categories">
        @foreach (collect($categories ?? []) as $i => $cat)
          @php
            $colorClass = $catColors[$i % count($catColors)];
            $iconClass = $catIcons[$i % count($catIcons)];
            $count = (int) ($cat->article_count ?? 0);
          @endphp
          <article class="cat-card {{ $colorClass }}">
            <div class="cat-icon"><i class='{{ $iconClass }}'></i></div>
            <div class="cat-body">
              <h3>{{ $cat->name }}</h3>
              <p>{{ $cat->description ?: 'Browse helpful guides and troubleshooting articles.' }}</p>
            </div>
            <a class="cat-link" href="{{ route('knowledge', ['category' => $cat->category_id]) }}">View {{ $count }} {{ $count === 1 ? 'article' : 'articles' }}</a>
          </article>
        @endforeach
      </section>

      <!-- Article panels -->
      <section class="kb-grid">
        @if (!empty($q) || !empty($category_id))
          <section class="panel kb-panel">
            <div class="panel-head">
              <h3>
                @if (!empty($q))
                  Search Results
                @elseif (!empty($activeCategory))
                  {{ $activeCategory->name }} Articles
                @else
                  Articles
                @endif
              </h3>
              <a class="btn-outlined small" href="{{ route('knowledge') }}">Clear</a>
            </div>
            <div class="panel-body articles">
              @forelse ($results as $a)
                <article class="article-row">
                  <div class="art-left">
                    <i class='bx bx-file'></i>
                    <a class="art-title" href="{{ route('knowledge.show', ['slug' => $a->slug]) }}">{{ $a->title }}</a>
                    <p class="art-snippet">{{ $a->summary ?: Str::limit(strip_tags((string) ($a->content_html ?? '')), 120) }}</p>
                    <div class="art-tags">
                      <span class="chip chip-low">{{ $a->category_name ?? 'General' }}</span>
                      @if (!empty($a->is_featured))
                        <span class="chip chip-medium">Featured</span>
                      @endif
                    </div>
                  </div>
                  <div class="art-right">
                    <span class="meta">
                      {{ ($a->updated_at ?? null) instanceof \Illuminate\Support\Carbon ? $a->updated_at->diffForHumans() : 'Updated recently' }}
                    </span>
                  </div>
                </article>
              @empty
                <div class="muted">No articles found. Try a different search.</div>
              @endforelse
            </div>
          </section>
        @else
          <!-- Featured -->
          <section class="panel kb-panel">
            <div class="panel-head">
              <h3>Featured Articles</h3>
              <a class="btn-outlined small" href="{{ route('knowledge') }}">See all</a>
            </div>
            <div class="panel-body articles">
              @forelse ($featured as $a)
                <article class="article-row">
                  <div class="art-left">
                    <i class='bx bxs-star'></i>
                    <a class="art-title" href="{{ route('knowledge.show', ['slug' => $a->slug]) }}">{{ $a->title }}</a>
                    <p class="art-snippet">{{ $a->summary ?: Str::limit(strip_tags((string) ($a->content_html ?? '')), 120) }}</p>
                    <div class="art-tags">
                      <span class="chip chip-low">{{ $a->category_name ?? 'General' }}</span>
                    </div>
                  </div>
                  <div class="art-right">
                    <span class="meta">
                      {{ ($a->updated_at ?? null) instanceof \Illuminate\Support\Carbon ? $a->updated_at->diffForHumans() : 'Updated recently' }}
                    </span>
                  </div>
                </article>
              @empty
                <div class="muted">No featured articles yet.</div>
              @endforelse
            </div>
          </section>

          <!-- Latest -->
          <section class="panel kb-panel">
            <div class="panel-head">
              <h3>Latest Articles</h3>
              <a class="btn-outlined small" href="{{ route('knowledge') }}">See all</a>
            </div>
            <div class="panel-body articles">
              @forelse ($latest as $a)
                <article class="article-row">
                  <div class="art-left">
                    <i class='bx bx-file'></i>
                    <a class="art-title" href="{{ route('knowledge.show', ['slug' => $a->slug]) }}">{{ $a->title }}</a>
                    <p class="art-snippet">{{ $a->summary ?: Str::limit(strip_tags((string) ($a->content_html ?? '')), 120) }}</p>
                  </div>
                  <div class="art-right">
                    <span class="meta">
                      {{ ($a->updated_at ?? null) instanceof \Illuminate\Support\Carbon ? $a->updated_at->diffForHumans() : 'Updated recently' }}
                    </span>
                  </div>
                </article>
              @empty
                <div class="muted">No articles yet.</div>
              @endforelse
            </div>
          </section>
        @endif
      </section>
    </main>
  </div>

  <script src="{{ asset('assets/js/components/sidebar.js') }}"></script>
  <script src="{{ asset('assets/js/pages/knowledge.js') }}"></script>
</body>
</html>