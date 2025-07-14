<?php
require_once './config/app.php';
require_once './config/database.php';

// Redirect if not logged in
if (!isLoggedIn()) {
  header('Location: signin.php');
  exit;
}

$db = new Database();
$conn = $db->connect();
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([getCurrentUserId()]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TrailerVerse - Profile</title>
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

<body class="bg-slate-950 text-white min-h-screen">

  <?php include './includes/header.php'; ?> <div class="pt-24 max-w-4xl mx-auto px-6 py-8">
    <div class="glass rounded-2xl p-8">
      <div class="flex items-center space-x-6 mb-8">
        <div class="w-24 h-24 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
          <i class="fas fa-user text-white text-2xl"></i>
        </div>
        <div>
          <h1 class="text-3xl font-bold"><?= $user['username'] ?></h1>
          <p class="text-sm text-gray-500 mt-2">
            Member since <?= date('M Y', strtotime($user['created_at'])) ?>
          </p>
        </div>
      </div>

      <div class="grid md:grid-cols-2 gap-8">
        <div>
          <h2 class="text-xl font-semibold mb-4">Profile Information</h2>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">First Name</label>
              <p class="text-white"><?= $user['first_name'] ?: 'Not set' ?></p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">Last Name</label>
              <p class="text-white"><?= $user['last_name'] ?: 'Not set' ?></p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">Bio</label>
              <p class="text-white"><?= $user['bio'] ?: 'No bio added yet' ?></p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">Profile Visibility</label>
              <p class="text-white">
                <i class="fas fa-<?= $user['is_public'] ? 'eye' : 'eye-slash' ?> mr-2"></i>
                <?= $user['is_public'] ? 'Public' : 'Private' ?>
              </p>
            </div>
          </div>
        </div>

        <div>
          <h2 class="text-xl font-semibold mb-4">Movie Stats</h2>
          <div class="space-y-4">
            <div class="glass p-4 rounded-lg">
              <div class="flex items-center justify-between">
                <span class="text-gray-400">Movies Watched</span>
                <span class="text-2xl font-bold">0</span>
              </div>
            </div>
            <div class="glass p-4 rounded-lg">
              <div class="flex items-center justify-between">
                <span class="text-gray-400">Reviews Written</span>
                <span class="text-2xl font-bold">0</span>
              </div>
            </div>
            <div class="glass p-4 rounded-lg">
              <div class="flex items-center justify-between">
                <span class="text-gray-400">Average Rating</span>
                <span class="text-2xl font-bold">-</span>
              </div>
            </div>
            <div class="glass p-4 rounded-lg">
              <div class="flex items-center justify-between">
                <span class="text-gray-400">Achievements</span>
                <span class="text-2xl font-bold">0</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-8 pt-6 border-t border-gray-700">
        <div class="flex space-x-4">
          <button class="px-6 py-2 bg-white text-slate-950 rounded-lg hover:bg-gray-100 transition-colors">
            <i class="fas fa-edit mr-2"></i>Edit Profile
          </button>
          <a href="./index.php" class="px-6 py-2 glass rounded-lg hover:bg-white/10 transition-colors">
            <i class="fas fa-home mr-2"></i>Back to Home
          </a>
        </div>
      </div>
    </div>
  </div>

  <?php include './includes/footer.php'; ?>
</body>

</html>