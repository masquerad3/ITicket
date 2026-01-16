<!DOCTYPE html>
<html lang="en-US">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>ITicket - Create Ticket</title>

  <!-- Global/base -->
  <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
  <!-- Shared components -->
  <link rel="stylesheet" href="{{ asset('assets/css/components/topbar.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/components/sidebar.css') }}">
  <!-- Page-specific -->
  <link rel="stylesheet" href="{{ asset('assets/css/pages/create-ticket.css') }}">

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
      <header class="page-header">
        <div class="page-header-left">
          <h2>Create New Ticket</h2>
          <p class="muted">Tell us what you need help with. The more details, the faster we can assist.</p>
        </div>
        <div class="page-header-actions">
          <a class="btn-outlined" href="{{ route('tickets.index') }}"><i class='bx bx-left-arrow-alt'></i> Back to Tickets</a>
        </div>
      </header>

      <!-- Status Messages -->
      @if (session('status'))
        <div class="panel" role="status" style="border:1px solid #d1fae5;background:#ecfdf5;color:#065f46;">
          {{ session('status') }}
        </div>
      @endif

      <!-- Error Messages -->
      @if ($errors->any())
        <div class="panel" role="alert" style="border:1px solid #fecaca;background:#fef2f2;color:#991b1b;">
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <section class="create-grid">
        <!-- Form -->
        <section class="panel form-panel">
          <form id="ticketForm" method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data" novalidate>
            @csrf
            <h3 class="panel-title">Ticket Details</h3>

            <div class="form-grid">
              <div class="field full">
                <label for="subject">Subject</label>
                <input id="subject" name="subject" type="text" placeholder="Brief summary (e.g., Cannot access email)" value="{{ old('subject') }}" required>
                <small class="hint">Keep it short and specific.</small>
              </div>

              <div class="field">
                <label for="category">Category</label>
                <div class="select-pill wide">
                  <select id="category" name="category" required>
                    <option value="" disabled @selected(old('category') === null)>Select category</option>
                    <option value="Account/Access" @selected(old('category') === 'Account/Access')>Account/Access</option>
                    <option value="Software" @selected(old('category') === 'Software')>Software</option>
                    <option value="Hardware" @selected(old('category') === 'Hardware')>Hardware</option>
                    <option value="Network" @selected(old('category') === 'Network')>Network</option>
                    <option value="Request" @selected(old('category') === 'Request')>Request</option>
                    <option value="Other" @selected(old('category') === 'Other')>Other</option>
                  </select>
                  <i class='bx bx-chevron-down'></i>
                </div>
              </div>

              <div class="field">
                <label for="priority">Priority</label>
                <div class="select-pill">
                  <select id="priority" name="priority" required>
                    <option value="Medium" @selected(old('priority', 'Medium') === 'Medium')>Medium</option>
                    <option value="High" @selected(old('priority', 'Medium') === 'High')>High</option>
                    <option value="Low" @selected(old('priority', 'Medium') === 'Low')>Low</option>
                  </select>
                  <i class='bx bx-chevron-down'></i>
                </div>
              </div>

              <div class="field">
                <label for="department">Department (optional)</label>
                <input id="department" name="department" type="text" placeholder="Your department" value="{{ old('department') }}">
              </div>

              <div class="field">
                <label for="location">Location (optional)</label>
                <input id="location" name="location" type="text" placeholder="Building/Room (e.g., Main 3A)" value="{{ old('location') }}">
              </div>

              <div class="field full">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="6" placeholder="Describe the issue, steps to reproduce, and any error messages." required>{{ old('description') }}</textarea>
                <div class="desc-row">
                  <small class="hint">Tip: Include screenshots or exact error text.</small>
                  <small class="counter" id="descCounter">0 / 1000</small>
                </div>
              </div>

              <div class="field full">
                <label>Attachments</label>
                <div class="dropzone" id="dropzone" tabindex="0" aria-label="File upload area">
                  <i class='bx bx-cloud-upload'></i>
                  <p>Drag & drop files here or <button type="button" class="linklike" id="pickFiles">browse</button></p>
                  <small class="muted">PNG, JPG, PDF, DOCX up to 10MB each</small>
                  <input id="fileInput" name="files[]" type="file" multiple hidden accept=".png,.jpg,.jpeg,.pdf,.doc,.docx,.txt">
                </div>
                <ul class="file-list" id="fileList" aria-live="polite"></ul>
              </div>

              <div class="field full">
                <label>Preferred contact</label>
                <div class="radio-row">
                  <label class="radio">
                    <input type="radio" name="contact" value="email" @checked(old('contact', 'email') === 'email')>
                    <span>Email</span>
                  </label>
                  <label class="radio">
                    <input type="radio" name="contact" value="phone" @checked(old('contact', 'email') === 'phone')>
                    <span>Phone</span>
                  </label>
                  <label class="radio">
                    <input type="radio" name="contact" value="teams" @checked(old('contact', 'email') === 'teams')>
                    <span>Teams/Chat</span>
                  </label>
                </div>
              </div>

              <div class="field full">
                <label class="checkbox">
                  <input type="checkbox" id="consent" name="consent" value="1" @checked(old('consent')) required>
                  <span>Confirm the information provided is accurate to the best of my knowledge.</span>
                </label>
              </div>
            </div>

            <div class="form-actions">
              <button class="btn-outlined" type="button" onclick="history.back()">Cancel</button>
              <button class="btn-primary" type="submit">Submit Ticket</button>
            </div>
          </form>
        </section>

        <!-- Right: Help panel -->
        <aside class="panel help-panel">
          <div class="help-head">
            <h3>Tips for a fast resolution</h3>
          </div>
          <ul class="tips">
            <li>Share exact error messages or screenshots.</li>
            <li>Tell us when it started and how often it happens.</li>
            <li>Include device, OS, and network if relevant.</li>
            <li>Set the right priority. Use High only if work is blocked.</li>
          </ul>

          <div class="help-head mt">
            <h3>Example subjects</h3>
          </div>
          <ul class="examples">
            <li>"Outlook: cannot send emails — error 0x800CCC0E"</li>
            <li>"Request: Adobe Photoshop installation for Marketing PC"</li>
            <li>"VPN connects but no internet access"</li>
          </ul>
        </aside>
      </section>
    </main>
  </div>

  <!-- Scripts -->
  <script src="{{ asset('assets/js/components/sidebar.js') }}"></script>
  <script src="{{ asset('assets/js/pages/create-ticket.js') }}"></script>
</body>
</html>