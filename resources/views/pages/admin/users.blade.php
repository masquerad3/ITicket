<!DOCTYPE html>
<html lang="en-US">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>ITicket - Manage Users</title>

  <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/components/topbar.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/components/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/pages/admin-users.css') }}">

  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
  <div class="page">
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

    @include('partials.sidebar')
    <div class="backdrop"></div>

    <main class="content">
      <header class="page-header">
        <div class="page-header-left">
          <h2>Manage Users</h2>
          <p class="muted">Admin-only: update roles and deactivate accounts.</p>
        </div>
        <div class="page-header-actions">
          <div class="admin-search" role="search">
            <i class='bx bx-search'></i>
            <input id="userSearch" type="text" placeholder="Search by name, email, role…" autocomplete="off">
          </div>
        </div>
      </header>

      @if (session('status'))
        <div class="panel" role="status" style="border:1px solid #d1fae5;background:#ecfdf5;color:#065f46;">
          {{ session('status') }}
        </div>
      @endif

      @if ($errors->any())
        <div class="panel" role="alert" style="border:1px solid #fecaca;background:#fef2f2;color:#991b1b;">
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <section class="panel admin-panel">
        <div class="panel-head admin-panel-head">
          <div class="head-left">
            <strong>Users</strong>
            <span class="pill">{{ $users->count() }} total</span>
          </div>
          <div class="head-right muted">Tip: change role to <strong>it</strong> to test staff tickets</div>
        </div>

        <div class="panel-body" style="padding:0;">
          <div class="table-wrap">
            <table class="admin-table" id="usersTable">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Contact</th>
                  <th>Role</th>
                  <th>Active</th>
                  <th>Created</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                @foreach ($users as $u)
                  @php
                    $role = strtolower((string) ($u->role ?? 'user'));
                    $isMe = (int) ($u->user_id ?? 0) === (int) auth()->id();
                    $rowClass = $isMe ? 'is-me' : '';
                  @endphp

                  <tr class="{{ $rowClass }}" data-search="{{ strtolower(($u->first_name ?? '').' '.($u->last_name ?? '').' '.($u->email ?? '').' '.($u->contact ?? '').' '.($u->role ?? '')) }}">
                    <td>#{{ $u->user_id }}</td>
                    <td>
                      <div class="name-cell">
                        <span class="name">{{ $u->first_name }} {{ $u->last_name }}</span>
                        @if($isMe)
                          <span class="pill me">You</span>
                        @endif
                      </div>
                    </td>
                    <td class="mono">{{ $u->email }}</td>
                    <td class="mono">{{ $u->contact }}</td>
                    <td>
                      <span class="role-badge role-{{ $role }}">{{ $role }}</span>

                      <form method="POST" action="{{ route('admin.users.update', $u->user_id) }}" class="row-form">
                        @csrf
                        @method('PATCH')

                        <input type="hidden" name="first_name" value="{{ $u->first_name }}">
                        <input type="hidden" name="last_name" value="{{ $u->last_name }}">
                        <input type="hidden" name="email" value="{{ $u->email }}">
                        <input type="hidden" name="contact" value="{{ $u->contact }}">

                        <select name="role" class="input" aria-label="Role">
                          <option value="user" @selected($u->role === 'user')>user</option>
                          <option value="it" @selected($u->role === 'it')>it</option>
                          <option value="admin" @selected($u->role === 'admin')>admin</option>
                        </select>
                    </td>
                    <td>
                        <label class="toggle" title="Active">
                          <input type="hidden" name="is_active" value="0">
                          <input type="checkbox" name="is_active" value="1" @checked((int) $u->is_active === 1)>
                          <span class="track"><span class="thumb"></span></span>
                        </label>
                    </td>
                    <td>{{ !empty($u->created_at) ? \Illuminate\Support\Carbon::parse($u->created_at)->format('Y-m-d') : '' }}</td>
                    <td>
                        <button type="submit" class="btn-primary btn-small">Save</button>
                      </form>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </main>
  </div>

  <script src="{{ asset('assets/js/components/sidebar.js') }}"></script>
  <script>
    (function () {
      const input = document.getElementById('userSearch');
      const table = document.getElementById('usersTable');
      if (!input || !table) return;

      input.addEventListener('input', function () {
        const q = (input.value || '').trim().toLowerCase();
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach((tr) => {
          const hay = (tr.getAttribute('data-search') || '').toLowerCase();
          tr.style.display = q === '' || hay.includes(q) ? '' : 'none';
        });
      });
    })();
  </script>
</body>
</html>
