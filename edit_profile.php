<?php
require_once './config/app.php';
require_once './services/UserService.php';

$userService = new UserService();

// Redirect if not logged in
if (!isLoggedIn()) {
  header('Location: signin.php');
  exit;
}

$currentUserId = getCurrentUserId();
$user = $userService->getUserById($currentUserId);

if (!$user) {
  http_response_code(404);
  echo '<!DOCTYPE html><html><head><title>Profile Not Found</title></head><body style="background:#0a0a0a;color:#fff;font-family:Inter, sans-serif;display:flex;align-items:center;justify-content:center;height:100vh">Profile not found.</body></html>';
  exit;
}

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $firstName = trim($_POST['first_name'] ?? '');
  $lastName = trim($_POST['last_name'] ?? '');
  $bio = trim($_POST['bio'] ?? '');
  $isPublic = isset($_POST['is_public']) ? 1 : 0;

  // Validation
  if (empty($username)) {
    $errors[] = 'Username is required';
  } elseif (strlen($username) > 20) {
    $errors[] = 'Username must be 20 characters or less';
  } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors[] = 'Username can only contain letters, numbers, and underscores';
  } else {
    // Check if username is taken by another user
    if (!$userService->isUsernameAvailable($username, $currentUserId)) {
      $errors[] = 'Username is already taken';
    }
  }

  if (strlen($firstName) > 15) {
    $errors[] = 'First name must be 15 characters or less';
  }

  if (strlen($lastName) > 20) {
    $errors[] = 'Last name must be 20 characters or less';
  }

  if (strlen($bio) > 500) {
    $errors[] = 'Bio must be 500 characters or less';
  }

  // If no errors, update profile
  if (empty($errors)) {
    $updateData = [
      'username' => $username,
      'first_name' => $firstName,
      'last_name' => $lastName,
      'bio' => $bio,
      'is_public' => $isPublic
    ];

    if ($userService->updateProfile($currentUserId, $updateData)) {
      $success = true;
      // Refresh user data
      $user = $userService->getUserById($currentUserId);
      // Redirect after 2 seconds
      header('refresh:2;url=profile.php');
    } else {
      $errors[] = 'Failed to update profile. Please try again.';
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TrailerVerse - Edit Profile</title>
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

    .form-input {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      transition: all 0.3s ease;
    }

    .form-input:focus {
      background: rgba(255, 255, 255, 0.08);
      border-color: rgba(59, 130, 246, 0.5);
      box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
    }

    .error-message {
      background: rgba(239, 68, 68, 0.1);
      border: 1px solid rgba(239, 68, 68, 0.2);
      color: #ef4444;
    }

    .success-message {
      background: rgba(34, 197, 94, 0.1);
      border: 1px solid rgba(34, 197, 94, 0.2);
      color: #22c55e;
    }
  </style>
</head>

<body class="bg-slate-950 text-white min-h-screen">

  <?php include './includes/header.php'; ?>

  <div class="pt-16">
    <div class="max-w-4xl mx-auto px-6 py-8">
      <div class="mb-8">
        <h1 class="text-3xl font-bold mb-2">Edit Profile</h1>
        <p class="text-gray-400">Update your profile information</p>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="error-message p-4 rounded-lg mb-6">
          <div class="flex items-center mb-2">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <span class="font-medium">Please fix the following errors:</span>
          </div>
          <ul class="list-disc list-inside space-y-1">
            <?php foreach ($errors as $error): ?>
              <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="success-message p-4 rounded-lg mb-6">
          <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span class="font-medium">Profile updated successfully! Redirecting...</span>
          </div>
        </div>
      <?php endif; ?>

      <form method="POST" class="space-y-8">
        <!-- Basic Information -->
        <div class="glass p-6 rounded-xl">
          <h2 class="text-xl font-semibold mb-6 flex items-center">
            <i class="fas fa-user mr-3 text-blue-400"></i>Basic Information
          </h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="username" class="block text-sm font-medium text-gray-300 mb-2">
                Username <span class="text-red-400">*</span>
              </label>
              <input
                type="text"
                id="username"
                name="username"
                value="<?= htmlspecialchars($user['username']) ?>"
                required
                pattern="[a-zA-Z0-9_]+"
                maxlength="20"
                class="form-input w-full px-4 py-3 rounded-lg text-white placeholder-gray-500 focus:outline-none"
              >
              <p class="text-xs text-gray-500 mt-1">
                Letters, numbers, and underscores only. Max 20 characters.
              </p>
            </div>

            <div>
              <label for="first_name" class="block text-sm font-medium text-gray-300 mb-2">
                First Name
              </label>
              <input
                type="text"
                id="first_name"
                name="first_name"
                value="<?= htmlspecialchars($user['first_name'] ?? '') ?>"
                maxlength="15"
                class="form-input w-full px-4 py-3 rounded-lg text-white placeholder-gray-500 focus:outline-none"
              >
            </div>

            <div class="md:col-span-2">
              <label for="last_name" class="block text-sm font-medium text-gray-300 mb-2">
                Last Name
              </label>
              <input
                type="text"
                id="last_name"
                name="last_name"
                value="<?= htmlspecialchars($user['last_name'] ?? '') ?>"
                maxlength="20"
                class="form-input w-full px-4 py-3 rounded-lg text-white placeholder-gray-500 focus:outline-none"
              >
            </div>
          </div>
        </div>

        <!-- Bio Section -->
        <div class="glass p-6 rounded-xl">
          <h2 class="text-xl font-semibold mb-6 flex items-center">
            <i class="fas fa-edit mr-3 text-blue-400"></i>About You
          </h2>

          <div>
            <label for="bio" class="block text-sm font-medium text-gray-300 mb-2">
              Bio
            </label>
            <textarea
              id="bio"
              name="bio"
              rows="4"
              maxlength="500"
              class="form-input w-full px-4 py-3 rounded-lg text-white placeholder-gray-500 focus:outline-none resize-vertical"
              placeholder="Tell us about your movie preferences, favorite genres, or anything else you'd like to share..."
            ><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
            <p class="text-xs text-gray-500 mt-1">
              Max 500 characters. <span id="bio-count">0</span>/500
            </p>
          </div>
        </div>

        <!-- Privacy Settings -->
        <div class="glass p-6 rounded-xl">
          <h2 class="text-xl font-semibold mb-6 flex items-center">
            <i class="fas fa-shield-alt mr-3 text-blue-400"></i>Privacy Settings
          </h2>

          <div class="flex items-center">
            <input
              type="checkbox"
              id="is_public"
              name="is_public"
              value="1"
              <?= $user['is_public'] ? 'checked' : '' ?>
              class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
            >
            <label for="is_public" class="ml-3 text-sm font-medium text-gray-300">
              Make my profile public
            </label>
          </div>
          <p class="text-xs text-gray-500 mt-2">
            Public profiles can be viewed by other users and appear in search results.
          </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 pt-6">
          <button
            type="submit"
            class="px-8 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-300 font-medium shadow-lg flex-1 sm:flex-none"
          >
            <i class="fas fa-save mr-2"></i>Save Changes
          </button>

          <a
            href="profile.php"
            class="px-8 py-3 glass text-white rounded-lg hover:bg-white/10 transition-all duration-300 font-medium shadow-lg text-center flex-1 sm:flex-none"
          >
            <i class="fas fa-times mr-2"></i>Cancel
          </a>
        </div>
      </form>
    </div>
  </div>

  <?php include './includes/footer.php'; ?>

  <script>
    // Character counter for bio
    const bioTextarea = document.getElementById('bio');
    const bioCount = document.getElementById('bio-count');

    function updateBioCount() {
      const count = bioTextarea.value.length;
      bioCount.textContent = count;
      bioCount.className = count > 450 ? 'text-yellow-400' : count > 500 ? 'text-red-400' : 'text-gray-500';
    }

    bioTextarea.addEventListener('input', updateBioCount);
    updateBioCount(); // Initial count
  </script>
</body>

</html>