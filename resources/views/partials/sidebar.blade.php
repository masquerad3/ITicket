<aside class="slide-menu">
  <div class="menu-header">
    <button class="menu-close" type="button">
      <i class='bx bxs-chevron-right-circle'></i>
    </button>
  </div>

  <div class="menu-content">
    @php
      $role = strtolower((string) (auth()->user()?->role ?? 'user'));
      $is_staff = in_array($role, ['admin', 'it'], true);

      $ticketsLabel = $is_staff ? 'Ticket Queue' : 'My Tickets';
      $ticketsHref = $is_staff
        ? route('tickets.index', ['view' => 'queue'])
        : route('tickets.index');
    @endphp

    <nav class="menu-group">
      <h4 class="group-title">Main Menu</h4>
      <!-- Use the @class directive to conditionally add the 'active' class based on the current route -->
      <a @class(['menu-link', 'active' => request()->routeIs('dashboard')]) href="{{ route('dashboard') }}"><i class='bx bx-home'></i> Dashboard</a>
      <a @class(['menu-link', 'active' => request()->routeIs('tickets.index') || request()->routeIs('tickets.show') || request()->routeIs('ticket')]) href="{{ $ticketsHref }}"><i class='bx bx-list-check'></i> {{ $ticketsLabel }}</a>
      <a @class(['menu-link', 'active' => request()->routeIs('tickets.create')]) href="{{ route('tickets.create') }}"><i class='bx bx-plus-circle'></i> Create Ticket</a>
    </nav>

    <nav class="menu-group">
      <h4 class="group-title">Support</h4>
      <a @class(['menu-link', 'active' => request()->routeIs('knowledge') || request()->routeIs('knowledge-article')]) href="{{ route('knowledge') }}"><i class='bx bx-book'></i> Knowledge Base</a>
      <!-- <a class="menu-link" href="#"><i class='bx bx-chat'></i> Live Chat</a> -->
      <a @class(['menu-link', 'active' => request()->routeIs('contact')]) href="{{ route('contact') }}"><i class='bx bx-envelope'></i> Contact</a>
    </nav>

    <nav class="menu-group">
      <h4 class="group-title">Account</h4>
      <a @class(['menu-link', 'active' => request()->routeIs('profile')]) href="{{ route('profile') }}"><i class='bx bx-user-circle'></i> Profile</a>
      <a @class(['menu-link', 'active' => request()->routeIs('settings')]) href="{{ route('settings') }}"><i class='bx bx-cog'></i> Settings</a>
    </nav>

    @if ($role === 'admin')
      <nav class="menu-group">
        <h4 class="group-title">Admin</h4>
        <a @class(['menu-link', 'active' => request()->routeIs('admin.users.*')]) href="{{ route('admin.users.index') }}"><i class='bx bx-user-voice'></i> Manage Users</a>
      </nav>
    @endif
  </div>
</aside>