<!DOCTYPE html>
<html lang="en-US">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>ITicket - Profile</title>
 
  <!-- Global/base (reset, utilities, shared patterns) -->
  <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
  <!-- Shared component styles -->
  <link rel="stylesheet" href="{{ asset('assets/css/components/topbar.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/components/sidebar.css') }}">
  <!-- Page styles -->
  <link rel="stylesheet" href="{{ asset('assets/css/pages/profile.css') }}">
  
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
        
        <!-- Profile chip -->
        @include('partials.profile-chip')
      </div>
    </header>

    <!-- Slide-out Sidebar -->
    @include('partials.sidebar')
    <!-- Overlay backdrop -->
    <div class="backdrop"></div>

    
    <main class="content">
      <header class="page-header">
        <div class="page-header-left">
          <h2>My Profile</h2>
          <p class="muted">Manage your personal information and preferences</p>
        </div>
        <div class="page-header-actions">
          <button class="btn-outlined" id="openPwModal"><i class='bx bx-lock'></i> Change Password</button>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn-danger" id="logoutBtn" type="submit"><i class='bx bx-log-out'></i>Logout</button>
          </form>
        </div>
      </header>

      <section class="profile-grid">
        <!-- Left: Summary card -->
        <aside class="profile-card">
          <div class="profile-avatar">
            <div class="avatar-lg">SM</div>
            <button class="btn-outlined small" type="button">
              <i class='bx bx-upload'></i> Upload Photo
            </button>
          </div>

          <div class="profile-info">
            <h3 class="name">Samuel Muralidharan</h3>
            <p class="role">Non-IT • Student Affairs</p>
            <p class="email">samuel.muralidharan@example.com</p>
          </div>

          <div class="divider"></div>

          <div class="quick-stats">
            <div class="qstat">
              <span class="qstat-val">3</span>
              <span class="qstat-label">Open Tickets</span>
            </div>
            <div class="qstat">
              <span class="qstat-val">2</span>
              <span class="qstat-label">In Progress</span>
            </div>
            <div class="qstat">
              <span class="qstat-val">12</span>
              <span class="qstat-label">Resolved</span>
            </div>
          </div>

          <div class="divider"></div>

          <div class="actions">
            <a class="btn-link" href="{{ route('tickets') }}"><i class='bx bx-list-check'></i> View My Tickets</a>
            <a class="btn-link" href="{{ route('create-ticket') }}"><i class='bx bx-plus-circle'></i> Create Ticket</a>
          </div>
        </aside>

        <!-- Right: Editable form -->
        <section class="profile-form panel">
          <form id="profileForm" method="POST" novalidate>
            @csrf

            <h3 class="panel-title">Personal Information</h3>
            <div class="form-grid">
              <div class="field">
                <label for="fullName">Full Name</label>
                <input id="fullName" name="fullName" type="text" placeholder="Full name" value="Samuel Muralidharan" required>
              </div>
              <div class="field">
                <label for="username">Username</label>
                <input id="username" name="username" type="text" placeholder="Username" value="s.muralidharan" required>
              </div>
              <div class="field">
                <label for="department">Department</label>
                <input id="department" name="department" type="text" placeholder="Department" value="Student Affairs">
              </div>
              <div class="field">
                <label for="role">Role</label>
                <input id="role" name="role" type="text" value="Non-IT" readonly>
              </div>
            </div>

            <h3 class="panel-title">Contact</h3>
            <div class="form-grid">
              <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" placeholder="Email" value="samuel.muralidharan@example.com" required>
              </div>
              <div class="field">
                <label for="phone">Phone</label>
                <input id="phone" name="phone" type="tel" placeholder="+63 900 000 0000">
              </div>
            </div>

            <div class="form-actions">
              <button class="btn-outlined" type="button">Cancel</button>
              <button class="btn-primary" type="submit">Save Changes</button>
            </div>
          </form>
        </section>
      </section>
    </main>

  </div>

  <script src="assets/js/components/sidebar.js"></script>
</body>
</html>