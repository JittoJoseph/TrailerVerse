<?php
require_once 'config/app.php';
require_once 'config/tmdb_config.php';
require_once 'services/MovieService.php';
require_once 'services/AchievementService.php';
require_once 'services/GenreService.php';
require_once 'services/StatsService.php';

$movieService = new MovieService();
$achievementService = new AchievementService();
$genreService = new GenreService();
$statsService = new StatsService();

$trendingMovies = $movieService->getTrendingMovies();
$movies = $trendingMovies['results'] ?? [];
$latestAchievements = $achievementService->getLatestAchievements();
$genres = $genreService->getGenres();

$stats = [
    'movies_tracked' => $statsService->getTotalMoviesTracked(),
    'active_users' => $statsService->getTotalUsers(),
    'reviews' => $statsService->getTotalReviews(),
    'movies_watched' => $statsService->getTotalMoviesWatched(),
];

// Random featured movie from top 12 trending
$featuredCandidates = array_slice($movies, 0, min(12, count($movies)));
$featuredMovie = $featuredCandidates ? $featuredCandidates[array_rand($featuredCandidates)] : null;

// Genre icons and colors mapping
$genreStyles = [
  28 => ['icon' => 'fas fa-bolt', 'color' => 'from-red-500 to-pink-500'], // Action
  35 => ['icon' => 'fas fa-laugh-beam', 'color' => 'from-yellow-500 to-orange-500'], // Comedy
  878 => ['icon' => 'fas fa-rocket', 'color' => 'from-purple-500 to-pink-500'], // Science Fiction
  27 => ['icon' => 'fas fa-ghost', 'color' => 'from-gray-700 to-gray-900'], // Horror
  53 => ['icon' => 'fas fa-skull', 'color' => 'from-red-600 to-red-800'], // Thriller
  16 => ['icon' => 'fas fa-palette', 'color' => 'from-green-500 to-teal-500'], // Animation
  12 => ['icon' => 'fas fa-compass', 'color' => 'from-orange-500 to-red-500'], // Adventure
  80 => ['icon' => 'fas fa-balance-scale', 'color' => 'from-gray-600 to-gray-800'], // Crime
  10751 => ['icon' => 'fas fa-home', 'color' => 'from-blue-500 to-cyan-500'], // Family
  10402 => ['icon' => 'fas fa-music', 'color' => 'from-pink-500 to-rose-500'], // Music
  10752 => ['icon' => 'fas fa-fighter-jet', 'color' => 'from-red-700 to-red-900'], // War
  37 => ['icon' => 'fas fa-hat-cowboy', 'color' => 'from-amber-600 to-yellow-600'], // Western
  // Add more mappings as needed for other genres
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title>TrailerVerse - Your Cinematic Universe</title>
  <?php include 'includes/head.php'; ?>
  <style>
    .glass {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .gradient-text {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .movie-card {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .movie-card:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
    }

    .hero-gradient {
      background: linear-gradient(135deg,
        rgba(0, 0, 0, 0.1) 0%,
        rgba(0, 0, 0, 0.1) 50%,
        rgba(0, 0, 0, 0.1) 100%);
    }

    .floating-animation {
      animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
    }

    .scrollbar-hide::-webkit-scrollbar {
      display: none;
    }

    .scrollbar-hide {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }
  </style>
</head>

<body class="bg-slate-950 text-white min-h-screen overflow-x-hidden">

  <?php include 'includes/header.php'; ?>

  <!-- Main Discover Page -->
  <div class="pt-6 lg:pt-24 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 md:px-6">

      <!-- Compact Hero Section -->
      <?php if ($featuredMovie): ?>
        <div class="relative mb-6 lg:mb-12">
          <!-- Mobile: Compact Card Layout -->
          <div class="block lg:hidden">
            <div class="glass rounded-2xl overflow-hidden border border-white/5">
              <div class="relative">
                <!-- Background Image -->
                <div class="relative h-48">
                  <img src="<?= getTMDBBackdropUrl($featuredMovie['backdrop_path']) ?>"
                       alt="<?= $featuredMovie['title'] ?>"
                       class="w-full h-full object-cover">
                  <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/50 to-transparent"></div>
                </div>

                <!-- Content Overlay -->
                <div class="relative p-4">
                  <div class="flex items-start space-x-4">
                    <!-- Poster -->
                    <div class="flex-shrink-0">
                      <img src="<?= getTMDBPosterUrl($featuredMovie['poster_path']) ?>"
                           alt="<?= $featuredMovie['title'] ?>"
                           class="w-20 h-auto rounded-lg shadow-lg border border-white/10">
                    </div>

                    <!-- Movie Info -->
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center space-x-2 mb-2">
                        <div class="w-1 h-4 bg-gradient-to-b from-red-500 to-orange-500 rounded-full"></div>
                        <span class="text-xs font-medium text-gray-300 tracking-wider uppercase">
                          Featured Movie
                        </span>
                      </div>

                      <h1 class="text-lg font-bold text-white leading-tight mb-2 line-clamp-2">
                        <?= $featuredMovie['title'] ?>
                      </h1>

                      <div class="flex items-center space-x-3 mb-3">
                        <div class="flex items-center space-x-1">
                          <i class="fas fa-star text-yellow-400 text-xs"></i>
                          <span class="text-white font-semibold text-sm"><?= number_format($featuredMovie['vote_average'], 1) ?></span>
                        </div>
                        <div class="w-px h-3 bg-white/20"></div>
                        <div class="flex items-center space-x-1">
                          <i class="fas fa-calendar text-gray-400 text-xs"></i>
                          <span class="text-gray-400 text-xs"><?= date('Y', strtotime($featuredMovie['release_date'])) ?></span>
                        </div>
                      </div>

                      <a href="movie.php?id=<?= htmlspecialchars($featuredMovie['id']) ?>"
                         class="inline-flex items-center px-4 py-2 bg-white text-slate-950 rounded-lg hover:bg-gray-100 transition-all duration-300 font-medium text-sm shadow-md hover:shadow-lg transform hover:scale-105">
                        <i class="fas fa-play mr-2"></i>
                        Watch Now
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Desktop: Full Hero Layout -->
          <div class="hidden lg:block">
            <div class="relative overflow-hidden rounded-3xl bg-slate-900/50 backdrop-blur-sm border border-white/5">
              <div class="relative min-h-[28rem] flex items-center py-8">
                <!-- Background Image with Overlay -->
                <div class="absolute inset-0">
                  <img src="<?= getTMDBBackdropUrl($featuredMovie['backdrop_path']) ?>"
                       alt="<?= $featuredMovie['title'] ?>"
                       class="w-full h-full object-cover opacity-20">
                  <div class="absolute inset-0 bg-gradient-to-r from-slate-950/90 via-slate-950/60 to-slate-950/30"></div>
                </div>

                <!-- Content -->
                <div class="relative z-10 max-w-7xl mx-auto px-8 w-full">
                  <div class="grid grid-cols-2 gap-12 items-center">
                    <!-- Movie Info -->
                    <div class="space-y-6">
                      <div class="flex items-center space-x-3">
                        <div class="w-1 h-8 bg-gradient-to-b from-red-500 to-orange-500 rounded-full"></div>
                        <span class="text-sm font-medium text-gray-300 tracking-wider uppercase">
                          Featured Movie
                        </span>
                      </div>

                      <h1 class="text-5xl font-bold text-white leading-tight">
                        <?= $featuredMovie['title'] ?>
                      </h1>

                      <p class="text-gray-400 text-base leading-relaxed max-w-lg">
                        <?= substr($featuredMovie['overview'], 0, 180) ?>...
                      </p>

                      <div class="flex items-center space-x-6">
                        <div class="flex items-center space-x-2">
                          <i class="fas fa-star text-yellow-400"></i>
                          <span class="text-white font-semibold text-lg"><?= number_format($featuredMovie['vote_average'], 1) ?></span>
                        </div>
                        <div class="w-px h-6 bg-white/20"></div>
                        <div class="flex items-center space-x-2">
                          <i class="fas fa-calendar text-gray-400"></i>
                          <span class="text-gray-400 text-sm"><?= date('Y', strtotime($featuredMovie['release_date'])) ?></span>
                        </div>
                      </div>

                      <div class="flex flex-wrap items-center gap-4 pt-4">
                        <a href="movie.php?id=<?= htmlspecialchars($featuredMovie['id']) ?>"
                           class="px-8 py-4 bg-white text-slate-950 rounded-xl hover:bg-gray-100 transition-all duration-300 font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 inline-flex items-center">
                          <i class="fas fa-play mr-3"></i>
                          Watch Now
                        </a>
                      </div>
                    </div>

                    <!-- Movie Poster -->
                    <div class="flex justify-end">
                      <div class="relative floating-animation">
                        <img src="<?= getTMDBPosterUrl($featuredMovie['poster_path']) ?>"
                             alt="<?= $featuredMovie['title'] ?>"
                             class="w-64 xl:w-72 h-auto rounded-2xl shadow-2xl border border-white/10">
                        <div class="absolute -inset-6 bg-gradient-to-r from-slate-800/20 to-slate-700/20 rounded-3xl blur-2xl -z-10"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 lg:gap-8">

        <!-- Trending Movies Section (8 columns on xl) -->
        <div class="xl:col-span-8 space-y-6">
          <!-- Section Header -->
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-2xl lg:text-3xl font-bold flex items-center">
                <i class="fas fa-trending-up text-white mr-3"></i>
                Trending Now
              </h2>
              <p class="text-gray-400 text-sm mt-1">Discover what's popular this week</p>
            </div>
            <a href="explore.php" class="px-4 py-2 glass rounded-xl hover:bg-white/10 transition-all duration-300 text-sm font-medium inline-flex items-center">
              View All
              <i class="fas fa-arrow-right ml-2"></i>
            </a>
          </div>

          <!-- Movies Grid -->
          <?php if ($movies): ?>
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 xl:grid-cols-7 gap-2 sm:gap-3 md:gap-4">
              <?php foreach (array_slice($movies, 0, 14) as $index => $movie): ?>
                <div class="movie-card group cursor-pointer <?= $index >= 12 ? 'hidden lg:block' : '' ?>">
                  <a href="movie.php?id=<?= htmlspecialchars($movie['id']) ?>" class="block">
                    <div class="relative rounded-xl overflow-hidden mb-2 aspect-[2/3] bg-gray-800">
                      <img src="<?= getTMDBPosterUrl($movie['poster_path']) ?>"
                           alt="<?= $movie['title'] ?>"
                           class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">

                      <!-- Overlay -->
                      <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                      <!-- Play Button -->
                      <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center transform scale-75 group-hover:scale-100 transition-transform duration-300">
                          <i class="fas fa-play text-white text-xs sm:text-sm"></i>
                        </div>
                      </div>

                      <!-- Rating Badge -->
                      <div class="absolute top-2 right-2 glass px-1.5 py-0.5 sm:px-2 sm:py-1 rounded-full text-xs font-medium">
                        <i class="fas fa-star text-yellow-400 mr-0.5 sm:mr-1"></i>
                        <?= number_format($movie['vote_average'], 1) ?>
                      </div>

                      <!-- Hover Info -->
                      <div class="absolute bottom-0 left-0 right-0 p-2 sm:p-3 transform translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                        <h3 class="font-semibold text-white text-xs sm:text-sm leading-tight mb-0.5 sm:mb-1 line-clamp-2">
                          <?= $movie['title'] ?>
                        </h3>
                        <p class="text-gray-300 text-xs">
                          <?= date('Y', strtotime($movie['release_date'])) ?>
                        </p>
                      </div>
                    </div>
                  </a>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <!-- Browse by Genre Section -->
          <div class="glass rounded-2xl p-6 md:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 md:mb-8 gap-4 sm:gap-0">
              <div>
                <h3 class="text-xl md:text-2xl font-bold flex items-center mb-1 md:mb-2">
                  <i class="fas fa-th-large text-white mr-2 md:mr-3"></i>
                  Browse by Genre
                </h3>
                <p class="text-gray-400 text-xs md:text-sm">Discover movies by your favorite categories</p>
              </div>
              <a href="genres.php" class="px-3 py-1.5 md:px-4 md:py-2 glass rounded-xl hover:bg-white/10 transition-all duration-300 text-xs md:text-sm font-medium inline-flex items-center justify-center sm:justify-start">
                View All
                <i class="fas fa-arrow-right ml-1.5 md:ml-2"></i>
              </a>
            </div>

            <div class="grid grid-cols-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 gap-3 md:gap-4">
              <?php foreach ($genres as $genre):
                $genreId = $genre['id'];
                $style = $genreStyles[$genreId] ?? ['icon' => 'fas fa-film', 'color' => 'from-slate-600 to-slate-800'];
              ?>
                <a href="genre-movies.php?id=<?= $genre['id'] ?>" class="group">
                  <div class="bg-gradient-to-br <?= $style['color'] ?> rounded-2xl p-4 md:p-6 text-center hover:scale-105 transition-all duration-300 cursor-pointer shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-white/20 rounded-xl flex items-center justify-center mx-auto mb-2 md:mb-3 group-hover:bg-white/30 transition-colors">
                      <i class="<?= $style['icon'] ?> text-white text-lg md:text-xl"></i>
                    </div>
                    <div class="text-white font-semibold text-xs md:text-sm group-hover:text-gray-100 transition-colors">
                      <?= htmlspecialchars($genre['name']) ?>
                    </div>
                    <div class="mt-1 md:mt-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                      <div class="w-6 h-0.5 md:w-8 md:h-1 bg-white/50 rounded-full mx-auto"></div>
                    </div>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Sidebar (4 columns on xl) -->
        <div class="xl:col-span-4 space-y-6">

          <!-- Community Stats -->
          <div class="glass rounded-2xl p-6">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-xl font-semibold flex items-center">
                <i class="fas fa-chart-line text-white mr-3"></i>
                Community Pulse
              </h3>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="text-center p-4 bg-white/5 rounded-xl border border-white/10">
                <div class="text-2xl font-bold text-white mb-1"><?= number_format($stats['movies_tracked']) ?></div>
                <div class="text-xs text-gray-400">Movies Tracked</div>
              </div>
              <div class="text-center p-4 bg-white/5 rounded-xl border border-white/10">
                <div class="text-2xl font-bold text-white mb-1"><?= number_format($stats['active_users']) ?></div>
                <div class="text-xs text-gray-400">Active Users</div>
              </div>
              <div class="text-center p-4 bg-white/5 rounded-xl border border-white/10">
                <div class="text-2xl font-bold text-white mb-1"><?= number_format($stats['reviews']) ?></div>
                <div class="text-xs text-gray-400">Reviews</div>
              </div>
              <div class="text-center p-4 bg-white/5 rounded-xl border border-white/10">
                <div class="text-2xl font-bold text-white mb-1"><?= number_format($stats['movies_watched']) ?></div>
                <div class="text-xs text-gray-400">Movies Watched</div>
              </div>
            </div>
          </div>

          <!-- Recent Achievements -->
          <div class="glass rounded-2xl p-6">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-xl font-semibold flex items-center">
                <i class="fas fa-trophy text-white mr-3"></i>
                Latest Achievements
              </h3>
              <a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">
                View All
              </a>
            </div>

            <div class="space-y-4 max-h-80 overflow-y-auto scrollbar-hide">
              <?php
              $achievementColors = [
                'bg-gradient-to-br from-emerald-400 to-teal-500',
                'bg-gradient-to-br from-blue-400 to-indigo-500',
                'bg-gradient-to-br from-purple-400 to-pink-500',
                'bg-gradient-to-br from-orange-400 to-red-500',
                'bg-gradient-to-br from-cyan-400 to-blue-500',
                'bg-gradient-to-br from-yellow-400 to-orange-500'
              ];

              foreach ($latestAchievements as $index => $achievement):
                $colorIndex = ($achievement['id'] ?? $index) % count($achievementColors);
                $bgColor = $achievementColors[$colorIndex];
              ?>
                <div class="flex items-center space-x-4 p-3 rounded-xl hover:bg-white/5 transition-colors duration-200 group">
                  <div class="w-12 h-12 <?= $bgColor ?> rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform duration-200 shadow-lg">
                    <i class="<?= htmlspecialchars($achievement['icon']) ?> text-white text-lg"></i>
                  </div>

                  <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-white text-sm truncate group-hover:text-gray-200 transition-colors">
                      <?= htmlspecialchars($achievement['name']) ?>
                    </h4>
                    <div class="flex items-center justify-between mt-1">
                      <p class="text-xs text-gray-400">
                        <i class="fas fa-user text-xs mr-1"></i>
                        @<?= htmlspecialchars($achievement['username']) ?>
                      </p>
                      <div class="flex items-center space-x-1 bg-white/10 rounded-full px-2 py-0.5">
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <span class="text-yellow-400 font-medium text-xs">+<?= $achievement['points'] ?? 10 ?></span>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>

              <?php if (empty($latestAchievements)): ?>
                <div class="text-center py-8">
                  <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-trophy text-gray-400 text-2xl"></i>
                  </div>
                  <h4 class="font-semibold text-gray-400 mb-2">No Recent Achievements</h4>
                  <p class="text-sm text-gray-500">Start watching movies to unlock achievements!</p>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Call to Action -->
          <div class="glass rounded-2xl p-6 text-center bg-white/5 border border-white/10">
            <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
              <i class="fas fa-rocket text-white text-2xl"></i>
            </div>
            <h3 class="text-xl font-semibold mb-2">Join the Community</h3>
            <p class="text-gray-400 mb-6 text-sm leading-relaxed">
              Start your cinematic journey and connect with fellow movie enthusiasts
            </p>

            <?php if (isset($_SESSION['user_id'])): ?>
              <a href="profile.php" class="inline-block w-full px-6 py-3 bg-white text-slate-950 rounded-xl hover:bg-gray-100 transition-all duration-300 font-medium shadow-lg hover:shadow-xl transform hover:scale-105">
                <i class="fas fa-user mr-2"></i>
                View Profile
              </a>
            <?php else: ?>
              <div class="space-y-3">
                <a href="auth/signup.php" class="inline-block w-full px-6 py-3 bg-white text-slate-950 rounded-xl hover:bg-gray-100 transition-all duration-300 font-medium shadow-lg hover:shadow-xl transform hover:scale-105">
                  <i class="fas fa-user-plus mr-2"></i>
                  Get Started
                </a>
                <a href="auth/signin.php" class="inline-block w-full px-6 py-3 glass rounded-xl hover:bg-white/10 transition-all duration-300 font-medium">
                  <i class="fas fa-sign-in-alt mr-2"></i>
                  Sign In
                </a>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include 'includes/footer.php'; ?>

</body>

</html>