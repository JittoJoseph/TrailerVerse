<?php
require_once './config/app.php';
require_once './services/UserService.php';
require_once './config/tmdb_config.php';

$userService = new UserService();

// Redirect if not logged in
if (!isLoggedIn()) {
  header('Location: signin.php');
  exit;
}

// Fetch user data and related lists
$userId = getCurrentUserId();
$user = $userService->getUserById($userId);
$stats = $userService->getUserStats($userId);
$achCount = $userService->getAchievementCount($userId);
$reviews = $userService->getRecentReviews($userId, 5);
$watchlist = $userService->getWatchlist($userId, 12);
$watchedMovies = $userService->getWatchedMovies($userId, 18);
$achievements = $userService->getAchievements($userId);
$followersCount = $userService->getFollowersCount($userId);
$followingCount = $userService->getFollowingCount($userId);
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

    .profile-cover {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      position: relative;
    }

    .profile-cover::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.3);
    }

    .stat-card {
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    }

    .line-clamp-2 {
      display: -webkit-box;
      -webkit-line-clamp: 2;
      line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
  </style>
</head>

<body class="bg-slate-950 text-white min-h-screen">

  <body class="bg-slate-950 text-white min-h-screen">

    <?php include './includes/header.php'; ?>

    <div class="pt-16">
      <!-- Profile Cover Section -->
      <div class="profile-cover h-64 sm:h-72 lg:h-80 relative">
        <div class="absolute inset-0 z-10 flex items-end">
          <div class="max-w-6xl mx-auto px-6 w-full pb-6">
            <div class="flex flex-col md:flex-row items-start md:items-start space-y-4 md:space-y-0 md:space-x-6">
              <!-- Profile Avatar -->
              <div class="relative">
                <div class="w-32 h-32 md:w-40 md:h-40 rounded-full overflow-hidden bg-gray-700 border-4 border-white shadow-xl">
                  <?php if ($user['profile_picture']): ?>
                    <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="<?= htmlspecialchars($user['username']) ?> avatar" class="object-cover w-full h-full">
                  <?php else: ?>
                    <div class="flex items-center justify-center w-full h-full bg-gradient-to-br from-blue-500 to-purple-600">
                      <i class="fas fa-user text-white text-4xl"></i>
                    </div>
                  <?php endif; ?>
                </div>
                <!-- Online Status Indicator -->
                <div class="absolute bottom-2 right-2 w-6 h-6 bg-green-500 rounded-full border-4 border-white"></div>
              </div>

              <!-- Profile Info -->
              <div class="flex-1 text-white">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                  <div class="flex-1">
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-2"><?= htmlspecialchars($user['username']) ?></h1>
                    <p class="text-base sm:text-lg lg:text-xl text-gray-200 mb-4 max-w-2xl line-clamp-2 leading-relaxed"><?= htmlspecialchars($user['bio'] ?: 'Movie enthusiast exploring cinematic worlds') ?></p>

                    <!-- Badges Row -->
                    <div class="flex flex-wrap items-center gap-3 mb-4">
                      <!-- Favorite Genre or New User Badge -->
                      <?php if ($stats['favorite_genre_name']): ?>
                        <span class="px-3 py-1.5 bg-gradient-to-r from-red-500 to-pink-500 text-white text-sm font-medium rounded-full flex items-center space-x-2 shadow-lg">
                          <i class="fas fa-heart text-xs"></i>
                          <span>Favorite: <?= htmlspecialchars($stats['favorite_genre_name']) ?></span>
                        </span>
                      <?php else: ?>
                        <span class="px-3 py-1.5 bg-gradient-to-r from-blue-500 to-purple-500 text-white text-sm font-medium rounded-full flex items-center space-x-2 shadow-lg">
                          <i class="fas fa-star text-xs"></i>
                          <span>New User</span>
                        </span>
                      <?php endif; ?>

                      <!-- Profile Status Badge -->
                      <span class="px-3 py-1.5 bg-gray-700 bg-opacity-80 text-gray-200 text-sm font-medium rounded-full flex items-center space-x-2">
                        <i class="fas fa-<?= $user['is_public'] ? 'globe' : 'lock' ?> text-xs"></i>
                        <span><?= $user['is_public'] ? 'Public Profile' : 'Private Profile' ?></span>
                      </span>

                      <!-- Member Since Badge -->
                      <span class="px-3 py-1.5 bg-gray-700 bg-opacity-80 text-gray-200 text-sm font-medium rounded-full flex items-center space-x-2">
                        <i class="fas fa-calendar text-xs"></i>
                        <span>Since <?= date('M Y', strtotime($user['created_at'])) ?></span>
                      </span>
                    </div>

                    <!-- Quick Stats Row -->
                    <div class="flex flex-wrap items-center gap-4 sm:gap-6 text-sm">
                      <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-blue-500 bg-opacity-20 rounded-full flex items-center justify-center">
                          <i class="fas fa-film text-blue-400 text-xs"></i>
                        </div>
                        <div>
                          <span class="font-bold text-white"><?= $stats['movies_watched'] ?></span>
                          <span class="text-gray-300 ml-1">watched</span>
                        </div>
                      </div>
                      <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-green-500 bg-opacity-20 rounded-full flex items-center justify-center">
                          <i class="fas fa-users text-green-400 text-xs"></i>
                        </div>
                        <div>
                          <span class="font-bold text-white"><?= $followersCount ?></span>
                          <span class="text-gray-300 ml-1">followers</span>
                        </div>
                      </div>
                      <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-purple-500 bg-opacity-20 rounded-full flex items-center justify-center">
                          <i class="fas fa-user-friends text-purple-400 text-xs"></i>
                        </div>
                        <div>
                          <span class="font-bold text-white"><?= $followingCount ?></span>
                          <span class="text-gray-300 ml-1">following</span>
                        </div>
                      </div>
                      <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-yellow-500 bg-opacity-20 rounded-full flex items-center justify-center">
                          <i class="fas fa-star text-yellow-400 text-xs"></i>
                        </div>
                        <div>
                          <span class="font-bold text-white"><?= $stats['reviews_written'] ?></span>
                          <span class="text-gray-300 ml-1">reviews</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Action Buttons -->
                  <div class="flex flex-col sm:flex-row gap-3 sm:flex-shrink-0">
                    <a href="edit_profile.php" class="px-4 sm:px-6 py-2.5 sm:py-3 bg-white text-slate-950 rounded-lg hover:bg-gray-100 transition font-medium shadow-lg text-center">
                      <i class="fas fa-edit mr-2"></i>Edit Profile
                    </a>
                    <a href="index.php" class="px-4 sm:px-6 py-2.5 sm:py-3 bg-gray-800 bg-opacity-80 text-white rounded-lg hover:bg-gray-700 transition font-medium shadow-lg text-center">
                      <i class="fas fa-home mr-2"></i>Home
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="max-w-6xl mx-auto px-6 py-8">
        <!-- Top Section: Stats and Watchlist Side by Side -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-8 mb-12">

          <!-- Left Column - Stats & Quick Info (2/5 width on lg+, full width on mobile) -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Statistics Card -->
            <div class="glass p-6 rounded-xl stat-card h-fit">
              <h3 class="text-xl font-semibold mb-6 flex items-center">
                <i class="fas fa-chart-line mr-3 text-blue-400"></i>Your Stats
              </h3>
              <?php
              // calculate progress thresholds for watched, watchlist, and achievements
              $watched = $stats['movies_watched'];
              $watchedThresholds = [10, 25, 50, 100];
              foreach ($watchedThresholds as $t) {
                if ($watched < $t) {
                  $nextWatched = $t;
                  break;
                }
              }
              $nextWatched = $nextWatched ?? end($watchedThresholds);
              $watchedPercent = min(100, ($watched / $nextWatched) * 100);

              $watchlistCount = $stats['movies_in_watchlist'];
              $watchlistThresholds = [10, 25, 50];
              foreach ($watchlistThresholds as $t) {
                if ($watchlistCount < $t) {
                  $nextWatchlist = $t;
                  break;
                }
              }
              $nextWatchlist = $nextWatchlist ?? end($watchlistThresholds);
              $watchlistPercent = min(100, ($watchlistCount / $nextWatchlist) * 100);

              $points = $stats['achievement_points'];
              $pointsThresholds = [100, 250, 500];
              foreach ($pointsThresholds as $t) {
                if ($points < $t) {
                  $nextPoints = $t;
                  break;
                }
              }
              $nextPoints = $nextPoints ?? end($pointsThresholds);
              $pointsPercent = min(100, ($points / $nextPoints) * 100);
              ?>
              <div class="space-y-5">
                <!-- Watched Progress -->
                <div>
                  <div class="flex justify-between items-center mb-2">
                    <span class="flex items-center space-x-2">
                      <i class="fas fa-film text-blue-400"></i>
                      <span class="font-medium">Movies Watched</span>
                    </span>
                    <span class="font-bold text-blue-400"><?= $watched ?>/<?= $nextWatched ?></span>
                  </div>
                  <div class="w-full bg-gray-700 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-400 to-blue-500 h-2.5 transition-all duration-500" style="width: <?= $watchedPercent ?>%;"></div>
                  </div>
                </div>

                <!-- Watchlist Progress -->
                <div>
                  <div class="flex justify-between items-center mb-2">
                    <span class="flex items-center space-x-2">
                      <i class="fas fa-bookmark text-yellow-400"></i>
                      <span class="font-medium">Watchlist</span>
                    </span>
                    <span class="font-bold text-yellow-400"><?= $watchlistCount ?>/<?= $nextWatchlist ?></span>
                  </div>
                  <div class="w-full bg-gray-700 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 h-2.5 transition-all duration-500" style="width: <?= $watchlistPercent ?>%;"></div>
                  </div>
                </div>

                <!-- Reviews & Ratings Row -->
                <div class="grid grid-cols-2 gap-3 pt-2">
                  <div class="text-center p-3 bg-gray-800 bg-opacity-50 rounded-lg">
                    <div class="flex items-center justify-center space-x-1 mb-1">
                      <i class="fas fa-comment text-green-400 text-sm"></i>
                      <span class="text-xs font-medium">Reviews</span>
                    </div>
                    <span class="text-xl font-bold text-green-400"><?= $stats['reviews_written'] ?></span>
                  </div>
                  <div class="text-center p-3 bg-gray-800 bg-opacity-50 rounded-lg">
                    <div class="flex items-center justify-center space-x-1 mb-1">
                      <i class="fas fa-star text-yellow-300 text-sm"></i>
                      <span class="text-xs font-medium">Ratings</span>
                    </div>
                    <span class="text-xl font-bold text-yellow-300"><?= $stats['ratings_given'] ?></span>
                  </div>
                </div>

                <!-- Average Rating -->
                <div class="text-center p-3 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg">
                  <div class="flex items-center justify-center space-x-2 mb-1">
                    <i class="fas fa-star-half-alt text-white text-sm"></i>
                    <span class="text-xs font-medium text-white">Average Rating</span>
                  </div>
                  <span class="text-xl font-bold text-white">
                    <?= $stats['average_rating'] > 0 ? number_format($stats['average_rating'], 1) : '-' ?>
                  </span>
                </div>

                <!-- Achievement Points Progress -->
                <div>
                  <div class="flex justify-between items-center mb-2">
                    <span class="flex items-center space-x-2">
                      <i class="fas fa-trophy text-purple-400"></i>
                      <span class="font-medium">Achievement Points</span>
                    </span>
                    <span class="font-bold text-purple-400"><?= $points ?>/<?= $nextPoints ?></span>
                  </div>
                  <div class="w-full bg-gray-700 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-400 to-purple-500 h-2.5 transition-all duration-500" style="width: <?= $pointsPercent ?>%;"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Quick Info Card -->
            <div class="glass p-4 rounded-xl stat-card">
              <h3 class="text-lg font-semibold mb-4 flex items-center">
                <i class="fas fa-info-circle mr-3 text-blue-400"></i>Profile Info
              </h3>
              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <span class="text-gray-400 text-sm">Profile Status</span>
                  <span class="px-2 py-1 bg-green-600 text-white text-xs rounded-full">
                    <?= $user['is_public'] ? 'Public' : 'Private' ?>
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-gray-400 text-sm">Achievements Earned</span>
                  <span class="font-bold text-purple-400"><?= $achCount ?></span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-gray-400 text-sm">Total Points</span>
                  <span class="font-bold text-yellow-400"><?= $stats['achievement_points'] ?></span>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column - Watchlist (3/5 width on lg+, full width on mobile) -->
          <div class="lg:col-span-3">
            <!-- Watchlist Section -->
            <section>
              <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl sm:text-2xl font-bold flex items-center">
                  <i class="fas fa-bookmark mr-3 text-yellow-400"></i>Your Watchlist
                </h2>
                <span class="text-xs sm:text-sm text-gray-400 bg-gray-800 px-2 sm:px-3 py-1 rounded-full"><?= $stats['movies_in_watchlist'] ?> movies</span>
              </div>
              <?php if (empty($watchlist)): ?>
                <div class="glass p-6 sm:p-8 rounded-xl text-center">
                  <i class="fas fa-bookmark text-4xl sm:text-6xl text-gray-600 mb-4"></i>
                  <h3 class="text-lg sm:text-xl font-semibold text-gray-400 mb-2">Your watchlist is empty</h3>
                  <p class="text-sm sm:text-base text-gray-500">Start adding movies you want to watch!</p>
                </div>
              <?php else: ?>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                  <?php foreach (array_slice($watchlist, 0, 10) as $index => $movie): ?>
                    <a href="movie.php?id=<?= htmlspecialchars($movie['movie_id']) ?>" class="group cursor-pointer">
                      <div class="relative rounded-lg overflow-hidden mb-3">
                        <img src="<?= getTMDBImageUrl($movie['poster_path'], 'w300') ?>"
                          alt="<?= htmlspecialchars($movie['title']) ?>"
                          class="w-full h-48 sm:h-52 md:h-56 lg:h-48 object-cover transition-transform group-hover:scale-105">

                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                          <button class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center glass">
                            <i class="fas fa-play text-white text-sm"></i>
                          </button>
                        </div>

                        <div class="absolute top-2 left-2 w-5 h-5 bg-yellow-500 text-slate-950 rounded-full flex items-center justify-center text-xs font-bold">
                          <i class="fas fa-bookmark text-xs"></i>
                        </div>

                        <?php if ($movie['vote_average']): ?>
                          <div class="absolute top-2 right-2 glass px-1.5 py-0.5 rounded text-xs">
                            <i class="fas fa-star text-yellow-400 mr-1"></i>
                            <?= number_format($movie['vote_average'], 1) ?>
                          </div>
                        <?php endif; ?>
                      </div>

                      <h3 class="text-xs sm:text-sm font-medium group-hover:text-gray-300 transition-colors mb-1">
                        <span class="block sm:hidden">
                          <?= strlen($movie['title']) > 12 ? substr($movie['title'], 0, 12) . '...' : $movie['title'] ?>
                        </span>
                        <span class="hidden sm:block md:hidden">
                          <?= strlen($movie['title']) > 14 ? substr($movie['title'], 0, 14) . '...' : $movie['title'] ?>
                        </span>
                        <span class="hidden md:block lg:hidden">
                          <?= strlen($movie['title']) > 15 ? substr($movie['title'], 0, 15) . '...' : $movie['title'] ?>
                        </span>
                        <span class="hidden lg:block">
                          <?= strlen($movie['title']) > 16 ? substr($movie['title'], 0, 16) . '...' : $movie['title'] ?>
                        </span>
                      </h3>
                      <?php if ($movie['release_date']): ?>
                        <p class="text-xs text-gray-500"><?= date('Y', strtotime($movie['release_date'])) ?></p>
                      <?php endif; ?>
                    </a>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </section>
          </div>
        </div>

        <!-- Watched Movies Section - Full Width -->
        <section class="mb-12">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl sm:text-2xl font-bold flex items-center">
              <i class="fas fa-check-circle mr-3 text-green-400"></i>Watched Movies
            </h2>
            <span class="text-xs sm:text-sm text-gray-400 bg-gray-800 px-2 sm:px-3 py-1 rounded-full"><?= $stats['movies_watched'] ?> movies</span>
          </div>

          <?php if (empty($watchedMovies)): ?>
            <div class="glass p-6 sm:p-8 rounded-xl text-center">
              <i class="fas fa-film text-4xl sm:text-6xl text-gray-600 mb-4"></i>
              <h3 class="text-lg sm:text-xl font-semibold text-gray-400 mb-2">No movies watched yet</h3>
              <p class="text-sm sm:text-base text-gray-500">Start watching movies to build your collection!</p>
            </div>
          <?php else: ?>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
              <?php
              foreach (array_slice($watchedMovies, 0, 18) as $index => $movie):
              ?>
                <a href="movie.php?id=<?= htmlspecialchars($movie['movie_id']) ?>" class="group cursor-pointer">
                  <div class="relative rounded-lg overflow-hidden mb-3">
                    <img src="<?= getTMDBImageUrl($movie['poster_path'], 'w300') ?>"
                      alt="<?= htmlspecialchars($movie['title']) ?>"
                      class="w-full h-48 sm:h-52 md:h-56 lg:h-48 object-cover transition-transform group-hover:scale-105">

                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                      <button class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center glass">
                        <i class="fas fa-play text-white text-sm"></i>
                      </button>
                    </div>


                    <!-- User Rating (if available) -->
                    <?php if (!empty($movie['user_rating'])): ?>
                      <div class="absolute top-2 right-2 glass px-1.5 py-0.5 rounded text-xs">
                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                        <?= number_format($movie['user_rating'], 1) ?>
                      </div>
                    <?php elseif ($movie['vote_average']): ?>
                      <div class="absolute top-2 right-2 glass px-1.5 py-0.5 rounded text-xs">
                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                        <?= number_format($movie['vote_average'], 1) ?>
                      </div>
                    <?php endif; ?>
                  </div>

                  <h3 class="text-xs sm:text-sm font-medium group-hover:text-gray-300 transition-colors mb-1">
                    <span class="block sm:hidden">
                      <?= strlen($movie['title']) > 10 ? substr($movie['title'], 0, 10) . '...' : $movie['title'] ?>
                    </span>
                    <span class="hidden sm:block md:hidden">
                      <?= strlen($movie['title']) > 12 ? substr($movie['title'], 0, 12) . '...' : $movie['title'] ?>
                    </span>
                    <span class="hidden md:block lg:hidden">
                      <?= strlen($movie['title']) > 13 ? substr($movie['title'], 0, 13) . '...' : $movie['title'] ?>
                    </span>
                    <span class="hidden lg:block xl:hidden">
                      <?= strlen($movie['title']) > 10 ? substr($movie['title'], 0, 10) . '...' : $movie['title'] ?>
                    </span>
                    <span class="hidden xl:block">
                      <?= strlen($movie['title']) > 12 ? substr($movie['title'], 0, 12) . '...' : $movie['title'] ?>
                    </span>
                  </h3>

                  <?php if ($movie['release_date']): ?>
                    <p class="text-xs text-gray-500"><?= date('Y', strtotime($movie['release_date'])) ?></p>
                  <?php endif; ?>

                </a>
              <?php endforeach; ?>
            </div>

            <?php if (count($watchedMovies) > 16): ?>
              <div class="text-center mt-6">
                <button class="px-6 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:from-green-600 hover:to-green-700 transition-all duration-300 font-medium">
                  <i class="fas fa-eye mr-2"></i>View All Watched Movies
                </button>
              </div>
            <?php endif; ?>
          <?php endif; ?>
        </section>

        <!-- Full Width Sections Below -->
        <div class="space-y-12">

          <!-- Recent Reviews Section -->
          <section>
            <div class="flex items-center justify-between mb-8">
              <h2 class="text-3xl font-bold flex items-center">
                <i class="fas fa-comment mr-4 text-green-400"></i>Recent Reviews
              </h2>
              <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-400 bg-gray-800 px-3 py-1 rounded-full"><?= count($reviews) ?> reviews</span>
                <button class="text-blue-400 hover:text-blue-300 transition-colors text-sm font-medium">
                  View All <i class="fas fa-arrow-right ml-1"></i>
                </button>
              </div>
            </div>
            <?php if (empty($reviews)): ?>
              <div class="glass p-12 rounded-xl text-center">
                <i class="fas fa-comment text-8xl text-gray-600 mb-6"></i>
                <h3 class="text-2xl font-semibold text-gray-400 mb-3">No reviews yet</h3>
                <p class="text-gray-500 text-lg">Share your thoughts on movies you've watched!</p>
              </div>
            <?php else: ?>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($reviews as $rev): ?>
                  <div class="glass p-6 rounded-xl hover:bg-opacity-10 transition-all duration-300 group">
                    <div class="flex items-start space-x-4">
                      <img src="<?= getTMDBImageUrl($rev['poster_path'], 'w92') ?>"
                        alt="<?= htmlspecialchars($rev['title']) ?>"
                        class="w-16 h-24 object-cover rounded-lg flex-shrink-0 group-hover:scale-105 transition-transform duration-300">
                      <div class="flex-1">
                        <h3 class="text-lg font-semibold text-white mb-2 group-hover:text-blue-400 transition-colors"><?= htmlspecialchars($rev['title']) ?></h3>
                        <p class="text-gray-300 text-sm leading-relaxed mb-3">
                          <?= htmlspecialchars(strlen($rev['review_text']) > 150 ? substr($rev['review_text'], 0, 150) . '...' : $rev['review_text']) ?>
                        </p>
                        <div class="flex items-center justify-between">
                          <span class="text-xs text-gray-500 flex items-center">
                            <i class="fas fa-calendar mr-1"></i>
                            <?= date('M j, Y', strtotime($rev['created_at'])) ?>
                          </span>
                          <button class="text-xs text-blue-400 hover:text-blue-300 transition-colors font-medium">Read More</button>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </section>

          <!-- Achievements Section -->
          <section class="mt-12">
            <div class="flex items-center justify-between mb-8">
              <h2 class="text-3xl font-bold flex items-center">
                <i class="fas fa-trophy mr-4 text-yellow-400"></i>Achievements
              </h2>
              <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-400 bg-gray-800 px-4 py-2 rounded-full"><?= count($achievements) ?> earned</span>
              </div>
            </div>

            <?php if (empty($achievements)): ?>
              <div class="glass p-12 rounded-xl text-center">
                <i class="fas fa-trophy text-8xl text-gray-600 mb-6"></i>
                <h3 class="text-2xl font-semibold text-gray-400 mb-3">No achievements yet</h3>
                <p class="text-gray-500 text-lg">Start watching movies and engaging with the platform to unlock achievements!</p>
              </div>
            <?php else: ?>
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                <?php
                // Premium gradient color palette with semantic meanings
                $achievementGradients = [
                  'bg-gradient-to-br from-emerald-400 via-emerald-500 to-emerald-600',
                  'bg-gradient-to-br from-blue-400 via-blue-500 to-blue-600',
                  'bg-gradient-to-br from-purple-400 via-purple-500 to-purple-600',
                  'bg-gradient-to-br from-pink-400 via-pink-500 to-pink-600',
                  'bg-gradient-to-br from-orange-400 via-orange-500 to-orange-600',
                  'bg-gradient-to-br from-indigo-400 via-indigo-500 to-indigo-600',
                  'bg-gradient-to-br from-cyan-400 via-cyan-500 to-cyan-600',
                  'bg-gradient-to-br from-red-400 via-red-500 to-red-600',
                  'bg-gradient-to-br from-yellow-400 via-yellow-500 to-yellow-600',
                  'bg-gradient-to-br from-teal-400 via-teal-500 to-teal-600',
                  'bg-gradient-to-br from-violet-400 via-violet-500 to-violet-600',
                  'bg-gradient-to-br from-rose-400 via-rose-500 to-rose-600',
                  'bg-gradient-to-br from-amber-400 via-amber-500 to-amber-600',
                  'bg-gradient-to-br from-lime-400 via-lime-500 to-lime-600',
                  'bg-gradient-to-br from-sky-400 via-sky-500 to-sky-600',
                  'bg-gradient-to-br from-fuchsia-400 via-fuchsia-500 to-fuchsia-600',
                  'bg-gradient-to-br from-slate-400 via-slate-500 to-slate-600',
                  'bg-gradient-to-br from-emerald-500 via-blue-500 to-purple-500'
                ];

                foreach ($achievements as $index => $ach):
                  $gradientIndex = ($ach['id'] ?? $index) % count($achievementGradients);
                  $gradientBg = $achievementGradients[$gradientIndex];
                ?>
                  <div class="group relative">
                    <!-- Achievement Card -->
                    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-800/50 via-slate-800/30 to-slate-900/50 backdrop-blur-xl border border-white/10 hover:border-white/20 transition-all duration-500 ease-out group-hover:shadow-2xl group-hover:shadow-purple-500/10 group-hover:-translate-y-1">

                      <!-- Gradient Background Overlay -->
                      <div class="absolute inset-0 <?= $gradientBg ?> opacity-5 group-hover:opacity-10 transition-opacity duration-500"></div>

                      <!-- Content -->
                      <div class="relative p-6">
                        <!-- Icon Header -->
                        <div class="flex items-center justify-between mb-4">
                          <div class="relative">
                            <div class="w-14 h-14 <?= $gradientBg ?> rounded-2xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-110">
                              <i class="<?= htmlspecialchars($ach['icon']) ?> text-white text-xl"></i>
                            </div>
                            <!-- Glow Effect -->
                            <div class="absolute inset-0 w-14 h-14 <?= $gradientBg ?> rounded-2xl blur-md opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>
                          </div>

                          <!-- Points Badge -->
                          <div class="flex items-center space-x-1 bg-gradient-to-r from-yellow-400/20 to-orange-400/20 backdrop-blur-sm border border-yellow-400/20 rounded-full px-3 py-1.5">
                            <i class="fas fa-star text-yellow-400 text-xs"></i>
                            <span class="text-yellow-400 font-semibold text-sm">+<?= $ach['points'] ?></span>
                          </div>
                        </div>

                        <!-- Achievement Info -->
                        <div class="space-y-3">
                          <h3 class="font-bold text-lg text-white group-hover:text-transparent group-hover:bg-gradient-to-r group-hover:from-white group-hover:to-gray-300 group-hover:bg-clip-text transition-all duration-300">
                            <?= htmlspecialchars($ach['name']) ?>
                          </h3>

                          <p class="text-gray-400 text-sm leading-relaxed line-clamp-2">
                            <?= htmlspecialchars($ach['description']) ?>
                          </p>

                          <!-- Achievement Meta -->
                          <div class="flex items-center justify-between pt-2 border-t border-white/10">
                            <div class="flex items-center space-x-2 text-xs text-gray-500">
                              <i class="fas fa-calendar-alt"></i>
                              <span>Earned <?= date('M j, Y', strtotime($ach['earned_at'])) ?></span>
                            </div>

                            <!-- Achievement Rarity Indicator -->
                            <div class="flex items-center space-x-1">
                              <?php
                              // Simple rarity based on points
                              $rarity = $ach['points'] >= 100 ? 'Epic' : ($ach['points'] >= 50 ? 'Rare' : 'Common');
                              $rarityColor = $ach['points'] >= 100 ? 'text-purple-400' : ($ach['points'] >= 50 ? 'text-blue-400' : 'text-green-400');
                              ?>
                              <div class="w-2 h-2 <?= str_replace('text-', 'bg-', $rarityColor) ?> rounded-full"></div>
                              <span class="text-xs <?= $rarityColor ?> font-medium"><?= $rarity ?></span>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- Shine Effect on Hover -->
                      <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent -skew-x-12 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000 ease-out"></div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </section>
        </div>

        <?php include './includes/footer.php'; ?>
      </div>
    </div>
  </body>

</html>