@php
  $u = auth()->user();

  $initials =
    ($u && $u->first_name ? strtoupper(substr($u->first_name, 0, 1)) : '') .
    ($u && $u->last_name ? strtoupper(substr($u->last_name, 0, 1)) : '');

  $initials = $initials ?: 'U';
@endphp

<div class="profile-chip">
  <div class="avatar">{{ $initials }}</div>

  <div class="user-meta">
    <p class="user-name">{{ $u?->first_name }} {{ $u?->last_name }}</p>
    <p class="user-role">{{ $u?->role }}</p>
  </div>
</div>