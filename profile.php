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
$activities = $userService->getRecentActivities($userId, 10);
$reviews = $userService->getRecentReviews($userId, 5);
$watchlist = $userService->getWatchlist($userId, 12);
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
  </style>
</head>

<body class="bg-slate-950 text-white min-h-screen">

  <body class="bg-slate-950 text-white min-h-screen">

    <?php include './includes/header.php'; ?>

    <div class="pt-16">
      <!-- Profile Cover Section -->
      <div class="profile-cover h-64 relative">
        <div class="absolute inset-0 z-10 flex items-end">
          <div class="max-w-6xl mx-auto px-6 w-full pb-6">
            <div class="flex flex-col md:flex-row items-start md:items-end space-y-4 md:space-y-0 md:space-x-6">
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
                    <p class="text-base sm:text-lg lg:text-xl text-gray-200 mb-4 max-w-2xl"><?= htmlspecialchars($user['bio'] ?: 'Movie enthusiast exploring cinematic worlds') ?></p>

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

          <!-- Recent Activity Section -->
          <section>
            <div class="flex items-center justify-between mb-8">
              <h2 class="text-3xl font-bold flex items-center">
                <i class="fas fa-clock mr-4 text-blue-400"></i>Recent Activity
              </h2>
              <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-400 bg-gray-800 px-3 py-1 rounded-full"><?= count($activities) ?> activities</span>
                <button class="text-blue-400 hover:text-blue-300 transition-colors text-sm font-medium">
                  View All <i class="fas fa-arrow-right ml-1"></i>
                </button>
              </div>
            </div>
            <?php if (empty($activities)): ?>
              <div class="glass p-12 rounded-xl text-center">
                <i class="fas fa-clock text-8xl text-gray-600 mb-6"></i>
                <h3 class="text-2xl font-semibold text-gray-400 mb-3">No recent activity</h3>
                <p class="text-gray-500 text-lg">Start watching movies to see your activity here!</p>
              </div>
            <?php else: ?>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($activities as $act): ?>
                  <div class="glass p-5 rounded-xl flex items-center space-x-4 hover:bg-opacity-10 transition-all duration-300 group">
                    <?php
                    $iconMap = [
                      'watched_movie' => 'fas fa-play',
                      'added_to_watchlist' => 'fas fa-bookmark',
                      'rated_movie' => 'fas fa-star',
                      'reviewed_movie' => 'fas fa-comment',
                      'achieved_badge' => 'fas fa-trophy'
                    ];
                    $colorMap = [
                      'watched_movie' => 'text-green-400 bg-green-500',
                      'added_to_watchlist' => 'text-yellow-400 bg-yellow-500',
                      'rated_movie' => 'text-blue-400 bg-blue-500',
                      'reviewed_movie' => 'text-purple-400 bg-purple-500',
                      'achieved_badge' => 'text-orange-400 bg-orange-500'
                    ];
                    $bgColor = explode(' ', $colorMap[$act['activity_type']])[1];
                    $textColor = explode(' ', $colorMap[$act['activity_type']])[0];
                    ?>
                    <div class="w-12 h-12 rounded-full <?= $bgColor ?> bg-opacity-20 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform duration-300">
                      <i class="<?= $iconMap[$act['activity_type']] ?> <?= $textColor ?> text-lg"></i>
                    </div>
                    <div class="flex-1">
                      <p class="text-sm">
                        <span class="font-semibold text-white"><?= htmlspecialchars($user['username']) ?></span>
                        <?php switch ($act['activity_type']):
                          case 'watched_movie': ?> watched <span class="text-blue-400 font-medium"><?= htmlspecialchars($act['movie_title']); ?></span><?php break;
                                                                                                                                                      case 'added_to_watchlist': ?> added <span class="text-yellow-400 font-medium"><?= htmlspecialchars($act['movie_title']); ?></span> to watchlist<?php break;
                                                                                                                                                                                                                                                                                                    case 'rated_movie': ?> rated <span class="text-blue-400 font-medium"><?= htmlspecialchars($act['movie_title']); ?></span><?php break;
                                                                                                                                                                                                                                                                                                                                                                                                                            case 'reviewed_movie': ?> reviewed <span class="text-purple-400 font-medium"><?= htmlspecialchars($act['movie_title']); ?></span><?php break;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            case 'achieved_badge': ?> earned the <span class="text-orange-400 font-medium"><?= htmlspecialchars($act['achievement_name']); ?></span> achievement<?php break;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          endswitch; ?>
                      </p>
                      <p class="text-gray-500 text-xs mt-1 flex items-center">
                        <i class="fas fa-clock mr-1"></i>
                        <?= date('M j, Y g:ia', strtotime($act['created_at'])) ?>
                      </p>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </section>
        </div>

        <!-- Achievements Section -->
        <section class="mt-16">
          <div class="flex items-center justify-between mb-8">
            <h2 class="text-3xl font-bold flex items-center">
              <i class="fas fa-trophy mr-4 text-yellow-400"></i>Achievements
            </h2>
            <div class="flex items-center space-x-4">
              <span class="text-sm text-gray-400 bg-gray-800 px-4 py-2 rounded-full"><?= count($achievements) ?> earned</span>
              <button class="text-blue-400 hover:text-blue-300 transition-colors text-sm font-medium">
                View All <i class="fas fa-arrow-right ml-1"></i>
              </button>
            </div>
          </div>
          <?php if (empty($achievements)): ?>
            <div class="glass p-12 rounded-xl text-center">
              <i class="fas fa-trophy text-8xl text-gray-600 mb-6"></i>
              <h3 class="text-2xl font-semibold text-gray-400 mb-3">No achievements yet</h3>
              <p class="text-gray-500 text-lg">Start watching movies and engaging with the platform to unlock achievements!</p>
            </div>
          <?php else: ?>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-6">
              <?php foreach ($achievements as $ach): ?>
                <div class="group flex flex-col items-center p-4 glass rounded-xl hover:scale-105 transition-transform duration-300">
                  <div class="w-16 h-16 mb-3 relative">
                    <img src="assets/achievements/<?= $ach['icon'] ?>"
                      alt="<?= htmlspecialchars($ach['name']) ?>"
                      class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-300">
                    <div class="absolute -top-1 -right-1 w-6 h-6 bg-yellow-400 rounded-full flex items-center justify-center group-hover:animate-pulse">
                      <i class="fas fa-check text-black text-xs"></i>
                    </div>
                  </div>
                  <h3 class="text-sm font-medium text-center text-white mb-1 group-hover:text-yellow-400 transition-colors"><?= htmlspecialchars($ach['name']) ?></h3>
                  <p class="text-xs text-gray-400 text-center"><?= date('M j, Y', strtotime($ach['earned_at'])) ?></p>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </section>

        <?php include './includes/footer.php'; ?>
      </div>
    </div>
  </body>

</html>