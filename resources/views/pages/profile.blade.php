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
  @php
    // Currently logged-in user
    $u = auth()->user();

    $first_initial = '';
    $last_initial = '';

    if ($u !== null && !empty($u->first_name)) {
        $first_initial = strtoupper(substr($u->first_name, 0, 1));
    }

    if ($u !== null && !empty($u->last_name)) {
        $last_initial = strtoupper(substr($u->last_name, 0, 1));
    }

    $initials = $first_initial . $last_initial;

    if ($initials === '') {
        $initials = 'U';
    }
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
            <button class="btn-danger" id="logoutBtn" type="submit"><i class='bx bx-log-out'></i>   Logout</button>
          </form>
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

      <section class="profile-grid">
        <!-- Left: Summary card -->
        <aside class="profile-card">
          <div class="profile-avatar">
            <div class="avatar-lg">{{ $initials }}</div>
            <button class="btn-outlined small" type="button">
              <i class='bx bx-upload'></i> Upload Photo
            </button>
          </div>

          <div class="profile-info">
            <h3 class="name">{{ $u?->first_name }} {{ $u?->last_name }}</h3>
            <p class="role">{{ $u?->role }}</p>
            <p class="email">{{ $u?->email }}</p>
          </div>

          <div class="divider"></div>

          <!-- Quick stats (currently placeholder values) -->
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
          <form id="profileForm" method="POST" action="{{ route('profile.update') }}" novalidate>
            @csrf

            <h3 class="panel-title">Personal Information</h3>
            <div class="form-grid">
              <div class="field">
                <label for="first_name">First Name</label>
                <!-- old('first_name', $u?->first_name) means:
                First choice: use the value the user just typed last time (old('first_name'))
                If there is no old input: fall back to the current value from the database ($u->first_name) -->
                <input id="first_name" name="first_name" type="text" placeholder="First name" 
                value="{{ old('first_name', $u?->first_name) }}" required>
              </div>
              <div class="field">
                <label for="last_name">Last Name</label>
                <input id="last_name" name="last_name" type="text" placeholder="Last name" 
                value="{{ old('last_name', $u?->last_name) }}" required>
              </div>
              <div class="field">
                <label for="role">Role</label>
                <input id="role" name="role" type="text" 
                value="{{ $u?->role }}" readonly>
              </div>
              <div class="field">
                <label for="is_active">Status</label>
                <input id="is_active" name="is_active" type="text" 
                value="{{ (int)($u?->is_active ?? 1) === 1 ? 'Active' : 'Inactive' }}" readonly>
              </div>
            </div>

            <h3 class="panel-title">Contact</h3>
            <div class="form-grid">
              <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" placeholder="Email" 
                value="{{ old('email', $u?->email) }}" required>
              </div>
              <div class="field">
                <label for="contact">Phone</label>
                <input id="contact" name="contact" type="text" placeholder="Contact Number"
                value="{{ old('contact', $u?->contact) }}" required>
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