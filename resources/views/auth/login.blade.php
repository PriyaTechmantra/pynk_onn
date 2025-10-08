<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login | ONN & PYNK</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
   

  * {
    box-sizing: border-box;
  }

body, html {
      height: 100%;
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: url('') no-repeat center center fixed;
      background-size: cover;
    }

    .overlay {
      background: rgba(255, 255, 255, 0.9);
      height: 100%;
      width: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
    }
.login-box {
  max-width: 600px;
  margin: 50px auto;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 0 15px rgba(0,0,0,0.1);
  padding: 30px;
  overflow: hidden;
}

.logo-row {
  display: flex;
  justify-content: center;
  gap: 30px;
  margin-bottom: 25px;
}

.logo-row img {
  height: 60px;
  max-width: 100px;
  object-fit: contain;
}

h2 {
  text-align: center;
  margin-bottom: 25px;
  font-size: 22px;
  color: #333;
}

input[type="text"], input[type="password"] {
  width: 100%;
  box-sizing: border-box;
  padding: 12px 15px;
  margin: 10px 0;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 15px;
}



.footer {
  margin-top: 20px;
  text-align: center;
  font-size: 12px;
  color: #777;
}

button {
  width: 100%;
  background: #dc3545; /* Red */
  color: #fff;
  border: none;
  padding: 12px;
  border-radius: 6px;
  font-size: 16px;
  cursor: pointer;
}

button:hover {
  background: #c82333; /* Darker red on hover */
}


  </style>
</head>
<body>

<div class="login-box">
  <div class="logo-row">
    <img src="{{ asset('backend/images/logo.png') }}" alt="ONN Logo">
    <img src="{{ asset('backend/images/Pynk_logo.png') }}" alt="PYNK Logo">
  </div>

  <h2>Login to Your Account</h2>

  <form action="{{route('login')}}" method="POST">
    @csrf
    <input type="text" name="email" placeholder="Email or Username" required>
    @error('email')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
    <input type="password" name="password" placeholder="Password" required>
    @error('password')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
    <button type="submit">Login</button>
  </form>

  <div class="footer">
    © 2025 ONN & PYNK Brands
  </div>
</div>

</body>
</html>
