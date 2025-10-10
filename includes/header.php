<!-- Navigation -->
<nav class="fixed top-0 w-full z-50 glass">
  <div class="max-w-7xl mx-auto px-6 py-3">
    <!-- Mobile Layout (Stacked) -->
    <div class="md:hidden space-y-4">
      <!-- Top Row: Logo and Menu Button -->
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center">
            <i class="fas fa-film text-slate-950"></i>
          </div>
          <a href="index.php" class="text-xl font-semibold hover:text-gray-300 transition-colors">TrailerVerse</a>
        </div>
        <button id="mobile-menu-button" class="p-2 rounded-lg hover:bg-white/10 transition-colors">
          <i class="fas fa-bars text-white text-xl"></i>
        </button>
      </div>

      <!-- Expandable Menu -->
      <div id="mobile-menu" class="hidden space-y-4 pb-4">
        <!-- Mobile Search -->
        <form action="explore.php" method="GET" class="w-full relative">
          <input type="text"
                 name="search"
                 placeholder="Search movies..."
                 class="w-full px-4 py-3 pl-10 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-white/50 focus:bg-white/20 transition-all">
          <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        </form>

        <!-- Mobile Navigation Links -->
        <div class="grid grid-cols-2 gap-3">
          <a href="index.php" class="flex items-center justify-center px-4 py-3 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
            <i class="fas fa-home mr-2"></i>Discover
          </a>
          <a href="explore.php" class="flex items-center justify-center px-4 py-3 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
            <i class="fas fa-compass mr-2"></i>Explore
          </a>
          <a href="genres.php" class="flex items-center justify-center px-4 py-3 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
            <i class="fas fa-tags mr-2"></i>Genres
          </a>
          <a href="feed.php" class="flex items-center justify-center px-4 py-3 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
            <i class="fas fa-stream mr-2"></i>Feed
          </a>
        </div>

        <!-- Mobile Auth Section -->
        <div class="border-t border-white/10 pt-4">
          <?php if (isset($_SESSION['username'])): ?>
            <div class="flex space-x-3">
              <a href="profile.php" class="flex-1 px-4 py-3 text-center text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                <i class="fas fa-user mr-2"></i>Profile
              </a>
              <a href="auth/logout.php" class="flex-1 px-4 py-3 text-center glass rounded-lg hover:bg-white/10 transition-colors">
                <i class="fas fa-sign-out-alt mr-2"></i>Logout
              </a>
            </div>
          <?php else: ?>
            <div class="flex space-x-3">
              <a href="auth/signin.php" class="flex-1 px-4 py-3 text-center text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                Sign In
              </a>
              <a href="auth/signup.php" class="flex-1 px-4 py-3 text-center bg-white text-slate-950 rounded-lg hover:bg-gray-100 transition-colors font-medium">
                Sign Up
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Desktop Layout (Horizontal) -->
    <div class="hidden md:flex items-center justify-between gap-8">
      <div class="flex items-center space-x-3">
        <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center">
          <i class="fas fa-film text-slate-950"></i>
        </div>
        <a href="index.php" class="text-xl font-semibold hover:text-gray-300 transition-colors">TrailerVerse</a>
      </div>

      <!-- Search Bar -->
      <div class="flex-1 max-w-md">
        <form action="explore.php" method="GET" class="w-full relative">
          <input type="text"
                 name="search"
                 placeholder="Search movies..."
                 class="w-full px-4 py-2 pl-10 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-white/50 focus:bg-white/20 transition-all">
          <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        </form>
      </div>

      <!-- Desktop Navigation -->
      <div class="flex items-center space-x-6">
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
  </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const mobileMenuButton = document.getElementById('mobile-menu-button');
  const mobileMenu = document.getElementById('mobile-menu');

  if (mobileMenuButton && mobileMenu) {
    mobileMenuButton.addEventListener('click', function() {
      const isHidden = mobileMenu.classList.contains('hidden');
      if (isHidden) {
        mobileMenu.classList.remove('hidden');
        mobileMenuButton.innerHTML = '<i class="fas fa-times text-white text-xl"></i>';
      } else {
        mobileMenu.classList.add('hidden');
        mobileMenuButton.innerHTML = '<i class="fas fa-bars text-white text-xl"></i>';
      }
    });
  }
});
</script>