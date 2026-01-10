<!DOCTYPE html>
<html lang="en-US">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>ITicket - Sign Up</title>

  <!-- Global/base -->
  <link rel="stylesheet" href="assets/css/styles.css">
  <!-- Base auth layout (same as login) -->
  <link rel="stylesheet" href="assets/css/pages/login.css">
  <!-- Register-specific tweaks -->
  <link rel="stylesheet" href="assets/css/pages/register.css">

  <!-- Icons -->
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
  <div class="page">
    <!-- Left Panel -->
    <div class="left-panel">
      <div class="inner">
        <div class="logo">
          <p>[logo]</p>
        </div>
        <div class="text-content">
          <h1>Welcome to <strong>ITicket</strong></h1>
          <p>Access your IT support dashboard and manage tickets efficiently. Report issues, track progress, and get faster support.</p>
        </div>
      </div>
    </div>

    <!-- Right Panel -->
    <div class="right-panel">
      <div class="inner">
        <h2>Sign Up</h2>
        <p>Create your account to get started</p>

        <form id="registerForm" method="POST" action="{{ route('register.post') }}" novalidate>
          @csrf

          {{-- Display validation errors --}}
          @if ($errors->any())
            <div class="error-box" role="alert">
              <ul>
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <!-- First name -->
          <div class="form-row">
            <label for="first_name">First Name</label>
            <div class="input-box">
              <i class='bx bxs-user'></i>
              <input
                type="text"
                id="first_name"
                name="first_name"
                placeholder="Enter your first name"
                value="{{ old('first_name') }}"
                autocomplete="given-name"
                required
              />
            </div>
          </div>

          <!-- Last name -->
          <div class="form-row">
            <label for="last_name">Last Name</label>
            <div class="input-box">
              <i class='bx bxs-user'></i>
              <input
                type="text"
                id="last_name"
                name="last_name"
                placeholder="Enter your last name"
                value="{{ old('last_name') }}"
                autocomplete="family-name"
                required
              />
            </div>
          </div>

          <!-- Email -->
          <div class="form-row">
            <label for="email">Email</label>
            <div class="input-box">
              <i class='bx bxs-envelope'></i>
              <input
                type="email"
                id="email"
                name="email"
                placeholder="Enter your email"
                value="{{ old('email') }}"
                autocomplete="email"
                required
              />
            </div>
          </div>

          <!-- Contact -->
          <div class="form-row">
            <label for="contact">Contact</label>
            <div class="input-box">
              <i class='bx bxs-phone'></i>
              <input
                type="text"
                id="contact"
                name="contact"
                placeholder="Enter your contact number"
                value="{{ old('contact') }}"
                autocomplete="tel"
                required
              />
            </div>
          </div>

          <!-- Password -->
          <div class="form-row">
            <label for="password">Password</label>
            <div class="input-box">
              <i class='bx bxs-lock'></i>
              <input
                type="password"
                id="password"
                name="password"
                placeholder="Create a password"
                autocomplete="new-password"
                required
              >
              <button type="button" class="toggle-pw">
                <i class='bx bx-show'></i>
              </button>
            </div>
          </div>

          <!-- Register Button -->
          <div class="login-button">
            <button type="submit">Create Account</button>
          </div>

          <!-- Login Link -->
          <div class="signup-link">
            Already have an account? <a href="{{ route('login') }}">Log in</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="assets/js/script.js"></script>
</body>
</html>