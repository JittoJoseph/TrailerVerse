<!-- Navigation -->
<nav class="fixed top-0 w-full z-50 glass">
  <div class="max-w-7xl mx-auto px-6 py-3 flex items-center justify-between gap-8">
    <div class="flex items-center space-x-3">
      <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center">
        <i class="fas fa-film text-slate-950"></i>
      </div>
      <a href="index.php" class="text-xl font-semibold hover:text-gray-300 transition-colors">TrailerVerse</a>
    </div>

    <!-- Search Bar -->
    <div class="hidden md:flex flex-1 max-w-md">
      <form action="explore.php" method="GET" class="w-full relative">
        <input type="text" 
               name="search" 
               placeholder="Search movies..." 
               class="w-full px-4 py-2 pl-10 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-white/50 focus:bg-white/20 transition-all">
        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
      </form>
    </div>

    <div class="hidden md:flex items-center space-x-6">
      <a href="index.php" class="text-gray-300 hover:text-white transition-colors">Discover</a>
      <a href="explore.php" class="text-gray-300 hover:text-white transition-colors">Explore</a>
      <a href="genres.php" class="text-gray-300 hover:text-white transition-colors">Genres</a>
      <a href="feed.php" class="text-gray-300 hover:text-white transition-colors">Feed</a>

      <?php if (isset($_SESSION['username'])): ?>
        <div class="flex items-center space-x-4">
          <a href="profile.php" class="text-gray-300 hover:text-white transition-colors">
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