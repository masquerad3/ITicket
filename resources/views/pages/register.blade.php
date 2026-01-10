<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register</title>
</head>
<body>

  <h1>Create Account</h1>

  @if ($errors->any())
    <div class="error-box" role="alert">
      <ul>
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('register.post') }}">
    @csrf

    <div>
      <label for="first_name">First name</label>
      <input id="first_name" name="first_name" value="{{ old('first_name') }}" required>
    </div>

    <div>
      <label for="last_name">Last name</label>
      <input id="last_name" name="last_name" value="{{ old('last_name') }}" required>
    </div>

    <div>
      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="{{ old('email') }}" required>
    </div>

    <div>
      <label for="contact">Contact</label>
      <input id="contact" name="contact" value="{{ old('contact') }}" required>
    </div>

    <div>
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>
    </div>

    <button type="submit">Register</button>
  </form>

  <p>
    Already have an account? <a href="{{ route('login.show') }}">Log in</a>
  </p>

</body>
</html>