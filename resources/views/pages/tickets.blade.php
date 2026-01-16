<!DOCTYPE html>
<html lang="en-US">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>ITicket - My Tickets</title>
 
  <!-- Global/base (reset, utilities, shared patterns) -->
  <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
  <!-- Shared component styles -->
  <link rel="stylesheet" href="{{ asset('assets/css/components/topbar.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/components/sidebar.css') }}">
  <!-- Page styles -->
  <link rel="stylesheet" href="{{ asset('assets/css/pages/tickets.css') }}">
  
  <!-- Icons -->
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
  <div class="page">

    <!-- Topbar -->
    <header class="topbar">
      <div class="topbar-left">
        <button class="menu-button">
          <i class='bx bx-menu'></i>
        </button>
      </div>
      
      <div class="topbar-right">
        <button class="notif-button">
          <i class='bx bx-bell'></i>
        </button>

        @include('partials.profile-chip')
      </div>
    </header>

    <!-- Slide-out Sidebar -->
    @include('partials.sidebar')
    <!-- Overlay backdrop -->
    <div class="backdrop"></div>

    
    <main class="content">
      <!-- Header + CTA -->
      <header class="page-header">
        <div class="page-header-left">
            @php
              $role = strtolower((string) (auth()->user()?->role ?? 'user'));
              $is_staff = in_array($role, ['admin', 'it'], true);
              $view = $view ?? ($is_staff ? strtolower((string) request()->query('view', 'queue')) : 'my');
              if (!$is_staff) $view = 'my';

              $title = 'My Tickets';
              $subtitle = 'View and manage all your submitted tickets';
              if ($is_staff) {
                if ($view === 'all') {
                  $title = 'All Tickets';
                  $subtitle = 'View and manage all submitted tickets';
                } elseif ($view === 'mine') {
                  $title = 'My Assigned Tickets';
                  $subtitle = 'Tickets currently assigned to you';
                } else {
                  $title = 'Ticket Queue';
                  $subtitle = 'Unassigned tickets plus tickets assigned to you';
                }
              }
            @endphp
            <h2>{{ $title }}</h2>
            <p class="muted">{{ $subtitle }}</p>

            @if ($is_staff)
              <nav class="ticket-tabs" aria-label="Ticket views">
                <a @class(['ticket-tab', 'is-active' => $view === 'queue']) href="{{ route('tickets.index', ['view' => 'queue']) }}">Queue</a>
                <a @class(['ticket-tab', 'is-active' => $view === 'mine']) href="{{ route('tickets.index', ['view' => 'mine']) }}">Mine</a>
                <a @class(['ticket-tab', 'is-active' => $view === 'all']) href="{{ route('tickets.index', ['view' => 'all']) }}">All</a>
              </nav>
            @endif
        </div>
        <div class="page-header-actions">
          <a class="btn-primary header-cta" href="{{ route('tickets.create') }}"><i class='bx bx-plus'></i> New Ticket</a>
        </div>
      </header>

      @if (session('status'))
        <div class="panel" role="status" style="border:1px solid #d1fae5;background:#ecfdf5;color:#065f46;">
          {{ session('status') }}
        </div>
      @endif

      <!-- Ticket Counters -->
      <section class="counter">
        <div class="counter-card counter-total">
          <div class="counter-value">{{ $counts['total'] ?? 0 }}</div>
          <div class="counter-label">Total Tickets</div>
        </div>
        <div class="counter-card counter-open">
          <div class="counter-value">{{ $counts['open'] ?? 0 }}</div>
          <div class="counter-label">Open Tickets</div>
        </div>
        <div class="counter-card counter-progress">
          <div class="counter-value">{{ $counts['progress'] ?? 0 }}</div>
          <div class="counter-label">In Progress</div>
        </div>
        <div class="counter-card counter-resolved">
          <div class="counter-value">{{ $counts['resolved'] ?? 0 }}</div>
          <div class="counter-label">Resolved</div>
        </div>
      </section>

      <!-- Filter bar (search + pill selects) -->
      <section class="filters">
        <div class="filter-bar">
          <div class="searchbar">
            <i class='bx bx-search'></i>
            <input id="ticketSearch" type="text" placeholder="Search tickets by ID, subject, or keyword" autocomplete="off">
            <button id="ticketSearchClear" type="button" class="btn-clear" title="Clear"><i class='bx bx-x'></i></button>
          </div>

          <div class="select-row">
            <div class="select-pill">
              <select id="statusFilter" aria-label="Status">
                <option value="">All Status</option>
                <option value="open">Open</option>
                <option value="in_progress">In Progress</option>
                <option value="resolved">Resolved</option>
                <option value="closed">Closed</option>
              </select>
              <i class='bx bx-chevron-down'></i>
            </div>

            <div class="select-pill">
              <select id="priorityFilter" aria-label="Priority">
                <option value="">All Priority</option>
                <option value="High">High</option>
                <option value="Medium">Medium</option>
                <option value="Low">Low</option>
              </select>
              <i class='bx bx-chevron-down'></i>
            </div>

            <div class="select-pill">
              <select id="sortFilter" aria-label="Sort">
                <option value="latest">Sort: Latest</option>
                <option value="oldest">Sort: Oldest</option>
              </select>
              <i class='bx bx-chevron-down'></i>
            </div>
          </div>
        </div>
      </section>

      <!-- Tickets Panel -->
      <section class="panel tickets-panel">
        <div class="panel-head">
          <div class="results-left">
            <strong id="resultsCount">Showing {{ isset($tickets) ? $tickets->count() : 0 }} Tickets</strong>
          </div>
          <div class="pager">
            <button id="pagerPrevTop" class="btn-pager" type="button" disabled><i class='bx bx-chevron-left'></i> Prev</button>
            <span id="pageInfoTop" class="page-info">1 - 1</span>
            <button id="pagerNextTop" class="btn-pager" type="button" disabled>Next <i class='bx bx-chevron-right'></i></button>
          </div>
        </div>

        <div class="panel-body">
          <div class="tickets-list">
            @if (isset($tickets) && $tickets->count() > 0)
              @foreach ($tickets as $t)
                @php
                  $displayId = '#TKT-' . $t->ticket_id;

                  $statusLabel = 'Open';
                  if ($t->status === 'in_progress') $statusLabel = 'In Progress';
                  if ($t->status === 'resolved') $statusLabel = 'Resolved';
                  if ($t->status === 'closed') $statusLabel = 'Closed';

                  $priorityLabel = $t->priority . ' Priority';

                  $assigneeName = '';
                  if (!empty($t->assignee_first_name)) {
                    $assigneeName = trim(($t->assignee_first_name ?? '').' '.($t->assignee_last_name ?? ''));
                  }

                  $searchBlob = strtolower(trim(
                    implode(' ', [
                      $displayId,
                      (string) ($t->subject ?? ''),
                      (string) ($t->description ?? ''),
                      (string) ($t->category ?? ''),
                      (string) ($assigneeName !== '' ? $assigneeName : ''),
                    ])
                  ));
                @endphp

                <article class="ticket-card"
                  data-ticket-id="{{ $t->ticket_id }}"
                  data-status="{{ $t->status }}"
                  data-priority="{{ $t->priority }}"
                  data-created-ts="{{ $t->created_at?->timestamp ?? 0 }}"
                  data-search="{{ $searchBlob }}">
                  <header class="tcard-head">
                    <a class="ticket-id" href="{{ route('tickets.show', $t->ticket_id) }}">{{ $displayId }}</a>
                    <div class="badges">
                      <span @class([
                        'chip',
                        'chip-high' => $t->priority === 'High',
                        'chip-medium' => $t->priority === 'Medium',
                        'chip-low' => $t->priority === 'Low',
                      ])>{{ $priorityLabel }}</span>
                      <span @class([
                        'status',
                        'status-open' => $t->status === 'open',
                        'status-progress' => $t->status === 'in_progress',
                        'status-resolved' => $t->status === 'resolved',
                        'status-closed' => $t->status === 'closed',
                      ])>{{ $statusLabel }}</span>
                    </div>
                  </header>
                  <div class="tcard-body">
                    <h4 class="t-subject">{{ $t->subject }}</h4>
                    <p class="t-desc">{{ $t->description }}</p>
                  </div>
                  <footer class="tcard-foot">
                    <span class="meta">{{ optional($t->created_at)->diffForHumans() }}</span>
                    <span class="dot">•</span>
                    <span class="meta">
                      {{ $assigneeName !== '' ? $assigneeName : ($t->assigned_to ? 'User #'.$t->assigned_to : 'Unassigned') }}
                    </span>
                    <span class="dot">•</span>
                    <span class="meta">Category: {{ $t->category }}</span>
                    <div class="row-actions">
                      <a class="action" href="{{ route('tickets.show', $t->ticket_id) }}" title="View"><i class='bx bx-show'></i></a>
                    </div>
                  </footer>
                </article>
              @endforeach
            @endif
          </div>
        </div>

        <div class="panel-foot">
          <div class="pager">
            <button id="pagerPrevBottom" class="btn-pager" type="button" disabled><i class='bx bx-chevron-left'></i> Prev</button>
            <span id="pageInfoBottom" class="page-info">1 - 1</span>
            <button id="pagerNextBottom" class="btn-pager" type="button" disabled>Next <i class='bx bx-chevron-right'></i></button>
          </div>
        </div>
      </section>

      <!-- Empty state (hidden when there are cards) -->
      <section class="empty-state" @if (isset($tickets) && $tickets->count() > 0) hidden @endif>
        <div class="empty-card">
          <i class='bx bx-folder-open'></i>
          <h3>No tickets found</h3>
          <p class="muted">Try adjusting filters or create a new ticket.</p>
          <a class="btn-primary" href="{{ route('tickets.create') }}"><i class='bx bx-plus'></i> Create Ticket</a>
        </div>
      </section>
    </main>

  </div>

  <script src="{{ asset('assets/js/components/sidebar.js') }}"></script>
  <script src="{{ asset('assets/js/pages/tickets.js') }}"></script>
</body>
</html>