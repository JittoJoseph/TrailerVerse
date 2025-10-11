<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/tmdb_config.php';
require_once __DIR__ . '/services/UserService.php';
require_once __DIR__ . '/services/MovieService.php';
require_once __DIR__ . '/services/GenreService.php';

if (!isLoggedIn()) {
  header('Location: auth/signin.php');
  exit;
}

$userService = new UserService();
$movieService = new MovieService();
$genreService = new GenreService();
$userId = getCurrentUserId();

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

$feed = $userService->getFeed($userId, $limit, $offset);
$suggested = $userService->getSuggestedUsers($userId, 8);
$trending = $movieService->getTrendingMovies()['results'] ?? [];
$genres = $genreService->getGenres();

function humanTime($timestamp)
{
  $time = strtotime($timestamp);
  $diff = time() - $time;
  if ($diff < 60) return $diff . 's ago';
  if ($diff < 3600) return floor($diff / 60) . 'm ago';
  if ($diff < 86400) return floor($diff / 3600) . 'h ago';
  if ($diff < 604800) return floor($diff / 86400) . 'd ago';
  return date('M d', $time);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title>Feed - TrailerVerse</title>
  <?php include __DIR__ . '/includes/head.php'; ?>
  <style>
    .glass {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .post-card {
      transition: all 0.3s ease;
    }

    .post-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    }

    .square-post {
      width: 100%;
      aspect-ratio: 1/1;
      object-fit: cover;
    }

    .scrollbar-hide::-webkit-scrollbar {
      display: none;
    }

    .scrollbar-hide {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    .line-clamp-2 {
      display: -webkit-box;
      -webkit-line-clamp: 2;
      line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
  </style>
  <script>
    async function toggleFollow(userId, btn) {
      btn.disabled = true;
      const following = btn.dataset.following === '1';
      const res = await fetch('social.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
          action: following ? 'unfollow' : 'follow',
          user_id: userId
        })
      });
      const data = await res.json();
      if (data.success) {
        btn.dataset.following = following ? '0' : '1';
        btn.textContent = following ? 'Following' : 'Follow';
        btn.className = following ?
          'px-3 py-1 rounded-lg bg-gradient-to-r from-blue-500 to-purple-600 text-white text-xs font-medium hover:from-blue-600 hover:to-purple-700 transition' :
          'px-3 py-1 rounded-lg glass text-xs font-medium hover:bg-white/10 transition';
      }
      btn.disabled = false;
    }
  </script>
</head>

<body class="bg-slate-950 text-white min-h-screen">
  <?php include __DIR__ . '/includes/header.php'; ?>

  <div class="pt-8 lg:pt-24 pb-8">
    <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 lg:gap-8">

        <!-- LEFT SIDEBAR: Profile Quick Access + Trending -->
        <aside class="hidden lg:block lg:col-span-3 space-y-6">
          <!-- Current User Card -->
          <?php
          $currentUser = $userService->getUserById($userId);
          $currentUserStats = $userService->getUserStats($userId);
          $currentUserInitial = strtoupper(substr($currentUser['username'], 0, 1));
          ?>
          <div class="glass rounded-2xl p-4">
            <div class="flex items-center space-x-3 mb-4">
              <div class="w-14 h-14 rounded-full flex items-center justify-center bg-gradient-to-br from-blue-500 to-purple-600">
                <span class="text-white font-bold text-xl"><?= $currentUserInitial ?></span>
              </div>
              <div class="flex-1 min-w-0">
                <a href="profile.php" class="font-semibold text-white hover:text-gray-300 block truncate">
                  @<?= htmlspecialchars($currentUser['username']) ?>
                </a>
                <p class="text-xs text-gray-400 truncate"><?= htmlspecialchars($currentUser['first_name'] ?? '') ?> <?= htmlspecialchars($currentUser['last_name'] ?? '') ?></p>
              </div>
            </div>
            <div class="grid grid-cols-3 gap-2 text-center text-xs">
              <div>
                <div class="font-bold text-blue-400"><?= $currentUserStats['movies_watched'] ?></div>
                <div class="text-gray-400">Watched</div>
              </div>
              <div>
                <div class="font-bold text-purple-400"><?= $userService->getFollowersCount($userId) ?></div>
                <div class="text-gray-400">Followers</div>
              </div>
              <div>
                <div class="font-bold text-pink-400"><?= $userService->getFollowingCount($userId) ?></div>
                <div class="text-gray-400">Following</div>
              </div>
            </div>
          </div>

          <!-- Trending Movies -->
          <div class="glass rounded-2xl p-4">
            <h3 class="font-semibold mb-4 flex items-center">
              <i class="fas fa-fire text-orange-500 mr-2"></i>
              Trending Now
            </h3>
            <div class="space-y-3">
              <?php foreach (array_slice($trending, 0, 5) as $movie): ?>
                <a href="movie.php?id=<?= (int)$movie['id'] ?>" class="flex items-center gap-3 p-2 rounded-lg hover:bg-white/5 transition group">
                  <img src="<?= getTMDBPosterUrl($movie['poster_path']) ?>" alt="" class="w-12 h-16 object-cover rounded">
                  <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-medium truncate group-hover:text-gray-300"><?= htmlspecialchars($movie['title']) ?></h4>
                    <div class="flex items-center text-xs text-gray-400 mt-1">
                      <i class="fas fa-star text-yellow-400 mr-1"></i>
                      <?= number_format($movie['vote_average'], 1) ?>
                    </div>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Browse Genres -->
          <div class="glass rounded-2xl p-4">
            <h3 class="font-semibold mb-3 flex items-center">
              <i class="fas fa-th-large text-green-500 mr-2"></i>
              Browse Genres
            </h3>
            <div class="flex flex-wrap gap-2">
              <?php foreach (array_slice($genres, 0, 8) as $genre): ?>
                <a href="genre-movies.php?id=<?= (int)$genre['id'] ?>" class="px-3 py-1 bg-white/10 hover:bg-white/20 rounded-full text-xs transition">
                  <?= htmlspecialchars($genre['name']) ?>
                </a>
              <?php endforeach; ?>
            </div>
            <a href="genres.php" class="block text-center text-xs text-gray-400 hover:text-white mt-3 transition">
              View All <i class="fas fa-arrow-right ml-1"></i>
            </a>
          </div>
        </aside>

        <!-- CENTER: Main Feed (Instagram Explore Style Grid) -->
        <main class="lg:col-span-6">
          <!-- Stories Bar -->
          <div class="glass rounded-2xl p-4 mb-6">
            <div class="flex space-x-4 overflow-x-auto scrollbar-hide">
              <?php foreach ($suggested as $suggestedUser): ?>
                <?php $initial = strtoupper(substr($suggestedUser['username'], 0, 1)); ?>
                <a href="profile.php?user_id=<?= (int)$suggestedUser['id'] ?>" class="flex-shrink-0 text-center group">
                  <div class="w-16 h-16 rounded-full p-0.5 bg-gradient-to-br from-pink-500 via-purple-500 to-blue-500 mb-2">
                    <div class="w-full h-full rounded-full bg-slate-950 flex items-center justify-center">
                      <span class="text-white font-semibold"><?= $initial ?></span>
                    </div>
                  </div>
                  <p class="text-xs text-gray-300 truncate w-16 group-hover:text-white"><?= htmlspecialchars($suggestedUser['username']) ?></p>
                </a>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Feed Posts Grid -->
          <?php if (empty($feed)): ?>
            <div class="glass p-8 sm:p-12 rounded-2xl text-center">
              <i class="fas fa-film text-4xl sm:text-6xl text-gray-600 mb-4"></i>
              <h2 class="text-xl sm:text-2xl font-semibold text-gray-300 mb-2">Your feed is empty</h2>
              <p class="text-sm sm:text-base text-gray-500 mb-6">Follow people or start watching and rating movies to see activity here.</p>
              <a href="index.php" class="inline-block px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg hover:from-blue-600 hover:to-purple-700 transition font-medium">
                Explore Movies
              </a>
            </div>
          <?php else: ?>
            <!-- Responsive Grid: 2 cols mobile, 3 cols tablet, 3 cols desktop -->
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-1">
              <?php foreach ($feed as $item): ?>
                <?php
                $userInitial = strtoupper(substr($item['username'], 0, 1));
                $isRated = $item['activity_type'] === 'rated_movie';
                $verb = $isRated ? 'rated' : 'watched';
                $rating = $isRated && $item['rating'] ? number_format($item['rating'], 1) : null;
                ?>
                <div class="relative group overflow-hidden bg-gray-900 aspect-square">
                  <!-- Movie Poster - Main clickable area -->
                  <a href="movie.php?id=<?= (int)$item['movie_id'] ?>" class="block w-full h-full">
                    <?php if (!empty($item['poster_path'])): ?>
                      <img src="<?= getTMDBPosterUrl($item['poster_path']) ?>"
                        alt="<?= htmlspecialchars($item['movie_title']) ?>"
                        class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                    <?php else: ?>
                      <div class="w-full h-full bg-gradient-to-br from-gray-800 to-gray-900 flex items-center justify-center">
                        <i class="fas fa-film text-5xl text-gray-600"></i>
                      </div>
                    <?php endif; ?>
                  </a>

                  <!-- Hover Overlay with Info (Desktop only) -->
                  <div class="hidden lg:flex absolute inset-0 bg-gradient-to-t from-black via-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex-col justify-end p-4 pointer-events-none">
                    <!-- User Info -->
                    <div class="flex items-center space-x-2 mb-2 pointer-events-auto">
                      <a href="profile.php?user_id=<?= (int)$item['user_id'] ?>" class="w-8 h-8 rounded-full flex items-center justify-center bg-gradient-to-br from-blue-500 to-purple-600 flex-shrink-0">
                        <span class="text-white font-semibold text-xs"><?= $userInitial ?></span>
                      </a>
                      <a href="profile.php?user_id=<?= (int)$item['user_id'] ?>" class="font-semibold text-sm hover:text-gray-300 truncate">
                        @<?= htmlspecialchars($item['username']) ?>
                      </a>
                    </div>

                    <!-- Movie Title -->
                    <div class="text-sm mb-2">
                      <span class="font-medium line-clamp-2">
                        <?= htmlspecialchars($item['movie_title']) ?>
                      </span>
                    </div>

                    <!-- Rating & Timestamp -->
                    <div class="flex items-center justify-between">
                      <span class="text-gray-400 text-xs"><?= humanTime($item['created_at']) ?></span>
                      <?php if ($rating): ?>
                        <div class="flex items-center space-x-1 px-2 py-0.5 bg-yellow-500/30 rounded-full border border-yellow-500/50">
                          <i class="fas fa-star text-yellow-400 text-xs"></i>
                          <span class="text-xs font-bold text-yellow-400"><?= $rating ?></span>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>

                  <!-- Mobile: Info overlay (non-clickable, just displays info) -->
                  <div class="lg:hidden absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-2 pointer-events-none">
                    <div class="flex items-center justify-between text-xs">
                      <span class="text-white font-medium truncate">@<?= htmlspecialchars($item['username']) ?></span>
                      <?php if ($rating): ?>
                        <div class="flex items-center space-x-1 px-2 py-0.5 bg-yellow-500/30 rounded-full border border-yellow-500/50">
                          <i class="fas fa-star text-yellow-400 text-xs"></i>
                          <span class="text-xs font-bold text-yellow-400"><?= $rating ?></span>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-center gap-3 pt-6">
              <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="px-4 py-2 glass rounded-lg hover:bg-white/10 transition">
                  <i class="fas fa-chevron-left mr-2"></i>Previous
                </a>
              <?php endif; ?>
              <span class="px-4 py-2 glass rounded-lg">Page <?= $page ?></span>
              <?php if (count($feed) === $limit): ?>
                <a href="?page=<?= $page + 1 ?>" class="px-4 py-2 glass rounded-lg hover:bg-white/10 transition">
                  Next<i class="fas fa-chevron-right ml-2"></i>
                </a>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </main>

        <!-- RIGHT SIDEBAR: Suggestions + Footer Links -->
        <aside class="hidden lg:block lg:col-span-3 space-y-6">
          <!-- Suggestions Card -->
          <div class="glass rounded-2xl p-4">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-semibold text-gray-400">Suggestions For You</h3>
              <a href="#" class="text-xs text-blue-400 hover:text-blue-300">See All</a>
            </div>
            <div class="space-y-3 max-h-96 overflow-y-auto scrollbar-hide">
              <?php foreach ($suggested as $suggestedUser): ?>
                <?php
                $initial = strtoupper(substr($suggestedUser['username'], 0, 1));
                $isFollowing = !empty($suggestedUser['is_following']);
                ?>
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-3 flex-1 min-w-0">
                    <a href="profile.php?user_id=<?= (int)$suggestedUser['id'] ?>" class="w-10 h-10 rounded-full flex items-center justify-center bg-gradient-to-br from-pink-500 to-purple-600 flex-shrink-0">
                      <span class="text-white font-semibold text-sm"><?= $initial ?></span>
                    </a>
                    <div class="flex-1 min-w-0">
                      <a href="profile.php?user_id=<?= (int)$suggestedUser['id'] ?>" class="block font-medium text-sm hover:text-gray-300 truncate">
                        @<?= htmlspecialchars($suggestedUser['username']) ?>
                      </a>
                      <p class="text-xs text-gray-500">Suggested for you</p>
                    </div>
                  </div>
                  <button
                    class="<?= $isFollowing ? 'glass' : 'bg-gradient-to-r from-blue-500 to-purple-600 text-white' ?> px-3 py-1 rounded-lg text-xs font-medium hover:opacity-80 transition flex-shrink-0"
                    onclick="toggleFollow(<?= (int)$suggestedUser['id'] ?>, this)"
                    data-following="<?= $isFollowing ? '1' : '0' ?>">
                    <?= $isFollowing ? 'Following' : 'Follow' ?>
                  </button>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Quick Stats -->
          <div class="glass rounded-2xl p-4">
            <h3 class="font-semibold mb-4 flex items-center">
              <i class="fas fa-chart-line text-blue-500 mr-2"></i>
              Community Stats
            </h3>
            <div class="space-y-3 text-sm">
              <div class="flex justify-between items-center">
                <span class="text-gray-400">Active Users</span>
                <span class="font-semibold text-blue-400">12.5K</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-400">Movies Tracked</span>
                <span class="font-semibold text-purple-400">847K</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-400">Reviews Written</span>
                <span class="font-semibold text-pink-400">234K</span>
              </div>
            </div>
          </div>

          <!-- Footer Links -->
          <div class="text-xs text-gray-500 space-y-2">
            <div class="flex flex-wrap gap-x-2 gap-y-1">
              <a href="#" class="hover:text-gray-300">About</a>
              <span>·</span>
              <a href="#" class="hover:text-gray-300">Help</a>
              <span>·</span>
              <a href="#" class="hover:text-gray-300">Privacy</a>
              <span>·</span>
              <a href="#" class="hover:text-gray-300">Terms</a>
            </div>
            <p class="text-gray-600">© 2025 TrailerVerse</p>
          </div>
        </aside>

      </div>
    </div>
  </div>

  <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>