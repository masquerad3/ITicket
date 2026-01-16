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

    $myId = auth()->id();
    $is_my_ticket = $myId !== null && (int) ($ticket->user_id ?? 0) === (int) $myId;

    $messages = $messages ?? collect();
    $tags = $tags ?? collect();
    if (!($tags instanceof \Illuminate\Support\Collection)) $tags = collect($tags);

    $files = $files ?? collect();
    if (!($files instanceof \Illuminate\Support\Collection)) $files = collect($files);

    $activity = $activity ?? collect();
    if (!($activity instanceof \Illuminate\Support\Collection)) $activity = collect($activity);
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
        @if (!empty($ticket->assigned_at))
          <div class="pill"><i class='bx bx-user-check'></i> Assigned {{ optional($ticket->assigned_at)->diffForHumans() }}</div>
        @endif
        @if (!empty($ticket->resolved_at))
          <div class="pill"><i class='bx bx-check-circle'></i> Resolved {{ optional($ticket->resolved_at)->diffForHumans() }}</div>
        @endif
        <div class="pill"><i class='bx bx-category'></i> {{ $ticket->category }}</div>
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
            <article class="msg requester {{ $is_my_ticket ? 'me' : '' }}">
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
                      @php
                        $isImg = preg_match('/\.(png|jpe?g|gif|webp)$/i', (string) $path) === 1;
                      @endphp
                      <li>
                        <i class='bx bx-file'></i>
                        <a href="{{ route('tickets.attachments.view', ['ticket' => $ticket->ticket_id, 'path' => $path]) }}" target="_blank" rel="noopener">{{ basename($path) }}</a>
                      </li>
                      @if ($isImg)
                        <li class="attachment-preview">
                          <details class="img-details">
                            <summary class="img-summary"></summary>
                            <a href="{{ route('tickets.attachments.view', ['ticket' => $ticket->ticket_id, 'path' => $path]) }}" target="_blank" rel="noopener">
                              <img class="img-attach" src="{{ route('tickets.attachments.view', ['ticket' => $ticket->ticket_id, 'path' => $path]) }}" alt="{{ basename($path) }}">
                            </a>
                          </details>
                        </li>
                      @endif
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

                  $mIsMe = $myId !== null && (int) ($m->user_id ?? 0) === (int) $myId;
                @endphp

                @if ($mType === 'system')
                  @continue
                @endif

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
                  <article class="msg {{ $mIsStaff ? 'agent' : 'requester' }} {{ $mIsMe ? 'me' : '' }}">
                    <div class="msg-aside">
                      <div class="avatar {{ $mIsStaff ? 'agent' : 'soft' }}">{{ $mInitials }}</div>
                    </div>
                    <div class="msg-body">
                      <div class="msg-top">
                        <strong>{{ trim(($mFirst ?? '').' '.($mLast ?? '')) }}</strong>
                        <span class="muted">{{ $mIsStaff ? 'IT Support' : 'Requester' }} • {{ optional($m->created_at)->diffForHumans() }}</span>
                      </div>
                      <p>{{ $m->body }}</p>

                      @php
                        $mFiles = $m->files ?? collect();
                        if (!($mFiles instanceof \Illuminate\Support\Collection)) $mFiles = collect($mFiles);
                      @endphp

                      @if ($mFiles->count() > 0)
                        <ul class="attachments">
                          @foreach ($mFiles as $f)
                            @php
                              $mime = (string) ($f->mime ?? '');
                              $isImg = str_starts_with($mime, 'image/');
                              $name = $f->original_name ?? basename((string) ($f->stored_path ?? ''));
                            @endphp
                            <li>
                              <i class='bx bx-file'></i>
                              <a href="{{ route('tickets.messageFiles.show', ['ticket' => $ticket->ticket_id, 'file' => $f->file_id]) }}" target="_blank" rel="noopener">{{ $name }}</a>
                            </li>
                            @if ($isImg)
                              <li class="attachment-preview">
                                <details class="img-details">
                                  <summary class="img-summary"></summary>
                                  <a href="{{ route('tickets.messageFiles.show', ['ticket' => $ticket->ticket_id, 'file' => $f->file_id]) }}" target="_blank" rel="noopener">
                                    <img class="img-attach" src="{{ route('tickets.messageFiles.show', ['ticket' => $ticket->ticket_id, 'file' => $f->file_id]) }}" alt="{{ $name }}">
                                  </a>
                                </details>
                              </li>
                            @endif
                          @endforeach
                        </ul>
                      @endif
                    </div>
                  </article>
                @endif
              @endforeach
            @endif
          </div>

          <!-- Composer (sticky inside panel) -->
          <div class="panel-foot composer">
            <form id="composerForm" method="POST" action="{{ route('tickets.messages.store', $ticket->ticket_id) }}" enctype="multipart/form-data">
              @csrf
              <div class="compose-tabs">
                <button type="button" class="tab-btn active" data-tab="public">Public reply</button>
                @if ($is_staff)
                  <button type="button" class="tab-btn" data-tab="internal">Internal note</button>
                @endif
              </div>

              <input type="hidden" name="message_type" id="messageType" value="public">
              <input id="composerFilesInput" name="files[]" type="file" multiple hidden accept=".png,.jpg,.jpeg,.gif,.webp,.pdf,.doc,.docx,.txt">

              <div class="compose-wrap">
                <textarea id="composerBody" name="body" rows="5" placeholder="Write your message..." required></textarea>
                <div class="compose-actions">
                  <div class="left">
                      <button id="composerAttachBtn" class="btn-outlined attach" type="button" title="Attach files to this message"><i class='bx bx-paperclip'></i> Attach</button>
                      <span id="composerFilesHint" class="muted" style="align-self:center;display:none;"></span>
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
              @if ($tags->count() > 0)
                @foreach ($tags as $tag)
                  <div class="tag">
                    <span class="tag-label">{{ $tag }}</span>
                    @if ($is_staff)
                      <form method="POST" action="{{ route('tickets.tags.delete', $ticket->ticket_id) }}" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="tag" value="{{ $tag }}">
                        <button type="submit" class="tag-remove" aria-label="Remove tag {{ $tag }}"><i class='bx bx-x'></i></button>
                      </form>
                    @endif
                  </div>
                @endforeach
              @else
                <p class="muted">No tags yet.</p>
              @endif

              @if ($is_staff)
                <form method="POST" action="{{ route('tickets.tags.store', $ticket->ticket_id) }}" class="tag-form">
                  @csrf
                  <input name="tag" type="text" placeholder="Add tag (e.g. email, vpn, mfa)" class="tag-input">
                  <button class="btn-outlined small" type="submit"><i class='bx bx-plus'></i> Add</button>
                </form>
              @endif
            </div>
          </section>

          <section class="panel info">
            <div class="panel-head"><h3>Attachments</h3></div>
            <div class="panel-body files">
              @if ($files->count() > 0)
                @foreach ($files as $f)
                  @php
                    $uploaderName = trim(($f->uploader_first_name ?? '').' '.($f->uploader_last_name ?? ''));
                    if ($uploaderName === '') $uploaderName = 'User #'.($f->uploaded_by ?? '');
                    $fname = $f->original_name ?? basename((string) ($f->stored_path ?? ''));
                  @endphp
                  <div class="file-row">
                    <a class="file" href="{{ route('tickets.files.show', ['ticket' => $ticket->ticket_id, 'file' => $f->file_id]) }}" target="_blank" rel="noopener"><i class='bx bx-file'></i> {{ $fname }}</a>
                    <div class="file-meta muted">Uploaded {{ optional($f->created_at ?? null)->diffForHumans() }} by {{ $uploaderName }}</div>
                  </div>
                @endforeach
              @elseif (count($attachments) > 0)
                <p class="muted">Only initial attachments are available below.</p>
              @else
                <p class="muted">No files attached.</p>
              @endif

              @if (count($attachments) > 0)
                <div class="file-section">
                  <div class="file-section-title muted">Initial attachments</div>
                  @foreach ($attachments as $path)
                    <a class="file" href="{{ route('tickets.attachments.view', ['ticket' => $ticket->ticket_id, 'path' => $path]) }}" target="_blank" rel="noopener"><i class='bx bx-file'></i> {{ basename($path) }}</a>
                  @endforeach
                </div>
              @endif

              <form id="uploadAttachmentsForm" method="POST" action="{{ route('tickets.attachments.store', $ticket->ticket_id) }}" enctype="multipart/form-data" style="margin-top:10px;">
                @csrf
                <input id="uploadAttachmentsInput" name="files[]" type="file" multiple hidden accept=".png,.jpg,.jpeg,.pdf,.doc,.docx,.txt">
                <button id="uploadAttachmentsBtn" class="btn-outlined small" type="button"><i class='bx bx-upload'></i> Upload</button>
              </form>
            </div>
          </section>

          <section class="panel info">
            <div class="panel-head"><h3>Activity</h3></div>
            <div class="panel-body activity">
              @if ($activity->count() > 0)
                @foreach ($activity as $a)
                  <div class="a-row">
                    <span class="muted">{{ optional($a['at'] ?? null)->diffForHumans() }}</span>
                    <p>{{ $a['text'] ?? '' }}</p>
                  </div>
                @endforeach
              @else
                <p class="muted">No activity yet.</p>
              @endif
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