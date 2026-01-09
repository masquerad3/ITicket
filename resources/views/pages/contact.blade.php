<!DOCTYPE html>
<html lang="en-US">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ITicket - Contact</title>

  <!-- Global/base -->
  <link rel="stylesheet" href="assets/css/styles.css">
  <!-- Shared components -->
  <link rel="stylesheet" href="assets/css/components/topbar.css">
  <link rel="stylesheet" href="assets/css/components/sidebar.css">
  <!-- Page-specific -->
  <link rel="stylesheet" href="assets/css/pages/contact.css">

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
        <div class="profile-chip" tabindex="0">
          <div class="avatar">SM</div>
          <div class="user-meta">
            <p class="user-name">Samuel Muralidharan</p>
            <p class="user-role">Non-IT</p>
          </div>
        </div>
      </div>
    </header>

    <!-- Sidebar -->
    <aside class="slide-menu">
      <div class="menu-header">
        <button class="menu-close"><i class='bx bxs-chevron-right-circle'></i></button>
      </div>

      <div class="menu-content">
        <nav class="menu-group">
          <h4 class="group-title">Main Menu</h4>
          <a class="menu-link" href="{{ route('dashboard') }}"><i class='bx bx-home'></i> Dashboard</a>
          <a class="menu-link" href="{{ route('tickets') }}"><i class='bx bx-list-check'></i> My Ticket</a>
          <a class="menu-link" href="{{ route('create-ticket') }}"><i class='bx bx-plus-circle'></i> Create Ticket</a>
        </nav>
        <nav class="menu-group">
          <h4 class="group-title">Support</h4>
          <a class="menu-link" href="{{ route('knowledge') }}"><i class='bx bx-book'></i> Knowledge Base</a>
          <a class="menu-link" href="#"><i class='bx bx-chat'></i> Live Chat</a>
          <a class="menu-link active" href="{{ route('contact') }}"><i class='bx bx-envelope'></i> Contact</a>
        </nav>
        <nav class="menu-group">
          <h4 class="group-title">Account</h4>
          <a class="menu-link" href="{{ route('profile') }}"><i class='bx bx-user-circle'></i> Profile</a>
          <a class="menu-link" href="{{ route('settings') }}"><i class='bx bx-cog'></i> Settings</a>
        </nav>
      </div>
    </aside>
    <div class="backdrop"></div>

    <!-- Content -->
    <main class="content">
      <!-- Hero -->
      <section class="contact-hero">
        <div class="hero-text">
          <h2>Contact IT Support</h2>
          <p>Reach us by chat, phone, or send a quick message. For issues needing tracking, create a ticket.</p>
        </div>
        <div class="hero-actions">
          <a class="btn-white" href="{{ route('create-ticket') }}"><i class='bx bx-plus'></i> Create Ticket</a>
        </div>
      </section>

      <!-- Channels -->
      <section class="channels">
        <a class="channel-card" href="mailto:support@example.edu">
          <div class="icon email"><i class='bx bx-envelope'></i></div>
          <div class="body">
            <h3>Email us</h3>
            <p>support@example.edu</p>
            <span class="hint">Typical reply within 1 business day</span>
          </div>
          <i class='bx bx-right-arrow-alt arrow'></i>
        </a>

        <a class="channel-card" href="tel:+639001234567">
          <div class="icon phone"><i class='bx bx-phone'></i></div>
          <div class="body">
            <h3>Call IT Desk</h3>
            <p>+63 900 123 4567</p>
            <span class="hint">Weekdays 8:00–17:00</span>
          </div>
          <i class='bx bx-right-arrow-alt arrow'></i>
        </a>

        <a class="channel-card" href="#">
          <div class="icon chat"><i class='bx bx-chat'></i></div>
          <div class="body">
            <h3>Live chat</h3>
            <p>Talk to an agent</p>
            <span class="hint">Usually replies in under 5 mins</span>
          </div>
          <i class='bx bx-right-arrow-alt arrow'></i>
        </a>
      </section>

      <!-- Emergency banner -->
      <section class="emergency">
        <i class='bx bx-error-circle'></i>
        <div class="em-body">
          <strong>Major outage or security incident?</strong>
          <p>Call the on‑call hotline +63 900 987 6543 (24/7). For non‑urgent issues, please create a ticket.</p>
        </div>
      </section>

      <!-- Main grid -->
      <section class="grid">
        <!-- Left: message form -->
        <section class="panel form-panel">
          <div class="panel-head">
            <h3>Send a quick message</h3>
            <p class="muted small">Not for urgent issues. We’ll get back to you by email.</p>
          </div>

          <form id="contactForm" class="panel-body" novalidate>
            <div class="field two">
              <div>
                <label for="fullName">Full name</label>
                <input id="fullName" type="text" placeholder="Your name" value="Samuel Muralidharan" required>
              </div>
              <div>
                <label for="email">Email</label>
                <input id="email" type="email" placeholder="you@example.com" value="samuel.muralidharan@example.com" required>
              </div>
            </div>

            <div class="field two">
              <div>
                <label for="topic">Topic</label>
                <div class="select-pill">
                  <select id="topic" required>
                    <option value="">Choose a topic</option>
                    <option>Account & Access</option>
                    <option>Software</option>
                    <option>Hardware / Device</option>
                    <option>Network / VPN</option>
                    <option>Other</option>
                  </select>
                  <i class='bx bx-chevron-down'></i>
                </div>
              </div>
              <div>
                <label for="priority">Priority</label>
                <div class="select-pill">
                  <select id="priority" required>
                    <option>Low</option>
                    <option selected>Medium</option>
                    <option>High</option>
                  </select>
                  <i class='bx bx-chevron-down'></i>
                </div>
              </div>
            </div>

            <div class="field">
              <label for="subject">Subject</label>
              <input id="subject" type="text" placeholder="What do you need help with?" required>
            </div>

            <div class="field">
              <label for="message">Message</label>
              <textarea id="message" rows="6" placeholder="Add details we should know…" required></textarea>
            </div>

            <div class="form-actions">
              <button class="btn-outlined" type="button" id="resetForm"><i class='bx bx-undo'></i> Clear</button>
              <button class="btn-primary" type="submit"><i class='bx bx-send'></i> Send Message</button>
            </div>
          </form>
        </section>

        <!-- Right: info cards -->
        <aside class="right-col">
          <section class="panel info-panel">
            <div class="panel-head">
              <h3>Office hours</h3>
            </div>
            <div class="panel-body">
              <ul class="hours">
                <li><span>Mon–Fri</span><strong>08:00–17:00</strong></li>
                <li><span>Sat</span><strong>09:00–12:00</strong></li>
                <li><span>Sun / Holidays</span><strong>Closed</strong></li>
              </ul>
              <p class="sla muted">Typical response: within 1 business day. High priority may be faster.</p>
            </div>
          </section>

          <section class="panel info-panel">
            <div class="panel-head">
              <h3>Visit us</h3>
            </div>
            <div class="panel-body locations">
              <div class="loc">
                <div class="pin"><i class='bx bx-buildings'></i></div>
                <div class="txt">
                  <strong>Main IT Desk</strong>
                  <p>Building A, Room 204, Campus Ave.</p>
                </div>
              </div>
              <div class="loc">
                <div class="pin soft"><i class='bx bx-building-house'></i></div>
                <div class="txt">
                  <strong>Annex Desk</strong>
                  <p>Library Ground Floor, East Wing</p>
                </div>
              </div>
              <div class="map-placeholder">Map placeholder</div>
            </div>
          </section>

          <section class="panel info-panel">
            <div class="panel-head">
              <h3>Escalation</h3>
            </div>
            <div class="panel-body">
              <ol class="escalate">
                <li>Reply to your latest ticket email.</li>
                <li>If no response in 24 hours, call the IT Desk.</li>
                <li>For emergencies, use the hotline above.</li>
              </ol>
            </div>
          </section>
        </aside>
      </section>
    </main>
  </div>

  <script src="assets/js/components/sidebar.js"></script>
  <script src="assets/js/pages/contact.js"></script>
</body>
</html>