<!DOCTYPE html>
<html lang="en-US">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ITicket - Ticket #TKT-{{ $ticket->ticket_id ?? '' }}</title>

  <!-- Global/base -->
  <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
  <!-- Shared components -->
  <link rel="stylesheet" href="{{ asset('assets/css/components/topbar.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/components/sidebar.css') }}">
  <!-- Page-specific (refreshed look) -->
  <link rel="stylesheet" href="{{ asset('assets/css/pages/ticket.css') }}">

  <!-- Icons -->
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
  @php
    $u = auth()->user();

    $role = strtolower((string) ($u?->role ?? 'user'));
    $is_staff = in_array($role, ['admin', 'it'], true);

    $requesterFirst = $ticket->requester_first_name ?? '';
    $requesterLast = $ticket->requester_last_name ?? '';
    if ($requesterFirst === '' && $u !== null) $requesterFirst = $u->first_name ?? '';
    if ($requesterLast === '' && $u !== null) $requesterLast = $u->last_name ?? '';

    $assigneeFirst = $ticket->assignee_first_name ?? '';
    $assigneeLast = $ticket->assignee_last_name ?? '';

    $first = '';
    $last = '';
    if (!empty($requesterFirst)) $first = strtoupper(substr($requesterFirst, 0, 1));
    if (!empty($requesterLast)) $last = strtoupper(substr($requesterLast, 0, 1));
    $initials = $first . $last;
    if ($initials === '') $initials = 'U';

    $assigneeInitials = '—';
    if (!empty($assigneeFirst) || !empty($assigneeLast)) {
      $af = !empty($assigneeFirst) ? strtoupper(substr($assigneeFirst, 0, 1)) : '';
      $al = !empty($assigneeLast) ? strtoupper(substr($assigneeLast, 0, 1)) : '';
      $assigneeInitials = trim($af . $al) !== '' ? ($af . $al) : 'IT';
    }

    $displayId = '#TKT-' . ($ticket->ticket_id ?? '');

    $statusLabel = 'Open';
    if (($ticket->status ?? '') === 'in_progress') $statusLabel = 'In Progress';
    if (($ticket->status ?? '') === 'resolved') $statusLabel = 'Resolved';
    if (($ticket->status ?? '') === 'closed') $statusLabel = 'Closed';

    $attachments = $ticket->attachments ?? [];
    if (!is_array($attachments)) $attachments = [];

    $messages = $messages ?? collect();
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
        <button class="notif-button"><i class='bx bx-bell'></i></button>
        @include('partials.profile-chip')
      </div>
    </header>

    <!-- Sidebar -->
    @include('partials.sidebar')
    <div class="backdrop"></div>

    <!-- Content -->
    <main class="content">
      <!-- Header band -->
      <section class="ticket-toolbar">
        <div class="tt-left">
          <h1>{{ $ticket->subject }}</h1>
          <div class="tt-meta">
            <span class="ticket-id">{{ $displayId }}</span>
            <span @class([
              'status',
              'status-open' => ($ticket->status ?? '') === 'open',
              'status-progress' => ($ticket->status ?? '') === 'in_progress',
              'status-resolved' => ($ticket->status ?? '') === 'resolved',
              'status-closed' => ($ticket->status ?? '') === 'closed',
            ])>{{ $statusLabel }}</span>
            <span @class([
              'chip',
              'chip-high' => ($ticket->priority ?? '') === 'High',
              'chip-medium' => ($ticket->priority ?? '') === 'Medium',
              'chip-low' => ($ticket->priority ?? '') === 'Low',
            ])>{{ $ticket->priority }}</span>
          </div>
        </div>
        <div class="tt-actions">
          <a class="btn-outlined" href="{{ route('tickets.index') }}"><i class='bx bx-left-arrow-alt'></i> Back</a>

          @if ($is_staff)
            @if (($ticket->assigned_to ?? null) === null)
              <form method="POST" action="{{ route('tickets.assignToMe', $ticket->ticket_id) }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn-soft"><i class='bx bx-user-check'></i> Assign to me</button>
              </form>
            @endif

            <form method="POST" action="{{ route('tickets.updateStatus', $ticket->ticket_id) }}" style="display:inline;">
              @csrf
              @method('PATCH')
              <input type="hidden" name="status" value="resolved">
              <button type="submit" class="btn-soft"><i class='bx bx-check-circle'></i> Mark Resolved</button>
            </form>
          @endif

          <button id="replyScroll" class="btn-primary" type="button"><i class='bx bx-reply'></i> Add Reply</button>
        </div>
      </section>

      @if (session('status'))
        <div class="panel" role="status" style="border:1px solid #d1fae5;background:#ecfdf5;color:#065f46;">
          {{ session('status') }}
        </div>
      @endif

      <!-- Info pills -->
      <section class="pill-row">
        <div class="pill"><i class='bx bx-time-five'></i> Created {{ optional($ticket->created_at)->format('Y-m-d H:i') }}</div>
        <div class="pill"><i class='bx bx-sync'></i> Updated {{ optional($ticket->updated_at)->diffForHumans() }}</div>
        <div class="pill"><i class='bx bx-category'></i> {{ $ticket->category }}</div>
        <div class="pill"><i class='bx bx-target-lock'></i> SLA: 4h response</div>
      </section>

      <!-- Main grid -->
      <section class="ticket-grid">
        <!-- Conversation -->
        <section class="panel thread-panel">
          <div class="panel-head">
            <h3>Conversation</h3>
          </div>

          <div class="panel-body thread">
            <!-- Requester message -->
            <article class="msg requester">
              <div class="msg-aside">
                <div class="avatar soft">{{ $initials }}</div>
              </div>
              <div class="msg-body">
                <div class="msg-top">
                  <strong>{{ $requesterFirst }} {{ $requesterLast }}</strong>
                  <span class="muted">Requester • {{ optional($ticket->created_at)->diffForHumans() }}</span>
                </div>
                <p>{{ $ticket->description }}</p>

                @if (count($attachments) > 0)
                  <ul class="attachments">
                    @foreach ($attachments as $path)
                      <li>
                        <i class='bx bx-file'></i>
                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($path) }}" target="_blank" rel="noopener">{{ basename($path) }}</a>
                      </li>
                    @endforeach
                  </ul>
                @endif
              </div>
            </article>

            @if ($messages->count() > 0)
              @foreach ($messages as $m)
                @php
                  $mFirst = $m->user_first_name ?? '';
                  $mLast = $m->user_last_name ?? '';

                  $mf = !empty($mFirst) ? strtoupper(substr($mFirst, 0, 1)) : '';
                  $ml = !empty($mLast) ? strtoupper(substr($mLast, 0, 1)) : '';
                  $mInitials = trim($mf . $ml) !== '' ? ($mf . $ml) : 'U';

                  $mRole = strtolower((string) ($m->user_role ?? 'user'));
                  $mIsStaff = in_array($mRole, ['admin', 'it'], true);
                  $mType = (string) ($m->message_type ?? 'public');
                @endphp

                @if ($mType === 'internal')
                  <article class="note">
                    <div class="note-icon"><i class='bx bx-note'></i></div>
                    <div class="note-body">
                      <div class="note-top">
                        <strong>Internal note</strong>
                        <span class="muted">{{ optional($m->created_at)->diffForHumans() }} by {{ trim(($mFirst ?? '').' '.($mLast ?? '')) }}</span>
                      </div>
                      <p>{{ $m->body }}</p>
                    </div>
                  </article>
                @else
                  <article class="msg {{ $mIsStaff ? 'agent' : 'requester' }}">
                    <div class="msg-aside">
                      <div class="avatar {{ $mIsStaff ? 'agent' : 'soft' }}">{{ $mInitials }}</div>
                    </div>
                    <div class="msg-body">
                      <div class="msg-top">
                        <strong>{{ trim(($mFirst ?? '').' '.($mLast ?? '')) }}</strong>
                        <span class="muted">{{ $mIsStaff ? 'IT Support' : 'Requester' }} • {{ optional($m->created_at)->diffForHumans() }}</span>
                      </div>
                      <p>{{ $m->body }}</p>
                    </div>
                  </article>
                @endif
              @endforeach
            @endif
          </div>

          <!-- Composer (sticky inside panel) -->
          <div class="panel-foot composer">
            <form id="composerForm" method="POST" action="{{ route('tickets.messages.store', $ticket->ticket_id) }}">
              @csrf
              <div class="compose-tabs">
                <button type="button" class="tab-btn active" data-tab="public">Public reply</button>
                @if ($is_staff)
                  <button type="button" class="tab-btn" data-tab="internal">Internal note</button>
                @endif
              </div>

              <input type="hidden" name="message_type" id="messageType" value="public">

              <div class="compose-wrap">
                <textarea id="composerBody" name="body" rows="5" placeholder="Write your message..." required></textarea>
                <div class="compose-actions">
                  <div class="left">
                    <button class="btn-outlined attach" type="button" disabled title="Coming soon"><i class='bx bx-paperclip'></i> Attach</button>
                    <button class="btn-outlined soft" type="button" disabled title="Coming soon"><i class='bx bx-tag'></i> Add tag</button>
                  </div>
                  <div class="right">
                    @if ($is_staff)
                      <div class="select-pill size-s">
                        <select name="next_status" aria-label="Next status">
                          <option value="">Keep status</option>
                          <option value="in_progress">Set In Progress</option>
                          <option value="resolved">Mark Resolved</option>
                          <option value="closed">Close</option>
                        </select>
                        <i class='bx bx-chevron-down'></i>
                      </div>
                    @endif
                    <button class="btn-primary send" type="submit"><i class='bx bx-send'></i> Send</button>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </section>

        <!-- Sidebar -->
        <aside class="right-col">
          <section class="panel info">
            <div class="panel-head"><h3>Details</h3></div>
            <div class="panel-body details">
              <dl>
                <div class="row">
                  <dt>Status</dt>
                  <dd>
                    <span @class([
                      'status',
                      'status-open' => ($ticket->status ?? '') === 'open',
                      'status-progress' => ($ticket->status ?? '') === 'in_progress',
                      'status-resolved' => ($ticket->status ?? '') === 'resolved',
                      'status-closed' => ($ticket->status ?? '') === 'closed',
                    ])>{{ $statusLabel }}</span>
                  </dd>
                </div>
                <div class="row">
                  <dt>Priority</dt>
                  <dd>
                    <span @class([
                      'chip',
                      'chip-high' => ($ticket->priority ?? '') === 'High',
                      'chip-medium' => ($ticket->priority ?? '') === 'Medium',
                      'chip-low' => ($ticket->priority ?? '') === 'Low',
                    ])>{{ $ticket->priority }}</span>
                  </dd>
                </div>
                <div class="row"><dt>Category</dt><dd>{{ $ticket->category }}</dd></div>
                <div class="row"><dt>Contact</dt><dd>{{ $ticket->preferred_contact }}</dd></div>
              </dl>
            </div>
          </section>

          <section class="panel info">
            <div class="panel-head"><h3>People</h3></div>
            <div class="panel-body people">
              <div class="p-row">
                <div class="p-who">
                  <div class="avatar soft">{{ $initials }}</div>
                  <div>
                    <strong>{{ $requesterFirst }} {{ $requesterLast }}</strong>
                    <p class="muted">Requester</p>
                  </div>
                </div>
                <a class="btn-mini" href="#"><i class='bx bx-user-plus'></i></a>
              </div>
              <div class="p-row">
                <div class="p-who">
                  <div class="avatar agent">{{ $assigneeInitials }}</div>
                  <div>
                    <strong>
                      @php
                        $assigneeName = trim(($assigneeFirst ?? '').' '.($assigneeLast ?? ''));
                      @endphp
                      {{ $assigneeName !== '' ? $assigneeName : (($ticket->assigned_to ?? null) ? 'User #'.$ticket->assigned_to : 'Unassigned') }}
                    </strong>
                    <p class="muted">Assignee</p>
                  </div>
                </div>
                <a class="btn-mini" href="#"><i class='bx bx-transfer'></i></a>
              </div>
            </div>
          </section>

          <section class="panel info">
            <div class="panel-head"><h3>Tags</h3></div>
            <div class="panel-body tags">
              <button class="tag">email</button>
              <button class="tag">login</button>
              <button class="tag">mfa</button>
              <button class="btn-outlined small"><i class='bx bx-plus'></i> Add</button>
            </div>
          </section>

          <section class="panel info">
            <div class="panel-head"><h3>Attachments</h3></div>
            <div class="panel-body files">
              @if (count($attachments) > 0)
                @foreach ($attachments as $path)
                  <a class="file" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($path) }}" target="_blank" rel="noopener"><i class='bx bx-file'></i> {{ basename($path) }}</a>
                @endforeach
              @else
                <p class="muted">No files attached.</p>
              @endif
              <button class="btn-outlined small"><i class='bx bx-upload'></i> Upload</button>
            </div>
          </section>

          <section class="panel info">
            <div class="panel-head"><h3>Activity</h3></div>
            <div class="panel-body activity">
              <div class="a-row"><span class="muted">Today 10:10</span><p>Internal note added by Prince Remo</p></div>
              <div class="a-row"><span class="muted">Today 10:05</span><p>Reply posted by Prince Remo</p></div>
              <div class="a-row"><span class="muted">Today 10:04</span><p>Status set to In Progress</p></div>
              <div class="a-row"><span class="muted">Today 09:42</span><p>Ticket created by Samuel Muralidharan</p></div>
            </div>
          </section>
        </aside>
      </section>
    </main>
  </div>

  <script src="{{ asset('assets/js/components/sidebar.js') }}"></script>
  <script src="{{ asset('assets/js/pages/ticket.js') }}"></script>
</body>
</html>