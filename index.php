<?php
require_once 'config/tmdb_config.php';
require_once 'services/MovieService.php';

// Get movie data
$movieService = new MovieService();
$trendingMovies = $movieService->getTrendingMovies();

// Get featured movie (random from top 10)
$featuredMovie = null;
if ($trendingMovies && isset($trendingMovies['results']) && count($trendingMovies['results']) > 0) {
  $topMovies = array_slice($trendingMovies['results'], 0, 10);
  $featuredMovie = $topMovies[array_rand($topMovies)];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title>TrailerVerse - Your Cinematic Universe</title>
  <?php include 'includes/head.php'; ?>
</head>

<body class="bg-slate-950 text-white min-h-screen">

  <?php include 'includes/header.php'; ?>

  <!-- Main Content -->
  <div class="pt-24 grid lg:grid-cols-4 gap-8 max-w-7xl mx-auto px-6">

    <!-- Left Content (3 columns) -->
    <div class="lg:col-span-3">

      <!-- Hero Section -->
      <?php if ($featuredMovie): ?>
        <section class="relative h-96 rounded-2xl overflow-hidden mb-12">
          <img src="<?= getTMDBBackdropUrl($featuredMovie['backdrop_path']) ?>"
            alt="<?= htmlspecialchars($featuredMovie['title']) ?>"
            class="w-full h-full object-cover">

          <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/40 to-transparent"></div>

          <div class="absolute bottom-8 left-8 max-w-lg">
            <h1 class="text-4xl font-bold mb-4 gradient-text">
              <?= htmlspecialchars($featuredMovie['title']) ?>
            </h1>
            <p class="text-gray-300 mb-6 leading-relaxed">
              <?= htmlspecialchars(substr($featuredMovie['overview'], 0, 150)) ?>...
            </p>
            <div class="flex items-center space-x-4">
              <button class="px-6 py-3 bg-white text-slate-950 rounded-lg hover:bg-gray-100 transition-colors font-medium">
                <i class="fas fa-play mr-2"></i>
                Watch Trailer
              </button>
              <button class="px-6 py-3 glass rounded-lg hover:bg-white/10 transition-colors">
                <i class="fas fa-plus mr-2"></i>
                My List
              </button>
            </div>
          </div>

          <div class="absolute top-4 right-4 glass px-3 py-1 rounded-full">
            <i class="fas fa-star text-yellow-400 mr-1"></i>
            <?= number_format($featuredMovie['vote_average'], 1) ?>
          </div>
        </section>
      <?php endif; ?>

      <!-- Trending Movies -->
      <?php if ($trendingMovies && isset($trendingMovies['results'])): ?>
        <section>
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold">Trending Now</h2>
            <button class="text-gray-400 hover:text-white transition-colors">
              View All <i class="fas fa-arrow-right ml-1"></i>
            </button>
          </div>

          <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <?php foreach (array_slice($trendingMovies['results'], 0, 12) as $index => $movie): ?>
              <a href="movie-detail.php?id=<?= $movie['id'] ?>" class="group cursor-pointer">
                <div class="relative rounded-lg overflow-hidden mb-3">
                  <img src="<?= getTMDBPosterUrl($movie['poster_path']) ?>"
                    alt="<?= htmlspecialchars($movie['title']) ?>"
                    class="w-full h-64 object-cover transition-transform group-hover:scale-105">

                  <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <button class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center glass">
                      <i class="fas fa-play text-white"></i>
                    </button>
                  </div>

                  <div class="absolute top-2 left-2 w-6 h-6 bg-white text-slate-950 rounded-full flex items-center justify-center text-xs font-bold">
                    <?= $index + 1 ?>
                  </div>

                  <div class="absolute top-2 right-2 glass px-2 py-1 rounded text-xs">
                    <?= number_format($movie['vote_average'], 1) ?>
                  </div>
                </div>

                <h3 class="text-sm font-medium group-hover:text-gray-300 transition-colors">
                  <?= htmlspecialchars(strlen($movie['title']) > 20 ? substr($movie['title'], 0, 20) . '...' : $movie['title']) ?>
                </h3>
                <p class="text-xs text-gray-500"><?= date('Y', strtotime($movie['release_date'])) ?></p>
              </a>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>
    </div>

    <!-- Right Sidebar (1 column) -->
    <div class="lg:col-span-1 space-y-8">

      <!-- Quick Stats -->
      <div class="glass rounded-2xl p-6">
        <h3 class="text-lg font-semibold mb-4">Community Stats</h3>
        <div class="space-y-4">
          <div class="flex justify-between items-center">
            <span class="text-gray-400">Movies Tracked</span>
            <span class="font-semibold">847K</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-400">Active Users</span>
            <span class="font-semibold">12K</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-400">Reviews Written</span>
            <span class="font-semibold">234K</span>
          </div>
        </div>
      </div>

      <!-- Recent Achievements -->
      <div class="glass rounded-2xl p-6">
        <h3 class="text-lg font-semibold mb-4">Latest Achievements</h3>
        <div class="space-y-3">
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
              <i class="fas fa-star text-slate-950 text-xs"></i>
            </div>
            <div>
              <p class="text-sm font-medium">First Review</p>
              <p class="text-xs text-gray-400">@moviebuff23</p>
            </div>
          </div>

          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
              <i class="fas fa-film text-white text-xs"></i>
            </div>
            <div>
              <p class="text-sm font-medium">10 Movies Watched</p>
              <p class="text-xs text-gray-400">@cinephile_sarah</p>
            </div>
          </div>

          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
              <i class="fas fa-trophy text-white text-xs"></i>
            </div>
            <div>
              <p class="text-sm font-medium">Genre Explorer</p>
              <p class="text-xs text-gray-400">@filmfan_mike</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Trending Genres -->
      <div class="glass rounded-2xl p-6">
        <h3 class="text-lg font-semibold mb-4">Trending Genres</h3>
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-sm">Action</span>
            <div class="w-16 h-2 bg-gray-700 rounded-full overflow-hidden">
              <div class="w-4/5 h-full bg-white rounded-full"></div>
            </div>
          </div>

          <div class="flex items-center justify-between">
            <span class="text-sm">Sci-Fi</span>
            <div class="w-16 h-2 bg-gray-700 rounded-full overflow-hidden">
              <div class="w-3/5 h-full bg-white rounded-full"></div>
            </div>
          </div>

          <div class="flex items-center justify-between">
            <span class="text-sm">Drama</span>
            <div class="w-16 h-2 bg-gray-700 rounded-full overflow-hidden">
              <div class="w-2/5 h-full bg-white rounded-full"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Call to Action -->
      <div class="glass rounded-2xl p-6 text-center">
        <h3 class="text-lg font-semibold mb-2">Join the Community</h3>
        <p class="text-sm text-gray-400 mb-4">Start tracking your movie journey today</p>
        <button class="w-full px-4 py-2 bg-white text-slate-950 rounded-lg hover:bg-gray-100 transition-colors font-medium">
          Get Started
        </button>
      </div>
    </div>
  </div>

  <?php include 'includes/footer.php'; ?>

</body>

</html>