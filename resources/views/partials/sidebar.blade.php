<aside class="slide-menu">
  <div class="menu-header">
    <button class="menu-close" type="button">
      <i class='bx bxs-chevron-right-circle'></i>
    </button>
  </div>

  <div class="menu-content">
    <nav class="menu-group">
      <h4 class="group-title">Main Menu</h4>
      <a class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class='bx bx-home'></i> Dashboard</a>
      <a class="menu-link {{ request()->routeIs('tickets') || request()->routeIs('ticket') ? 'active' : '' }}" href="{{ route('tickets') }}"><i class='bx bx-list-check'></i> My Ticket</a>
      <a class="menu-link {{ request()->routeIs('create-ticket') ? 'active' : '' }}" href="{{ route('create-ticket') }}"><i class='bx bx-plus-circle'></i> Create Ticket</a>
    </nav>

    <nav class="menu-group">
      <h4 class="group-title">Support</h4>
      <a class="menu-link {{ request()->routeIs('knowledge') || request()->routeIs('knowledge-article') ? 'active' : '' }}" href="{{ route('knowledge') }}"><i class='bx bx-book'></i> Knowledge Base</a>
      <a class="menu-link" href="#"><i class='bx bx-chat'></i> Live Chat</a>
      <a class="menu-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}"><i class='bx bx-envelope'></i> Contact</a>
    </nav>

    <nav class="menu-group">
      <h4 class="group-title">Account</h4>
      <a class="menu-link {{ request()->routeIs('profile') ? 'active' : '' }}" href="{{ route('profile') }}"><i class='bx bx-user-circle'></i> Profile</a>
      <a class="menu-link {{ request()->routeIs('settings') ? 'active' : '' }}" href="{{ route('settings') }}"><i class='bx bx-cog'></i> Settings</a>
      <!-- 
      {{-- Logout inside sidebar (optional) --}}
      <form method="POST" action="{{ route('logout') }}" style="margin-top: 12px;">
        @csrf
        <button class="menu-link" type="submit" style="background:none;border:0;padding:0;text-align:left;cursor:pointer;">
          <i class='bx bx-log-out'></i> Logout
        </button>
      </form> -->
    </nav>
  </div>
</aside>