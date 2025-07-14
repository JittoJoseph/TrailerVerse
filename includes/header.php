<!-- Navigation -->
<nav class="fixed top-0 w-full z-50 glass">
  <div class="max-w-7xl mx-auto px-6 py-3 flex items-center justify-between">
    <div class="flex items-center space-x-3">
      <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center">
        <i class="fas fa-film text-slate-950"></i>
      </div>
      <a href="index.php" class="text-xl font-semibold hover:text-gray-300 transition-colors">TrailerVerse</a>
    </div>

    <div class="hidden md:flex items-center space-x-8">
      <a href="index.php" class="text-gray-300 hover:text-white transition-colors">Discover</a>
      <a href="#" class="text-gray-300 hover:text-white transition-colors">Genres</a>
      <a href="#" class="text-gray-300 hover:text-white transition-colors">Feed</a>

      <?php if (isset($_SESSION['username'])): ?>
        <div class="flex items-center space-x-4">
          <a href="auth/profile.php" class="text-gray-300 hover:text-white transition-colors">
            <i class="fas fa-user mr-1"></i>Profile
          </a>
          <a href="auth/logout.php" class="px-4 py-2 glass rounded-lg hover:bg-white/10 transition-colors">
            <i class="fas fa-sign-out-alt mr-2"></i>Logout
          </a>
        </div>
      <?php else: ?>
        <div class="flex items-center space-x-4">
          <a href="auth/signin.php" class="text-gray-300 hover:text-white transition-colors">Sign In</a>
          <a href="auth/signup.php" class="px-6 py-2 bg-white text-slate-950 rounded-lg hover:bg-gray-100 transition-colors font-medium">
            Sign Up
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</nav>