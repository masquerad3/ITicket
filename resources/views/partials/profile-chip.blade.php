@php
    // Current logged-in user (from session)
    // auth() is Laravel’s helper for the authentication system.
    // ->user() returns the currently logged-in user (a User model) based on the session.
    $u = auth()->user(); 
    
    // Initialize variables to empty strings.
    $first_initial = '';
    $last_initial = '';

    // $u->first_name = "Samuel" | $u->last_name = "Muralidharan" | $u->role = "user"
    // ?-> is PHP’s nullsafe operator.
      // If $u is not null, then $u?->first_name returns the value of $u->first_name.
      // If $u is null, then $u?->first_name returns null instead of crashing.

    // checks if first_name and last_name are set and not empty, then gets the first character and converts to uppercase
    // substr($u->first_name, 0, 1): “substring starting at position 0, length 1” = first character.
    // strtoupper(): converts to uppercase.
    if ($u !== null && !empty($u->first_name)) {
        $first_initial = strtoupper(substr($u->first_name, 0, 1));
    }

    if ($u !== null && !empty($u->last_name)) {
        $last_initial = strtoupper(substr($u->last_name, 0, 1));
    }

    // . is string concatenation in PHP (join strings) | "S" . "M" → "SM" | "S" . "" → "S"
    $initials = $first_initial . $last_initial;

    // If both first and last names are missing, default to "U" for "User"
    // === means "exactly equal to" (same value and same type)
    if ($initials === '') {
        $initials = 'U';
    }
  @endphp

<div class="profile-chip">
  <div class="avatar">{{ $initials }}</div>

  <div class="user-meta">
    <p class="user-name">{{ $u?->first_name }} {{ $u?->last_name }}</p>
    <p class="user-role">{{ $u?->role }}</p>
  </div>
</div>