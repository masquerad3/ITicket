<!DOCTYPE html>
<html lang="en-US">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ITicket - Knowledge Article</title>

  <!-- Global/base -->
  <link rel="stylesheet" href="assets/css/styles.css">
  <!-- Shared components -->
  <link rel="stylesheet" href="assets/css/components/topbar.css">
  <link rel="stylesheet" href="assets/css/components/sidebar.css">
  <!-- Article page styles -->
  <link rel="stylesheet" href="assets/css/pages/knowledge-article.css">

  <!-- Icons -->
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
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
        <a href="#" aria-current="page">Reset your account password (self-service portal)</a>
      </nav>

      <!-- Article header -->
      <header class="article-head">
        <h1>Reset your account password (self-service portal)</h1>
        <div class="meta-row">
          <span class="meta"><i class='bx bx-time-five'></i> Updated: 2025-06-12</span>
          <span class="meta"><i class='bx bx-user'></i> Author: IT Support</span>
          <span class="meta"><i class='bx bx-bookmark'></i> Article ID: KB-0001</span>
          <span class="meta"><i class='bx bx-stopwatch'></i> Reading time: 3 min</span>
        </div>
        <p class="summary">
          Use the password self-service portal to reset or unlock your account using multi-factor authentication (MFA).
          Choose this method if you can access your phone or authenticator app.
        </p>
        <div class="chips">
          <span class="chip">Password</span>
          <span class="chip">MFA</span>
          <span class="chip">Account Access</span>
          <span class="chip chip-soft">Windows</span>
          <span class="chip chip-soft">macOS</span>
        </div>
      </header>

      <!-- Applies to / prerequisites -->
      <section class="two-col">
        <article class="info-card">
          <h3>Applies to</h3>
          <ul>
            <li>Staff & Students</li>
            <li>Windows 10/11, macOS 12+</li>
            <li>Outlook 2019/365, Webmail</li>
          </ul>
        </article>
        <article class="info-card">
          <h3>Prerequisites</h3>
          <ul>
            <li>MFA set up (Authenticator app or SMS)</li>
            <li>Access to your registered device</li>
          </ul>
        </article>
      </section>

      <!-- Steps -->
      <section class="steps">
        <h2>Steps</h2>

        <ol class="step-list">
          <li id="step-1">
            <div class="step-head">
              <span class="step-num">1</span>
              <h4>Open the password portal</h4>
              <button class="copy-link" data-target="#step-1" title="Copy link"><i class='bx bx-link'></i></button>
            </div>
            <p>Go to the Password Portal and choose “Reset password”.</p>
            <div class="callout info"><i class='bx bx-info-circle'></i> Bookmark the portal for future use.</div>
          </li>

          <li id="step-2">
            <div class="step-head">
              <span class="step-num">2</span>
              <h4>Verify with MFA</h4>
              <button class="copy-link" data-target="#step-2" title="Copy link"><i class='bx bx-link'></i></button>
            </div>
            <p>Approve the login request on your authenticator app or enter the SMS code.</p>
            <div class="callout tip"><i class='bx bx-bulb'></i> If you changed phones, see <a href="#">“Update MFA device”</a>.</div>
          </li>

          <li id="step-3">
            <div class="step-head">
              <span class="step-num">3</span>
              <h4>Set a new password</h4>
              <button class="copy-link" data-target="#step-3" title="Copy link"><i class='bx bx-link'></i></button>
            </div>
            <p>Enter a new password. Use at least 8 characters and mix letters and numbers.</p>
            <div class="callout warn"><i class='bx bx-error-circle'></i> Avoid reusing recent passwords.</div>
          </li>

          <li id="step-4">
            <div class="step-head">
              <span class="step-num">4</span>
              <h4>Update your devices</h4>
              <button class="copy-link" data-target="#step-4" title="Copy link"><i class='bx bx-link'></i></button>
            </div>
            <p>Update saved passwords on your phone, Outlook, and other apps to prevent lockouts.</p>
          </li>
        </ol>
      </section>

      <!-- Troubleshooting -->
      <section class="troubleshoot">
        <h2>Troubleshooting</h2>
        <details class="ts-item">
          <summary><i class='bx bx-shield'></i> I no longer have my MFA device</summary>
          <div class="ts-body">
            Use the backup method (SMS). If none available, <a href="{{ route('tickets.create') }}">create a ticket</a> for identity verification.
          </div>
        </details>
        <details class="ts-item">
          <summary><i class='bx bx-no-entry'></i> The portal says “account locked”</summary>
          <div class="ts-body">
            Wait 15 minutes and retry. If still locked, use “Unlock account” in the portal or contact support.
          </div>
        </details>
      </section>

      <!-- Attachments -->
      <section class="attachments">
        <h2>Attachments</h2>
        <ul class="file-list">
          <li><i class='bx bx-file'></i> Password-Portal-Guide.pdf · 1.2 MB</li>
          <li><i class='bx bx-file'></i> MFA-Setup-Checklist.docx · 0.4 MB</li>
        </ul>
      </section>

      <!-- Related -->
      <section class="related">
        <h2>Related Articles</h2>
        <ul>
          <li><a href="#">Update MFA device</a></li>
          <li><a href="#">Outlook: fix “Disconnected” status</a></li>
          <li><a href="#">VPN: Connect from home (Windows & macOS)</a></li>
        </ul>
      </section>

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

  <script src="assets/js/components/sidebar.js"></script>
  <script src="assets/js/pages/kb-article.js"></script>
</body>
</html>