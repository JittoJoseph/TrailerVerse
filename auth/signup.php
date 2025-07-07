<?php
require_once '../config/app.php';
require_once '../config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $confirm_password = trim($_POST['confirm_password'] ?? '');

  // Basic validation
  if (empty($username) || empty($password) || empty($confirm_password)) {
    $error = 'Please fill in all fields';
  } elseif ($password !== $confirm_password) {
    $error = 'Passwords do not match';
  } elseif (strlen($password) < 6) {
    $error = 'Password must be at least 6 characters long';
  } elseif (strlen($username) < 3) {
    $error = 'Username must be at least 3 characters long';
  } elseif (strlen($username) > 20) {
    $error = 'Username must be less than 20 characters';
  } else {
    try {
      $db = new Database();
      $conn = $db->connect();

      // Check if username already exists
      $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
      $stmt->execute([$username]);
      if ($stmt->fetch()) {
        $error = 'Username already exists';
      } else {
        // Create new user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");

        if ($stmt->execute([$username, $hashed_password])) {
          $success = 'Account created successfully! You can now sign in.';
          // Clear form data
          $username = '';
        } else {
          $error = 'Failed to create account. Please try again.';
        }
      }
    } catch (PDOException $e) {
      $error = 'Connection failed. Please try again.';
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TrailerVerse - Sign Up</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            'slate': {
              950: '#0a0a0a'
            }
          }
        }
      }
    }
  </script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    body {
      font-family: 'Inter', sans-serif;
    }

    .glass {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }
  </style>
</head>

<body class="bg-slate-950 text-white flex items-center justify-center min-h-screen p-4">
  <div class="glass p-8 rounded-2xl shadow-2xl w-full max-w-md">
    <div class="text-center mb-8">
      <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center mx-auto mb-4">
        <i class="fas fa-film text-slate-950 text-xl"></i>
      </div>
      <h2 class="text-2xl font-bold">Join TrailerVerse</h2>
      <p class="text-gray-400 mt-2">Start your cinematic journey today</p>
    </div>

    <?php if ($error): ?>
      <div class="bg-red-500/20 border border-red-500/50 text-red-300 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="bg-green-500/20 border border-green-500/50 text-green-300 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-check-circle mr-2"></i>
        <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <form action="signup.php" method="POST" class="space-y-6">
      <div>
        <label for="username" class="block text-sm font-medium mb-2">Username</label>
        <input type="text" id="username" name="username" required
          value="<?= htmlspecialchars($username ?? '') ?>"
          class="w-full p-3 rounded-lg glass border border-gray-600 focus:outline-none focus:ring-2 focus:ring-white/50 transition-all">
        <p class="text-xs text-gray-400 mt-1">3-20 characters, unique username</p>
      </div>
      <div>
        <label for="password" class="block text-sm font-medium mb-2">Password</label>
        <input type="password" id="password" name="password" required
          class="w-full p-3 rounded-lg glass border border-gray-600 focus:outline-none focus:ring-2 focus:ring-white/50 transition-all">
        <p class="text-xs text-gray-400 mt-1">At least 6 characters</p>
      </div>
      <div>
        <label for="confirm_password" class="block text-sm font-medium mb-2">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required
          class="w-full p-3 rounded-lg glass border border-gray-600 focus:outline-none focus:ring-2 focus:ring-white/50 transition-all">
      </div>
      <button type="submit" class="w-full bg-white text-slate-950 font-semibold py-3 rounded-lg hover:bg-gray-100 transition-colors">
        Create Account
      </button>
    </form>

    <p class="text-center text-sm mt-6 text-gray-400">
      Already have an account?
      <a href="signin.php" class="text-white hover:underline font-medium">Sign In</a>
    </p>

    <div class="text-center mt-6">
      <a href="../index.php" class="text-gray-400 hover:text-white transition-colors text-sm">
        <i class="fas fa-arrow-left mr-2"></i>
        Back to Home
      </a>
    </div>
  </div>
</body>

</html>