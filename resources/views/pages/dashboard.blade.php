<!DOCTYPE html>
<html lang="en-US">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>ITicket - Dashboard</title>
 
  <!-- Global/base -->
  <link rel="stylesheet" href="assets/css/styles.css">
  <!-- Shared component styles -->
  <link rel="stylesheet" href="assets/css/components/topbar.css">
  <link rel="stylesheet" href="assets/css/components/sidebar.css">
  <!-- Page styles -->
  <link rel="stylesheet" href="assets/css/pages/dashboard.css">
  
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
          <h2>Welcome back, <strong>Samuel</strong>!</h1>
          <p>Need help with an IT issue? Submit a ticket and we'll get right on it.</p>
        </div>
        <a class="new-ticket-btn" href="{{ route('create-ticket') }}">
          <i class='bx bx-plus'></i> 
          <p>Create New Ticket</p>
        </a>
      </div>

      <!-- Ticket Counters -->
      <section class="counter">
        <div class="counter-card counter-total">
          <div class="counter-value">17</div>
          <div class="counter-label">Total Tickets</div>
        </div>
        <div class="counter-card counter-open">
          <div class="counter-value">3</div>
          <div class="counter-label">Open Tickets</div>
        </div>
        <div class="counter-card counter-progress">
          <div class="counter-value">2</div>
          <div class="counter-label">In Progress</div>
        </div>
        <div class="counter-card counter-resolved">
          <div class="counter-value">12</div>
          <div class="counter-label">Resolved</div>
        </div>
      </section>

      <!-- Recent Tickets -->
      <section class="my-tickets">
        <div class="panel-header">
          <h3>My Recent Tickets</h3>
          <a href="{{ route('tickets') }}">View All</a>
        </div>

        <div class="ticket-card">
          <div class="ticket-header">
            <a class="ticket-id" href="">#TKT-1245</a>
            <div class="ticket-status status-high">High Priority</div>
          </div>
          <div class="ticket-body">
            <h4 class="ticket-title">Unable to connect to VPN</h4>
            <p class="ticket-description">I am having trouble connecting to the company VPN from my home network. It keeps timing out.</p>
            <div class="ticket-meta">
              <div class="ticket-date">2 hours ago</div>
            </div>
          </div>
        </div>

        <div class="ticket-card">
          <div class="ticket-header">
            <a class="ticket-id" href="">#TKT-1239</a>
            <div class="ticket-status status-medium">Medium Priority</div>
          </div>
          <div class="ticket-body">
            <h4 class="ticket-title">Email not syncing on mobile</h4>
            <p class="ticket-description">My work email is not syncing properly on my mobile device. I am missing important emails.</p>
            <div class="ticket-meta">
              <div class="ticket-date">Submitted on: 2025-06-08</div>
            </div>
          </div>
        
      </section>
      
    </main>

  </div>

  <script src="assets/js/components/sidebar.js"></script>
</body>
</html>