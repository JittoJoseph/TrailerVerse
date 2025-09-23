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
$userId = getCurrentUserId();

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

$feed = $userService->getFeed($userId, $limit, $offset);
$suggested = $userService->getSuggestedUsers($userId, 6);

function humanTime($timestamp)
{
  $time = strtotime($timestamp);
  $diff = time() - $time;
  if ($diff < 60) return $diff . 's';
  if ($diff < 3600) return floor($diff / 60) . 'm';
  if ($diff < 86400) return floor($diff / 3600) . 'h';
  return date('M d', $time);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title>Feed - TrailerVerse</title>
  <?php include __DIR__ . '/includes/head.php'; ?>
  <style>
    .post-card { transition: transform .2s ease; }
    .post-card:hover { transform: translateY(-2px); }
    .poster-img { width:100%; aspect-ratio:3/4; object-fit:cover; }
  </style>
  <script>
    // Lightweight follow/unfollow without reload
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
        btn.textContent = following ? 'Follow' : 'Following';
        btn.className = following ?
          'px-3 py-1 rounded-lg bg-white text-slate-950 text-sm' :
          'px-3 py-1 rounded-lg glass text-sm';
      }
      btn.disabled = false;
    }
  </script>
</head>

<body class="bg-slate-950 text-white min-h-screen">
  <?php include __DIR__ . '/includes/header.php'; ?>

  <div class="pt-24 max-w-6xl mx-auto px-4 md:px-6 grid grid-cols-1 md:grid-cols-4 gap-6">
  <!-- Left sidebar: trending movies & genres -->
  <div class="hidden md:block md:col-span-1 space-y-6">
      <div class="glass rounded-2xl p-4">
        <h2 class="font-semibold mb-3">Trending Now</h2>
        <?php $movieService = new MovieService();
              $trending = $movieService->getTrendingMovies()['results'] ?? [];
              $trending = array_slice($trending,0,5); ?>
        <div class="space-y-3">
          <?php foreach($trending as $m): ?>
            <a href="movie.php?id=<?= (int)$m['id'] ?>" class="flex items-center gap-3 hover:bg-gray-800 p-2 rounded-md">
              <img src="<?= getTMDBPosterUrl($m['poster_path']) ?>" alt="" class="w-10 h-14 object-cover rounded-md">
              <span class="truncate"><?= htmlspecialchars($m['title']) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="glass rounded-2xl p-4">
        <h3 class="font-semibold mb-3">Trending Genres</h3>
        <div class="flex flex-wrap gap-2">
          <?php foreach(array_slice($genres,0,5) as $g): ?>
            <a href="genres.php?genre=<?= (int)$g['id'] ?>" class="px-3 py-1 bg-white bg-opacity-20 text-sm rounded-full hover:bg-opacity-40">
              <?= htmlspecialchars($g['name']) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

  <!-- Center column: posts -->
  <main class="space-y-6 md:col-span-2">
      <!-- Stories bar -->
  <div class="flex space-x-4 overflow-x-auto pb-4">
        <?php foreach ($suggested as $s): ?>
          <?php $initial = strtoupper(substr($s['username'],0,1)); ?>
          <div class="flex-shrink-0 text-center">
            <div class="w-14 h-14 rounded-full p-0.5 bg-gradient-to-br from-blue-500 to-purple-600">
              <div class="w-full h-full rounded-full bg-slate-950 flex items-center justify-center">
                <span class="text-white font-semibold"><?= $initial ?></span>
              </div>
            </div>
            <p class="text-xs text-gray-300 mt-1"><?= htmlspecialchars($s['username']) ?></p>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if (empty($feed)): ?>
        <div class="glass p-10 rounded-2xl text-center">
          <i class="fas fa-users text-5xl text-gray-600 mb-3"></i>
          <h2 class="text-xl font-semibold text-gray-300 mb-1">Your feed is empty</h2>
          <p class="text-gray-500">Follow people or start watching, rating, and reviewing movies.</p>
        </div>
      <?php else: ?>
        <?php foreach ($feed as $item): ?>
          <article class="glass rounded-2xl overflow-hidden post-card">
            <!-- Header: avatar, username, timestamp -->
            <div class="flex items-center p-3 space-x-3">
              <div class="w-8 h-8 rounded-full overflow-hidden bg-gray-700">
                <?php if (!empty($item['profile_picture'])): ?>
                  <img src="<?= htmlspecialchars($item['profile_picture']) ?>" class="w-full h-full object-cover" alt="avatar">
                <?php else: ?>
                  <?php $initial = strtoupper(substr($item['username'], 0, 1)); ?>
                  <div class="w-full h-full rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                    <span class="text-white font-semibold"><?= $initial ?></span>
                  </div>
                <?php endif; ?>
              </div>
              <div>
                <a href="profile.php?user_id=<?= (int)$item['user_id'] ?>" class="font-semibold text-white hover:text-gray-300">
                  @<?= htmlspecialchars($item['username']) ?>
                </a>
                <div class="text-xs text-gray-400"><?= humanTime($item['created_at']) ?></div>
              </div>
            </div>

            <!-- Poster image -->
            <?php if (!empty($item['poster_path'])): ?>
              <img src="<?= getTMDBPosterUrl($item['poster_path']) ?>" alt="poster" class="poster-img">
            <?php else: ?>
              <div class="w-full poster-img bg-gray-800 flex items-center justify-center text-gray-500">
                <i class="fas fa-film text-4xl"></i>
              </div>
            <?php endif; ?>

            <!-- Caption below image -->
            <?php
              $verb = $item['activity_type'] === 'rated_movie' ? 'Rated' : 'Watched';
              $ratingTxt = ($item['activity_type'] === 'rated_movie' && $item['rating']) ? " {$item['rating']}/10" : '';
              $title = htmlspecialchars($item['movie_title'] ?? '');
            ?>
            <div class="p-3 text-white text-sm">
              <?= htmlspecialchars($item['username']) ?> <?= $verb ?> <?= $title ?><?= $ratingTxt ?>
            </div>
          </article>
        <?php endforeach; ?>

        <div class="flex items-center justify-center gap-3 pt-2">
          <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>" class="px-3 py-1 glass rounded">Prev</a>
          <?php endif; ?>
          <?php if (count($feed) === $limit): ?>
            <a href="?page=<?= $page + 1 ?>" class="px-3 py-1 glass rounded">Next</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </main>

    <!-- Right sidebar: suggested users -->
    <aside class="hidden md:block md:col-span-1 space-y-6">
      <div class="glass rounded-2xl p-4 h-64 overflow-y-auto">
        <h3 class="font-semibold mb-3">Suggested for you</h3>
        <div class="space-y-3">
          <?php foreach ($suggested as $s): ?>
            <?php $initial = strtoupper(substr($s['username'], 0, 1)); ?>
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-full flex items-center justify-center bg-gradient-to-br from-blue-500 to-purple-600">
                  <span class="text-white font-semibold"><?= $initial ?></span>
                </div>
                <a href="profile.php?user_id=<?= (int)$s['id'] ?>" class="truncate hover:text-gray-300 text-white">@<?= htmlspecialchars($s['username']) ?></a>
              </div>
              <?php $following = !empty($s['is_following']); ?>
              <button class="px-3 py-1 rounded-lg text-sm <?= $following ? 'glass' : 'bg-white text-slate-950' ?>" onclick="toggleFollow(<?= (int)$s['id'] ?>, this)" data-following="<?= $following ? '1' : '0' ?>">
                <?= $following ? 'Following' : 'Follow' ?>
              </button>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </aside>
  </div>

  <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>