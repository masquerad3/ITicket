<!DOCTYPE html>
<html lang="en-US">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>ITicket - Dashboard</title>
 
  <!-- Global/base -->
  <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
  <!-- Shared component styles -->
  <link rel="stylesheet" href="{{ asset('assets/css/components/topbar.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/components/sidebar.css') }}">
  <!-- Page styles -->
  <link rel="stylesheet" href="{{ asset('assets/css/pages/dashboard.css') }}">
  
  <!-- Icons -->
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
  @php
    $u = $user ?? auth()->user();
    $name = trim(($u->first_name ?? '').' '.($u->last_name ?? ''));
    if ($name === '') $name = 'there';

    $counts = $counts ?? ['total' => 0, 'open' => 0, 'progress' => 0, 'resolved' => 0];
    $recentTickets = $recentTickets ?? collect();
    if (!($recentTickets instanceof \Illuminate\Support\Collection)) $recentTickets = collect($recentTickets);

    $unassignedTickets = $unassignedTickets ?? collect();
    if (!($unassignedTickets instanceof \Illuminate\Support\Collection)) $unassignedTickets = collect($unassignedTickets);

    $assignedToMeTickets = $assignedToMeTickets ?? collect();
    if (!($assignedToMeTickets instanceof \Illuminate\Support\Collection)) $assignedToMeTickets = collect($assignedToMeTickets);

    $isStaff = (bool) ($is_staff ?? false);

    $statusLabel = function ($raw) {
      $s = strtolower(trim((string) $raw));
      if ($s === 'in progress' || $s === 'in_progress' || $s === 'progress') return 'In Progress';
      if ($s === 'resolved' || $s === 'closed') return 'Resolved';
      return 'Open';
    };

    $statusClass = function ($raw) {
      $s = strtolower(trim((string) $raw));
      if ($s === 'in progress' || $s === 'in_progress' || $s === 'progress') return 'badge-progress';
      if ($s === 'resolved' || $s === 'closed') return 'badge-resolved';
      return 'badge-open';
    };

    $priorityClass = function ($raw) {
      $p = strtolower(trim((string) $raw));
      if ($p === 'high') return 'badge-priority-high';
      if ($p === 'low') return 'badge-priority-low';
      return 'badge-priority-medium';
    };

    $formatWhen = function ($value) {
      if ($value instanceof \Carbon\CarbonInterface) return $value->diffForHumans();
      if (is_string($value) && trim($value) !== '') return $value;
      return '';
    };

    $aging = $aging ?? ['open_older_24h' => 0, 'open_older_3d' => 0];
    $priorityBreakdown = $priorityBreakdown ?? ['high' => 0, 'medium' => 0, 'low' => 0];
    $topCategories = $topCategories ?? collect();
    if (!($topCategories instanceof \Illuminate\Support\Collection)) $topCategories = collect($topCategories);
  @endphp
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
      <!--  Welcome Banner -->
      <div class="welcome-banner">
        <div class="banner-text">
          <h2>Welcome back, <strong>{{ $name }}</strong>!</h2>
          @if ($isStaff)
            <p>Here’s what needs attention today. Triage, assign, and resolve tickets faster.</p>
          @else
            <p>Need help with an IT issue? Submit a ticket and we'll get right on it.</p>
          @endif
        </div>
        @if ($isStaff)
          <a class="new-ticket-btn" href="{{ route('tickets.index') }}">
            <i class='bx bx-list-check'></i>
            <p>Go to Tickets</p>
          </a>
        @else
          <a class="new-ticket-btn" href="{{ route('tickets.create') }}">
            <i class='bx bx-plus'></i>
            <p>Create New Ticket</p>
          </a>
        @endif
      </div>

      <!-- Ticket Counters -->
      <section class="counter">
        @if ($isStaff)
          <div class="counter-card counter-unassigned">
            <div class="counter-value">{{ (int) $unassignedTickets->count() }}</div>
            <div class="counter-label">Unassigned</div>
          </div>
          <div class="counter-card counter-mine">
            <div class="counter-value">{{ (int) $assignedToMeTickets->count() }}</div>
            <div class="counter-label">Assigned to Me</div>
          </div>
          <div class="counter-card counter-open">
            <div class="counter-value">{{ (int) ($counts['open'] ?? 0) }}</div>
            <div class="counter-label">Open</div>
          </div>
          <div class="counter-card counter-resolved">
            <div class="counter-value">{{ (int) ($counts['resolved'] ?? 0) }}</div>
            <div class="counter-label">Resolved</div>
          </div>
        @else
          <div class="counter-card counter-total">
            <div class="counter-value">{{ (int) ($counts['total'] ?? 0) }}</div>
            <div class="counter-label">Total Tickets</div>
          </div>
          <div class="counter-card counter-open">
            <div class="counter-value">{{ (int) ($counts['open'] ?? 0) }}</div>
            <div class="counter-label">Open Tickets</div>
          </div>
          <div class="counter-card counter-progress">
            <div class="counter-value">{{ (int) ($counts['progress'] ?? 0) }}</div>
            <div class="counter-label">In Progress</div>
          </div>
          <div class="counter-card counter-resolved">
            <div class="counter-value">{{ (int) ($counts['resolved'] ?? 0) }}</div>
            <div class="counter-label">Resolved</div>
          </div>
        @endif
      </section>

      <div class="dashboard-grid">
        @if ($isStaff)
          <!-- Work Overview (Staff) -->
          <section class="panel">
            <div class="panel-header">
              <h3>Work Overview</h3>
              <a href="{{ route('tickets.index') }}">View All</a>
            </div>

            <div class="split-panels">
              <div class="mini-panel">
                <div class="mini-header">
                  <h4>Unassigned</h4>
                  <span class="mini-count">{{ $unassignedTickets->count() }}</span>
                </div>

                @if ($unassignedTickets->count() === 0)
                  <p class="muted">No unassigned tickets right now.</p>
                @else
                  @foreach ($unassignedTickets->take(2) as $t)
                    @php
                      $desc = (string) ($t->description ?? '');
                      if (strlen($desc) > 120) $desc = substr($desc, 0, 117).'...';
                      $when = $formatWhen($t->created_at ?? null);
                      $reqName = trim((string) (($t->requester_first_name ?? '').' '.($t->requester_last_name ?? '')));
                    @endphp
                    <div class="ticket-card">
                      <div class="ticket-header">
                        <a class="ticket-id" href="{{ route('tickets.show', ['ticket' => $t->ticket_id]) }}">#TKT-{{ $t->ticket_id }}</a>
                        <div class="ticket-badges">
                          <span class="badge {{ $statusClass($t->status ?? null) }}">{{ $statusLabel($t->status ?? null) }}</span>
                          <span class="badge {{ $priorityClass($t->priority ?? null) }}">{{ ucfirst((string) ($t->priority ?? '')) ?: 'Priority' }}</span>
                        </div>
                      </div>
                      <div class="ticket-body">
                        <h4 class="ticket-title">{{ $t->subject ?? 'Ticket' }}</h4>
                        <p class="ticket-description">{{ $desc }}</p>
                        <div class="ticket-meta">
                          @if ($reqName !== '')
                            <span class="meta-pill"><i class='bx bx-user'></i> {{ $reqName }}</span>
                          @endif
                          @if ($when !== '')
                            <span class="meta-pill"><i class='bx bx-time-five'></i> {{ $when }}</span>
                          @endif
                        </div>
                      </div>
                    </div>
                  @endforeach
                @endif
              </div>

              <div class="mini-panel">
                <div class="mini-header">
                  <h4>Assigned to Me</h4>
                  <span class="mini-count">{{ $assignedToMeTickets->count() }}</span>
                </div>

                @if ($assignedToMeTickets->count() === 0)
                  <p class="muted">Nothing assigned to you yet.</p>
                @else
                  @foreach ($assignedToMeTickets->take(2) as $t)
                    @php
                      $desc = (string) ($t->description ?? '');
                      if (strlen($desc) > 120) $desc = substr($desc, 0, 117).'...';
                      $when = $formatWhen($t->updated_at ?? ($t->created_at ?? null));
                      $reqName = trim((string) (($t->requester_first_name ?? '').' '.($t->requester_last_name ?? '')));
                    @endphp
                    <div class="ticket-card">
                      <div class="ticket-header">
                        <a class="ticket-id" href="{{ route('tickets.show', ['ticket' => $t->ticket_id]) }}">#TKT-{{ $t->ticket_id }}</a>
                        <div class="ticket-badges">
                          <span class="badge {{ $statusClass($t->status ?? null) }}">{{ $statusLabel($t->status ?? null) }}</span>
                          <span class="badge {{ $priorityClass($t->priority ?? null) }}">{{ ucfirst((string) ($t->priority ?? '')) ?: 'Priority' }}</span>
                        </div>
                      </div>
                      <div class="ticket-body">
                        <h4 class="ticket-title">{{ $t->subject ?? 'Ticket' }}</h4>
                        <p class="ticket-description">{{ $desc }}</p>
                        <div class="ticket-meta">
                          @if ($reqName !== '')
                            <span class="meta-pill"><i class='bx bx-user'></i> {{ $reqName }}</span>
                          @endif
                          @if ($when !== '')
                            <span class="meta-pill"><i class='bx bx-refresh'></i> Updated {{ $when }}</span>
                          @endif
                        </div>
                      </div>
                    </div>
                  @endforeach
                @endif
              </div>
            </div>
          </section>
        @endif

        <!-- Insights -->
        <section class="panel">
          <div class="panel-header">
            <h3>Insights</h3>
          </div>

          <div class="insights-grid">
            <div class="insight-card">
              <div class="insight-title">SLA / Aging</div>
              <div class="insight-rows">
                <div class="insight-row">
                  <span class="label">Open &gt; 24 hours</span>
                  <span class="value">{{ (int) ($aging['open_older_24h'] ?? 0) }}</span>
                </div>
                <div class="insight-row">
                  <span class="label">Open &gt; 3 days</span>
                  <span class="value">{{ (int) ($aging['open_older_3d'] ?? 0) }}</span>
                </div>
              </div>
            </div>

            <div class="insight-card">
              <div class="insight-title">Priority Breakdown</div>
              <div class="insight-rows">
                <div class="insight-row">
                  <span class="label">High</span>
                  <span class="value">{{ (int) ($priorityBreakdown['high'] ?? 0) }}</span>
                </div>
                <div class="insight-row">
                  <span class="label">Medium</span>
                  <span class="value">{{ (int) ($priorityBreakdown['medium'] ?? 0) }}</span>
                </div>
                <div class="insight-row">
                  <span class="label">Low</span>
                  <span class="value">{{ (int) ($priorityBreakdown['low'] ?? 0) }}</span>
                </div>
              </div>
            </div>

            <div class="insight-card">
              <div class="insight-title">Top Categories</div>
              @if ($topCategories->count() === 0)
                <p class="muted">No category data yet.</p>
              @else
                <div class="insight-rows">
                  @foreach ($topCategories as $cat => $cnt)
                    <div class="insight-row">
                      <span class="label">{{ $cat }}</span>
                      <span class="value">{{ (int) $cnt }}</span>
                    </div>
                  @endforeach
                </div>
              @endif
            </div>
          </div>
        </section>

        @if (!$isStaff)
          <!-- Recent Tickets (Requesters) -->
          <section class="panel">
            <div class="panel-header">
              <h3>My Recent Tickets</h3>
              <a href="{{ route('tickets.index') }}">View All</a>
            </div>

            @if ($recentTickets->count() === 0)
              <p class="muted">No tickets yet.</p>
            @else
              @foreach ($recentTickets as $t)
                @php
                  $desc = (string) ($t->description ?? '');
                  if (strlen($desc) > 140) $desc = substr($desc, 0, 137).'...';
                  $when = $formatWhen($t->created_at ?? null);
                  $assName = trim((string) (($t->assignee_first_name ?? '').' '.($t->assignee_last_name ?? '')));
                @endphp
                <div class="ticket-card">
                  <div class="ticket-header">
                    <a class="ticket-id" href="{{ route('tickets.show', ['ticket' => $t->ticket_id]) }}">#TKT-{{ $t->ticket_id }}</a>
                    <div class="ticket-badges">
                      <span class="badge {{ $statusClass($t->status ?? null) }}">{{ $statusLabel($t->status ?? null) }}</span>
                      <span class="badge {{ $priorityClass($t->priority ?? null) }}">{{ ucfirst((string) ($t->priority ?? '')) ?: 'Priority' }}</span>
                    </div>
                  </div>
                  <div class="ticket-body">
                    <h4 class="ticket-title">{{ $t->subject ?? 'Ticket' }}</h4>
                    <p class="ticket-description">{{ $desc }}</p>
                    <div class="ticket-meta">
                      @if ($assName !== '')
                        <span class="meta-pill"><i class='bx bx-user-check'></i> {{ $assName }}</span>
                      @endif
                      @if ($when !== '')
                        <span class="meta-pill"><i class='bx bx-time-five'></i> {{ $when }}</span>
                      @endif
                    </div>
                  </div>
                </div>
              @endforeach
            @endif
          </section>
        @endif
      </div>
      
    </main>

  </div>

  <script src="{{ asset('assets/js/components/sidebar.js') }}"></script>
</body>
</html>