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
    $is_admin = $role === 'admin';

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

    $myId = auth()->id();
    $is_my_ticket = $myId !== null && (int) ($ticket->user_id ?? 0) === (int) $myId;

    $requesterPhoto = (string) ($ticket->requester_photo_path ?? '');
    if ($requesterPhoto === '' && $is_my_ticket && $u !== null) {
      $requesterPhoto = (string) ($u->profile_photo_path ?? '');
    }
    $assigneePhoto = (string) ($ticket->assignee_photo_path ?? '');

    $statusLabel = 'Open';
    if (($ticket->status ?? '') === 'in_progress') $statusLabel = 'In Progress';
    if (($ticket->status ?? '') === 'resolved') $statusLabel = 'Resolved';
    if (($ticket->status ?? '') === 'closed') $statusLabel = 'Closed';

    $attachments = $ticket->attachments ?? [];
    if (!is_array($attachments)) $attachments = [];

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

          @if ($is_admin)
            <form method="POST" action="{{ route('tickets.hardDelete', $ticket->ticket_id) }}" style="display:inline;" onsubmit="return confirm('Permanently delete this ticket and all its messages/files? This cannot be undone.');">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn-danger"><i class='bx bx-trash'></i> Hard Delete</button>
            </form>
          @endif
        </div>
      </section>

      @if (session('status'))
        <div class="alert alert-success" role="status">
          <i class='bx bx-check-circle'></i>
          <span>{{ session('status') }}</span>
          <button type="button" class="alert-close" aria-label="Dismiss" data-dismiss="alert"><i class='bx bx-x'></i></button>
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
            @php
              $initialFiles = $files
                ->filter(fn ($f) => (int) ($f->uploaded_by ?? 0) === (int) ($ticket->user_id ?? 0))
                ->values();

              $createdSys = $messages
                ->first(fn ($m) => (string) ($m->message_type ?? '') === 'system' && (string) ($m->body ?? '') === 'TICKET_CREATED');
              $createdSysId = (int) ($createdSys->message_id ?? 0);
            @endphp

            @if ($createdSys)
              <div class="sys-msg">
                <span>Ticket created</span>
                <span class="muted">{{ optional($createdSys->created_at ?? null)->diffForHumans() }}</span>
              </div>
            @endif

            <!-- Requester message -->
            <article class="msg requester {{ $is_my_ticket ? '' : 'them' }}">
              <div class="msg-aside">
                <div class="avatar soft">
                  @if ($requesterPhoto !== '')
                    <img src="{{ asset('storage/' . ltrim($requesterPhoto, '/')) }}" alt="Requester">
                  @else
                    {{ $initials }}
                  @endif
                </div>
              </div>
              <div class="msg-body">
                <div class="msg-top">
                  <strong>{{ $requesterFirst }} {{ $requesterLast }}</strong>
                  <span class="muted">Requester • {{ optional($ticket->created_at)->diffForHumans() }}</span>
                </div>
                <p>{{ $ticket->description }}</p>

                @if ($initialFiles->count() > 0)
                  <ul class="attachments">
                    @foreach ($initialFiles as $f)
                      @php
                        $mime = (string) ($f->mime ?? '');
                        $isImg = str_starts_with($mime, 'image/');
                        $name = $f->original_name ?? basename((string) ($f->stored_path ?? ''));
                        $canDeleteInitial = $is_staff || ((int) ($f->uploaded_by ?? 0) === (int) ($myId ?? 0));
                      @endphp
                      <li>
                        <i class='bx bx-file'></i>
                        <a href="{{ route('tickets.files.show', ['ticket' => $ticket->ticket_id, 'file' => $f->file_id]) }}" target="_blank" rel="noopener">{{ $name }}</a>
                        @if ($canDeleteInitial)
                          <form method="POST" action="{{ route('tickets.files.delete', ['ticket' => $ticket->ticket_id, 'file' => $f->file_id]) }}" class="inline-form" onsubmit="return confirm('Remove this attachment?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="icon-btn" title="Remove attachment"><i class='bx bx-trash'></i></button>
                          </form>
                        @endif
                      </li>
                      @if ($isImg)
                        <li class="attachment-preview">
                          <details class="img-details">
                            <summary class="img-summary"></summary>
                            <a href="{{ route('tickets.files.show', ['ticket' => $ticket->ticket_id, 'file' => $f->file_id]) }}" target="_blank" rel="noopener">
                              <img class="img-attach" src="{{ route('tickets.files.show', ['ticket' => $ticket->ticket_id, 'file' => $f->file_id]) }}" alt="{{ $name }}">
                            </a>
                          </details>
                        </li>
                      @endif
                    @endforeach
                  </ul>
                @elseif (count($attachments) > 0)
                  <ul class="attachments">
                    @foreach ($attachments as $path)
                      @php
                        $isImg = preg_match('/\.(png|jpe?g|gif|webp)$/i', (string) $path) === 1;
                        $canDeleteLegacyInitial = $is_staff || $is_my_ticket;
                      @endphp
                      <li>
                        <i class='bx bx-file'></i>
                        <a href="{{ route('tickets.attachments.view', ['ticket' => $ticket->ticket_id, 'path' => $path]) }}" target="_blank" rel="noopener">{{ basename($path) }}</a>
                        @if ($canDeleteLegacyInitial)
                          <form method="POST" action="{{ route('tickets.attachments.delete', $ticket->ticket_id) }}" class="inline-form" onsubmit="return confirm('Remove this attachment?');">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="path" value="{{ $path }}">
                            <button type="submit" class="icon-btn" title="Remove attachment"><i class='bx bx-trash'></i></button>
                          </form>
                        @endif
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

                @if ($createdSysId > 0 && (int) ($m->message_id ?? 0) === $createdSysId)
                  @continue
                @endif

                @if ($mType === 'system')
                  @php
                    $body = (string) ($m->body ?? '');
                    $who = trim(($mFirst ?? '').' '.($mLast ?? ''));
                    if ($who === '') $who = 'User #'.($m->user_id ?? '');

                    $sysText = 'Update';
                    if ($body === 'TICKET_CREATED') {
                      $sysText = 'Ticket created';
                    } elseif (str_starts_with($body, 'ASSIGNED_TO:')) {
                      $sysText = "Assigned to {$who}";
                    } elseif (str_starts_with($body, 'STATUS_CHANGED:')) {
                      $st = trim(substr($body, strlen('STATUS_CHANGED:')));
                      $label = $st;
                      if ($st === 'in_progress') $label = 'In Progress';
                      if ($st === 'open') $label = 'Open';
                      if ($st === 'resolved') $label = 'Resolved';
                      if ($st === 'closed') $label = 'Closed';
                      $sysText = "Status changed to {$label}";
                    } elseif (str_starts_with($body, 'TAG_ADDED:')) {
                      $tag = trim(substr($body, strlen('TAG_ADDED:')));
                      $sysText = "Tag added: {$tag}";
                    } elseif (str_starts_with($body, 'TAG_REMOVED:')) {
                      $tag = trim(substr($body, strlen('TAG_REMOVED:')));
                      $sysText = "Tag removed: {$tag}";
                    } elseif (str_starts_with($body, 'ATTACHMENT_REMOVED:')) {
                      $name = trim(substr($body, strlen('ATTACHMENT_REMOVED:')));
                      $sysText = "Attachment removed: {$name}";
                    } elseif ($body === 'MESSAGE_DELETED') {
                      $sysText = $is_staff ? "Message deleted by {$who}" : 'Message deleted';
                    } elseif ($body === 'NOTE_DELETED') {
                      $sysText = $is_staff ? "Internal note deleted by {$who}" : 'Message deleted';
                    }
                  @endphp
                  <div class="sys-msg">
                    <span>{{ $sysText }}</span>
                    <span class="muted">{{ optional($m->created_at)->diffForHumans() }}</span>
                  </div>
                  @continue
                @endif

                @if ($mType === 'internal')
                  <article class="note">
                    <div class="note-icon"><i class='bx bx-note'></i></div>
                    <div class="note-body">
                      <div class="note-top">
                        <strong>Internal note</strong>
                        <span class="muted">{{ optional($m->created_at)->diffForHumans() }} by {{ trim(($mFirst ?? '').' '.($mLast ?? '')) }}</span>
                        @if ($is_staff)
                          <div class="msg-actions">
                            <button type="button" class="icon-btn msg-menu-btn" aria-label="Note actions" aria-expanded="false"><i class='bx bx-dots-vertical-rounded'></i></button>
                            <div class="msg-menu" role="menu">
                              <form method="POST" action="{{ route('tickets.messages.delete', ['ticket' => $ticket->ticket_id, 'message' => $m->message_id]) }}" class="menu-form" onsubmit="return confirm('Delete this note?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="menu-item" role="menuitem">Delete note</button>
                              </form>
                            </div>
                          </div>
                        @endif
                      </div>
                      <p>{{ $m->body }}</p>
                    </div>
                  </article>
                @else
                  <article class="msg {{ $mIsStaff ? 'agent' : 'requester' }} {{ $mIsMe ? '' : 'them' }}">
                    <div class="msg-aside">
                      @php
                        $mPhoto = (string) ($m->user_photo_path ?? '');
                        if ($mPhoto === '' && $mIsMe && $u !== null) {
                          $mPhoto = (string) ($u->profile_photo_path ?? '');
                        }
                      @endphp
                      <div class="avatar {{ $mIsStaff ? 'agent' : 'soft' }}">
                        @if ($mPhoto !== '')
                          <img src="{{ asset('storage/' . ltrim($mPhoto, '/')) }}" alt="User">
                        @else
                          {{ $mInitials }}
                        @endif
                      </div>
                    </div>
                    <div class="msg-body">
                      <div class="msg-top">
                        <strong>{{ trim(($mFirst ?? '').' '.($mLast ?? '')) }}</strong>
                        <span class="muted">{{ $mIsStaff ? 'IT Support' : 'Requester' }} • {{ optional($m->created_at)->diffForHumans() }}</span>
                        @if (($mType ?? '') !== 'system' && ($is_staff || ($mIsMe && ($mType ?? '') !== 'internal')))
                          <div class="msg-actions">
                            <button type="button" class="icon-btn msg-menu-btn" aria-label="Message actions" aria-expanded="false"><i class='bx bx-dots-vertical-rounded'></i></button>
                            <div class="msg-menu" role="menu">
                              <form method="POST" action="{{ route('tickets.messages.delete', ['ticket' => $ticket->ticket_id, 'message' => $m->message_id]) }}" class="menu-form" onsubmit="return confirm('Delete this message?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="menu-item" role="menuitem">Delete message</button>
                              </form>
                            </div>
                          </div>
                        @endif
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
                              @if ($is_staff || $mIsMe)
                                <form method="POST" action="{{ route('tickets.messageFiles.delete', ['ticket' => $ticket->ticket_id, 'file' => $f->file_id]) }}" class="inline-form" onsubmit="return confirm('Remove this attachment?');">
                                  @csrf
                                  @method('DELETE')
                                  <button type="submit" class="icon-btn" title="Remove attachment"><i class='bx bx-trash'></i></button>
                                </form>
                              @endif
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
                        <select id="statusSelect" name="_status" form="statusForm" aria-label="Update ticket status">
                          <option value="">Ticket Status</option>
                          <option value="open">Open</option>
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

            @if ($is_staff)
              <form id="statusForm" method="POST" action="{{ route('tickets.updateStatus', $ticket->ticket_id) }}" style="display:none;">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" id="statusHidden" value="">
              </form>
            @endif
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
                @php
                  $contact = (string) ($ticket->preferred_contact ?? '');
                  $contact = $contact !== '' ? ucfirst(strtolower($contact)) : '';
                @endphp
                <div class="row"><dt>Contact</dt><dd>{{ $contact }}</dd></div>
              </dl>
            </div>
          </section>

          <section class="panel info">
            <div class="panel-head"><h3>People</h3></div>
            <div class="panel-body people">
              <div class="p-row">
                <div class="p-who">
                  <div class="avatar soft">
                    @if ($requesterPhoto !== '')
                      <img src="{{ asset('storage/' . ltrim($requesterPhoto, '/')) }}" alt="Requester">
                    @else
                      {{ $initials }}
                    @endif
                  </div>
                  <div>
                    <strong>{{ $requesterFirst }} {{ $requesterLast }}</strong>
                    <p class="muted">Requester</p>
                  </div>
                </div>
                <a class="btn-mini" href="#"><i class='bx bx-user-plus'></i></a>
              </div>
              <div class="p-row">
                <div class="p-who">
                  <div class="avatar agent">
                    @if ($assigneePhoto !== '')
                      <img src="{{ asset('storage/' . ltrim($assigneePhoto, '/')) }}" alt="Assignee">
                    @else
                      {{ $assigneeInitials }}
                    @endif
                  </div>
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
              @php
                $messageAttachmentCount = 0;
                foreach ($messages as $_m) {
                  $mf = $_m->files ?? collect();
                  if (!($mf instanceof \Illuminate\Support\Collection)) $mf = collect($mf);
                  $messageAttachmentCount += $mf->count();
                }

                $hasTicketFiles = $files->count() > 0;
                $hasMessageFiles = $messageAttachmentCount > 0;
                $hasLegacyInitial = count($attachments) > 0;
              @endphp

              @if ($hasTicketFiles || $hasMessageFiles)
                @if ($hasTicketFiles)
                  @foreach ($files as $f)
                    @php
                      $uploaderName = trim(($f->uploader_first_name ?? '').' '.($f->uploader_last_name ?? ''));
                      if ($uploaderName === '') $uploaderName = 'User #'.($f->uploaded_by ?? '');
                      $fname = $f->original_name ?? basename((string) ($f->stored_path ?? ''));
                      $canDelete = $is_staff || ((int) ($f->uploaded_by ?? 0) === (int) ($myId ?? 0));
                    @endphp
                    <div class="file-row">
                      <div class="file-row-head">
                        <a class="file" href="{{ route('tickets.files.show', ['ticket' => $ticket->ticket_id, 'file' => $f->file_id]) }}" target="_blank" rel="noopener"><i class='bx bx-file'></i> {{ $fname }}</a>
                        @if ($canDelete)
                          <form method="POST" action="{{ route('tickets.files.delete', ['ticket' => $ticket->ticket_id, 'file' => $f->file_id]) }}" class="inline-form" onsubmit="return confirm('Remove this attachment?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="icon-btn" title="Remove attachment"><i class='bx bx-trash'></i></button>
                          </form>
                        @endif
                      </div>
                      <div class="file-meta muted">Uploaded {{ optional($f->created_at ?? null)->diffForHumans() }} by {{ $uploaderName }}</div>
                    </div>
                  @endforeach
                @endif

                @if ($hasMessageFiles)
                  <div class="file-section">
                    <div class="file-section-title muted">Message attachments</div>
                    @foreach ($messages as $_m)
                      @php
                        $_files = $_m->files ?? collect();
                        if (!($_files instanceof \Illuminate\Support\Collection)) $_files = collect($_files);
                      @endphp
                      @foreach ($_files as $_f)
                        @php
                          $_uploaderName = trim(($_f->uploader_first_name ?? '').' '.($_f->uploader_last_name ?? ''));
                          if ($_uploaderName === '') $_uploaderName = 'User #'.($_f->uploaded_by ?? '');
                          $_fname = $_f->original_name ?? basename((string) ($_f->stored_path ?? ''));
                          $_canDelete = $is_staff || ((int) ($_f->uploaded_by ?? 0) === (int) ($myId ?? 0));
                        @endphp
                        <div class="file-row">
                          <div class="file-row-head">
                            <a class="file" href="{{ route('tickets.messageFiles.show', ['ticket' => $ticket->ticket_id, 'file' => $_f->file_id]) }}" target="_blank" rel="noopener"><i class='bx bx-file'></i> {{ $_fname }}</a>
                            @if ($_canDelete)
                              <form method="POST" action="{{ route('tickets.messageFiles.delete', ['ticket' => $ticket->ticket_id, 'file' => $_f->file_id]) }}" class="inline-form" onsubmit="return confirm('Remove this attachment?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="icon-btn" title="Remove attachment"><i class='bx bx-trash'></i></button>
                              </form>
                            @endif
                          </div>
                          <div class="file-meta muted">Uploaded {{ optional($_f->created_at ?? null)->diffForHumans() }} by {{ $_uploaderName }}</div>
                        </div>
                      @endforeach
                    @endforeach
                  </div>
                @endif
              @else
                @if ($hasLegacyInitial)
                  <p class="muted">Only initial attachments are available below.</p>
                @else
                  <p class="muted">No files attached.</p>
                @endif
              @endif

              @if (count($attachments) > 0)
                <div class="file-section">
                  <div class="file-section-title muted">Initial attachments</div>
                  @foreach ($attachments as $path)
                    <div class="file-row-head">
                      <a class="file" href="{{ route('tickets.attachments.view', ['ticket' => $ticket->ticket_id, 'path' => $path]) }}" target="_blank" rel="noopener"><i class='bx bx-file'></i> {{ basename($path) }}</a>
                      @if ($is_staff || $is_my_ticket)
                        <form method="POST" action="{{ route('tickets.attachments.delete', $ticket->ticket_id) }}" class="inline-form" onsubmit="return confirm('Remove this attachment?');">
                          @csrf
                          @method('DELETE')
                          <input type="hidden" name="path" value="{{ $path }}">
                          <button type="submit" class="icon-btn" title="Remove attachment"><i class='bx bx-trash'></i></button>
                        </form>
                      @endif
                    </div>
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