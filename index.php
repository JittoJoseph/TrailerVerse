<?php
require_once 'config/tmdb_config.php';
require_once 'services/MovieService.php';
require_once 'services/AchievementService.php';

// Get movie data
$movieService = new MovieService();
$trendingMovies = $movieService->getTrendingMovies();
$popularMovies = $movieService->getPopularMovies();

// Get achievements data
$achievementService = new AchievementService();
$achievements = $achievementService->getLatestAchievements();

// Get featured movie (first trending movie)
$featuredMovie = null;
if ($trendingMovies && isset($trendingMovies['results'][0])) {
  $featuredMovie = $trendingMovies['results'][0];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TrailerVerse - Track Your Movie Journey</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            cinema: {
              dark: '#0a0a0a',
              gold: '#d4af37',
              red: '#8b0000'
            }
          }
        }
      }
    }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-cinema-dark text-white overflow-x-hidden">

  <!-- Minimalist Header -->
  <nav class="fixed top-0 w-full z-50 bg-black/90 backdrop-blur-sm border-b border-gray-800">
    <div class="max-w-7xl mx-auto px-6 py-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="w-2 h-8 bg-cinema-gold"></div>
          <h1 class="text-2xl font-light tracking-wider">TRAILERVERSE</h1>
        </div>
        <div class="hidden md:flex items-center space-x-8 text-sm font-light">
          <a href="#" class="text-cinema-gold hover:text-white transition-colors">DISCOVER</a>
          <a href="#" class="hover:text-cinema-gold transition-colors">MOVIES</a>
          <a href="#" class="hover:text-cinema-gold transition-colors">SOCIAL</a>
          <button class="border border-cinema-gold px-6 py-2 hover:bg-cinema-gold hover:text-black transition-all">
            SIGN IN
          </button>
        </div>
      </div>
    </div>
  </nav>

  <!-- Cinematic Hero -->
  <?php if ($featuredMovie): ?>
    <section class="relative h-screen flex items-center justify-center">
      <!-- Background -->
      <div class="absolute inset-0">
        <img src="<?= getTMDBBackdropUrl($featuredMovie['backdrop_path']) ?>"
          alt="<?= htmlspecialchars($featuredMovie['title']) ?>"
          class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-cinema-dark via-cinema-dark/60 to-cinema-dark/30"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-cinema-dark via-transparent to-cinema-dark/80"></div>
      </div>

      <!-- Content -->
      <div class="relative z-10 max-w-7xl mx-auto px-6 text-center">
        <div class="max-w-4xl mx-auto">
          <h1 class="text-6xl md:text-8xl font-thin mb-6 tracking-wider">
            YOUR MOVIE
            <span class="block text-cinema-gold font-light">JOURNEY</span>
          </h1>
          <p class="text-xl md:text-2xl font-light mb-12 text-gray-300 leading-relaxed max-w-2xl mx-auto">
            Track what you watch. Rate what you love. Discover what's next.
          </p>

          <!-- Featured Movie Info -->
          <div class="bg-black/50 backdrop-blur-sm p-8 rounded-lg max-w-2xl mx-auto mb-8 border border-gray-800">
            <div class="flex items-center justify-center space-x-4 mb-4">
              <span class="text-cinema-gold text-sm font-light tracking-wider">NOW TRENDING</span>
              <div class="w-px h-4 bg-gray-600"></div>
              <div class="flex items-center space-x-1">
                <i class="fas fa-star text-cinema-gold text-sm"></i>
                <span class="text-sm"><?= number_format($featuredMovie['vote_average'], 1) ?></span>
              </div>
            </div>
            <h2 class="text-2xl font-light mb-4"><?= htmlspecialchars($featuredMovie['title']) ?></h2>
            <p class="text-gray-300 text-sm leading-relaxed">
              <?= htmlspecialchars(substr($featuredMovie['overview'], 0, 150)) ?>...
            </p>
          </div>

          <button class="bg-cinema-gold text-black px-12 py-4 text-lg font-medium tracking-wider hover:bg-white transition-colors">
            START TRACKING
          </button>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <!-- Movie Collections -->
  <section class="py-20 bg-cinema-dark">
    <div class="max-w-7xl mx-auto px-6">

      <!-- Trending Movies -->
      <?php if ($trendingMovies && isset($trendingMovies['results'])): ?>
        <div class="mb-20">
          <div class="flex items-center mb-12">
            <div class="w-1 h-12 bg-cinema-gold mr-6"></div>
            <h2 class="text-3xl font-thin tracking-wider">TRENDING NOW</h2>
          </div>

          <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
            <?php foreach (array_slice($trendingMovies['results'], 0, 12) as $movie): ?>
              <div class="group cursor-pointer">
                <div class="relative overflow-hidden bg-gray-900">
                  <img src="<?= getTMDBPosterUrl($movie['poster_path']) ?>"
                    alt="<?= htmlspecialchars($movie['title']) ?>"
                    class="w-full h-80 object-cover transition-transform duration-500 group-hover:scale-110">

                  <!-- Minimal overlay -->
                  <div class="absolute inset-0 bg-black/0 group-hover:bg-black/80 transition-all duration-300 flex items-center justify-center">
                    <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-center">
                      <div class="w-12 h-12 border-2 border-cinema-gold rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-play text-cinema-gold"></i>
                      </div>
                      <p class="text-xs font-light tracking-wider">WATCH TRAILER</p>
                    </div>
                  </div>

                  <!-- Rating badge -->
                  <div class="absolute top-3 right-3 bg-black/80 px-2 py-1 text-xs">
                    <i class="fas fa-star text-cinema-gold mr-1"></i>
                    <?= number_format($movie['vote_average'], 1) ?>
                  </div>
                </div>

                <div class="pt-4">
                  <h3 class="font-light text-sm leading-tight">
                    <?= htmlspecialchars(strlen($movie['title']) > 25 ? substr($movie['title'], 0, 25) . '...' : $movie['title']) ?>
                  </h3>
                  <p class="text-xs text-gray-500 mt-1"><?= date('Y', strtotime($movie['release_date'])) ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Popular Movies -->
      <?php if ($popularMovies && isset($popularMovies['results'])): ?>
        <div>
          <div class="flex items-center mb-12">
            <div class="w-1 h-12 bg-white mr-6"></div>
            <h2 class="text-3xl font-thin tracking-wider">POPULAR FILMS</h2>
          </div>

          <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
            <?php foreach (array_slice($popularMovies['results'], 0, 12) as $movie): ?>
              <div class="group cursor-pointer">
                <div class="relative overflow-hidden bg-gray-900">
                  <img src="<?= getTMDBPosterUrl($movie['poster_path']) ?>"
                    alt="<?= htmlspecialchars($movie['title']) ?>"
                    class="w-full h-80 object-cover transition-transform duration-500 group-hover:scale-110">

                  <div class="absolute inset-0 bg-black/0 group-hover:bg-black/80 transition-all duration-300 flex items-center justify-center">
                    <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-center">
                      <div class="w-12 h-12 border-2 border-white rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-play text-white"></i>
                      </div>
                      <p class="text-xs font-light tracking-wider">WATCH TRAILER</p>
                    </div>
                  </div>

                  <div class="absolute top-3 right-3 bg-black/80 px-2 py-1 text-xs">
                    <i class="fas fa-star text-cinema-gold mr-1"></i>
                    <?= number_format($movie['vote_average'], 1) ?>
                  </div>
                </div>

                <div class="pt-4">
                  <h3 class="font-light text-sm leading-tight">
                    <?= htmlspecialchars(strlen($movie['title']) > 25 ? substr($movie['title'], 0, 25) . '...' : $movie['title']) ?>
                  </h3>
                  <p class="text-xs text-gray-500 mt-1"><?= date('Y', strtotime($movie['release_date'])) ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </section>

  <!-- Social Features Preview -->
  <section class="py-20 bg-gradient-to-b from-cinema-dark to-black">
    <div class="max-w-5xl mx-auto px-6 text-center">
      <h2 class="text-4xl font-thin tracking-wider mb-6">CONNECT & DISCOVER</h2>
      <p class="text-xl font-light text-gray-400 mb-16 max-w-3xl mx-auto">
        Follow friends, share reviews, and unlock achievements as you explore cinema together.
      </p>

      <div class="grid md:grid-cols-3 gap-12">
        <div class="text-center">
          <div class="w-20 h-20 border border-cinema-gold rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-users text-cinema-gold text-2xl"></i>
          </div>
          <h3 class="text-lg font-light mb-3 tracking-wider">FOLLOW FRIENDS</h3>
          <p class="text-sm text-gray-400 font-light leading-relaxed">See what your friends are watching and get personalized recommendations.</p>
        </div>

        <div class="text-center">
          <div class="w-20 h-20 border border-cinema-gold rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-star text-cinema-gold text-2xl"></i>
          </div>
          <h3 class="text-lg font-light mb-3 tracking-wider">RATE & REVIEW</h3>
          <p class="text-sm text-gray-400 font-light leading-relaxed">Share your thoughts and discover hidden gems through community ratings.</p>
        </div>

        <div class="text-center">
          <div class="w-20 h-20 border border-cinema-gold rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-trophy text-cinema-gold text-2xl"></i>
          </div>
          <h3 class="text-lg font-light mb-3 tracking-wider">EARN ACHIEVEMENTS</h3>
          <p class="text-sm text-gray-400 font-light leading-relaxed">Unlock badges and showcase your cinematic journey and expertise.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Minimalist Footer -->
  <footer class="border-t border-gray-800 py-12 bg-black">
    <div class="max-w-7xl mx-auto px-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="w-2 h-6 bg-cinema-gold"></div>
          <span class="text-sm font-light tracking-wider">TRAILERVERSE</span>
        </div>
        <p class="text-xs text-gray-500 font-light">BCA 5th Semester Project &copy; 2024</p>
      </div>
    </div>
  </footer>

</body>

</html>