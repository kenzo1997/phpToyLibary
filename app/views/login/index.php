<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(to bottom right, #e0f2ff, #90cdf4);
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }

    .login-container {
      background-color: #fff;
      padding: 2rem;
      border-radius: 1.5rem;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 400px;
    }

    .login-container h2 {
      text-align: center;
      margin-bottom: 1.5rem;
    }

    label {
      display: block;
      margin: 0.5rem 0 0.25rem;
      font-weight: bold;
    }

    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 0.75rem;
      margin-bottom: 1rem;
      border: 1px solid #ccc;
      border-radius: 0.75rem;
      font-size: 1rem;
    }

    .button-group {
      display: flex;
      gap: 1rem;
    }

    button {
      flex: 1;
      padding: 0.75rem;
      font-size: 1rem;
      border: none;
      border-radius: 0.75rem;
      cursor: pointer;
      transition: background-color 0.2s;
    }

    button[type="submit"] {
      background-color: #38a169;
      color: white;
    }

    button[type="submit"]:hover {
      background-color: #2f855a;
    }

    .login-btn {
      background-color: #e2e8f0;
      color: #2d3748;
    }

    .login-btn:hover {
      background-color: #cbd5e0;
    }

    .error-txt {
        color: red;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <?= htmlspecialchars($name) ?>
    <h2>Login</h2>
    <p class="error-txt" ><?= htmlspecialchars($error) ?></p>
    <form action="/login" method="post">
      <label for="username">Username:</label>
      <input type="text" id="username" name="username" required>

      <label for="password">Password:</label>
      <input type="password" id="password" name="password" required>

      <div class="button-group">
        <button type="submit">Login</button>
        <button type="button" class="register-btn" onclick="location.href='/register'">Register</button>
      </div>
    </form>
  </div>
</body>
</html>

