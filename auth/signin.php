<?php
require_once '../config/app.php';
require_once '../config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');

  if (empty($username) || empty($password)) {
    $error = 'Please fill in all fields';
  } else {
    try {
      $db = new Database();
      $conn = $db->connect();

      // Check if user exists
      $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
      $stmt->execute([$username]);
      $user = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($user && password_verify($password, $user['password'])) {
        // Login successful
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: ../index.php');
        exit();
      } else {
        $error = 'Invalid username or password';
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
  <title>TrailerVerse - Sign In</title>
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

<body class="bg-slate-950 text-white flex items-center justify-center min-h-screen">
  <div class="glass p-8 rounded-2xl shadow-2xl w-full max-w-md">
    <div class="text-center mb-8">
      <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center mx-auto mb-4">
        <i class="fas fa-film text-slate-950 text-xl"></i>
      </div>
      <h2 class="text-2xl font-bold">Sign In to TrailerVerse</h2>
      <p class="text-gray-400 mt-2">Welcome back to your cinematic universe</p>
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

    <form action="signin.php" method="POST" class="space-y-6">
      <div>
        <label for="username" class="block text-sm font-medium mb-2">Username</label>
        <input type="text" id="username" name="username" required
          value="<?= htmlspecialchars($username ?? '') ?>"
          class="w-full p-3 rounded-lg glass border border-gray-600 focus:outline-none focus:ring-2 focus:ring-white/50 transition-all">
      </div>
      <div>
        <label for="password" class="block text-sm font-medium mb-2">Password</label>
        <input type="password" id="password" name="password" required
          class="w-full p-3 rounded-lg glass border border-gray-600 focus:outline-none focus:ring-2 focus:ring-white/50 transition-all">
      </div>
      <button type="submit" class="w-full bg-white text-slate-950 font-semibold py-3 rounded-lg hover:bg-gray-100 transition-colors">
        Sign In
      </button>
    </form>

    <p class="text-center text-sm mt-6 text-gray-400">
      Don't have an account?
      <a href="signup.php" class="text-white hover:underline font-medium">Sign Up</a>
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