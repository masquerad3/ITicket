<!DOCTYPE html>
<html lang="en-US">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ITicket - Ticket #TKT-1245</title>

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
          <h1>Cannot Access Email Account</h1>
          <div class="tt-meta">
            <a class="ticket-id" href="#">#TKT-1245</a>
            <span class="status status-progress">In Progress</span>
            <span class="chip chip-high">High</span>
          </div>
        </div>
        <div class="tt-actions">
          <a class="btn-outlined" href="{{ route('tickets') }}"><i class='bx bx-left-arrow-alt'></i> Back</a>
          <button class="btn-soft"><i class='bx bx-check-circle'></i> Mark Resolved</button>
          <button class="btn-primary"><i class='bx bx-reply'></i> Add Reply</button>
        </div>
      </section>

      <!-- Info pills -->
      <section class="pill-row">
        <div class="pill"><i class='bx bx-time-five'></i> Created 2025-06-08 09:42</div>
        <div class="pill"><i class='bx bx-sync'></i> Updated 2 hours ago</div>
        <div class="pill"><i class='bx bx-envelope'></i> Email</div>
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
            <!-- Timeline event -->
            <div class="event">
              <div class="event-dot"></div>
              <div class="event-card">
                <p><strong>Status changed</strong> from Open to In Progress by Prince Remo</p>
                <span class="muted">Today 10:04</span>
              </div>
            </div>

            <!-- Requester message -->
            <article class="msg requester">
              <div class="msg-aside">
                <div class="avatar soft">SM</div>
              </div>
              <div class="msg-body">
                <div class="msg-top">
                  <strong>Samuel Muralidharan</strong>
                  <span class="muted">Requester • Today 09:42</span>
                </div>
                <p>I've been unable to log into my email since this morning. Getting "Invalid credentials" even though I'm using the correct password.</p>
                <ul class="attachments">
                  <li><i class='bx bx-file'></i> error-screenshot.png</li>
                </ul>
              </div>
            </article>

            <!-- Agent reply -->
            <article class="msg agent">
              <div class="msg-aside">
                <div class="avatar agent">PR</div>
              </div>
              <div class="msg-body">
                <div class="msg-top">
                  <strong>Prince Remo</strong>
                  <span class="muted">IT Support • Today 10:05</span>
                </div>
                <p>Hi Samuel — we are checking the auth logs. Can you confirm if you recently changed your password or MFA device?</p>
              </div>
            </article>

            <!-- Internal note -->
            <article class="note">
              <div class="note-icon"><i class='bx bx-note'></i></div>
              <div class="note-body">
                <div class="note-top">
                  <strong>Internal note</strong>
                  <span class="muted">Today 10:10 by Prince Remo</span>
                </div>
                <p>User shows multiple failed IMAP logins from home IP. Likely cached password in phone mail app.</p>
              </div>
            </article>
          </div>

          <!-- Composer (sticky inside panel) -->
          <div class="panel-foot composer">
            <div class="compose-tabs">
              <button class="tab-btn active" data-tab="public">Public reply</button>
              <button class="tab-btn" data-tab="internal">Internal note</button>
            </div>
            <div class="compose-wrap">
              <textarea rows="5" placeholder="Write your message..."></textarea>
              <div class="compose-actions">
                <div class="left">
                  <button class="btn-outlined attach"><i class='bx bx-paperclip'></i> Attach</button>
                  <button class="btn-outlined soft"><i class='bx bx-tag'></i> Add tag</button>
                </div>
                <div class="right">
                  <div class="select-pill size-s">
                    <select>
                      <option>Keep In Progress</option>
                      <option>Mark Resolved</option>
                      <option>Close</option>
                    </select>
                    <i class='bx bx-chevron-down'></i>
                  </div>
                  <button class="btn-primary send"><i class='bx bx-send'></i> Send</button>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Sidebar -->
        <aside class="right-col">
          <section class="panel info">
            <div class="panel-head"><h3>Details</h3></div>
            <div class="panel-body details">
              <dl>
                <div class="row"><dt>Status</dt><dd><span class="status status-progress">In Progress</span></dd></div>
                <div class="row"><dt>Priority</dt><dd><span class="chip chip-high">High</span></dd></div>
                <div class="row"><dt>Category</dt><dd>Email</dd></div>
                <div class="row"><dt>Impact</dt><dd>Cannot Work</dd></div>
              </dl>
            </div>
          </section>

          <section class="panel info">
            <div class="panel-head"><h3>People</h3></div>
            <div class="panel-body people">
              <div class="p-row">
                <div class="p-who">
                  <div class="avatar soft">SM</div>
                  <div>
                    <strong>Samuel Muralidharan</strong>
                    <p class="muted">Requester</p>
                  </div>
                </div>
                <a class="btn-mini" href="#"><i class='bx bx-user-plus'></i></a>
              </div>
              <div class="p-row">
                <div class="p-who">
                  <div class="avatar agent">PR</div>
                  <div>
                    <strong>Prince Remo</strong>
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
              <a class="file" href="#"><i class='bx bx-file'></i> error-screenshot.png</a>
              <a class="file" href="#"><i class='bx bx-file'></i> vpn-log.txt</a>
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