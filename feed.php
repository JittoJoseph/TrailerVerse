<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/tmdb_config.php';
require_once __DIR__ . '/services/UserService.php';

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
    .post-card {
      transition: transform .2s ease;
    }

    .post-card:hover {
      transform: translateY(-2px);
    }
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

  <div class="pt-24 max-w-6xl mx-auto px-4 md:px-6 grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Left spacer (hidden on mobile for minimal feel) -->
    <div class="hidden md:block"></div>

    <!-- Center column: posts -->
    <main class="space-y-4">
      <?php if (empty($feed)): ?>
        <div class="glass p-10 rounded-2xl text-center">
          <i class="fas fa-users text-5xl text-gray-600 mb-3"></i>
          <h2 class="text-xl font-semibold text-gray-300 mb-1">Your feed is empty</h2>
          <p class="text-gray-500">Follow people or start watching, rating, and reviewing movies.</p>
        </div>
      <?php else: ?>
        <?php foreach ($feed as $item): ?>
          <article class="glass rounded-2xl p-4 post-card">
            <header class="flex items-center justify-between mb-3">
              <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full overflow-hidden bg-gray-700">
                  <?php if (!empty($item['profile_picture'])): ?>
                    <img src="<?= htmlspecialchars($item['profile_picture']) ?>" class="w-full h-full object-cover" alt="avatar">
                  <?php endif; ?>
                </div>
                <div>
                  <a href="profile.php?user_id=<?= (int)$item['user_id'] ?>" class="font-medium hover:text-gray-300">
                    @<?= htmlspecialchars($item['username']) ?>
                  </a>
                  <div class="text-xs text-gray-400"><?= humanTime($item['created_at']) ?></div>
                </div>
              </div>
            </header>

            <div class="flex items-start gap-4">
              <?php if (!empty($item['poster_path'])): ?>
                <a href="movie.php?id=<?= (int)($item['movie_id'] ?? 0) ?>" class="flex-shrink-0">
                  <img src="<?= getTMDBPosterUrl($item['poster_path']) ?>" alt="poster" class="w-24 h-24 object-cover rounded-lg">
                </a>
              <?php else: ?>
                <div class="w-24 h-24 bg-gray-800 rounded-lg flex items-center justify-center text-gray-500">
                  <i class="fas fa-film"></i>
                </div>
              <?php endif; ?>

              <div class="flex-1 min-w-0">
                <?php
                $verb = [
                  'watched_movie' => 'watched',
                  'added_to_watchlist' => 'added to watchlist',
                  'rated_movie' => 'rated',
                  'reviewed_movie' => 'reviewed',
                  'achieved_badge' => 'earned'
                ][$item['activity_type']] ?? 'did something';
                ?>
                <p class="text-sm">
                  <span class="font-semibold"><?= htmlspecialchars($item['username']) ?></span>
                  <?= $verb ?>
                  <?php if ($item['movie_title'] ?? false): ?>
                    <a href="movie.php?id=<?= (int)($item['movie_id'] ?? 0) ?>" class="hover:underline">
                      <?= htmlspecialchars($item['movie_title']) ?>
                    </a>
                  <?php elseif ($item['achievement_name'] ?? false): ?>
                    <span class="text-yellow-400"><i class="<?= htmlspecialchars($item['achievement_icon']) ?> mr-1"></i><?= htmlspecialchars($item['achievement_name']) ?></span>
                  <?php endif; ?>
                </p>

                <?php if ($item['activity_type'] === 'rated_movie' && isset($item['metadata'])): ?>
                  <?php $meta = json_decode($item['metadata'] ?? 'null', true);
                  $rating = $meta['rating'] ?? null; ?>
                  <?php if ($rating): ?>
                    <div class="mt-2 text-yellow-300 text-sm"><i class="fas fa-star mr-1"></i>Rated <?= htmlspecialchars($rating) ?>/10</div>
                  <?php endif; ?>
                <?php endif; ?>
              </div>
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

    <!-- Right column: suggestions -->
    <aside class="space-y-4">
      <div class="glass rounded-2xl p-4">
        <h3 class="font-semibold mb-3">Suggested for you</h3>
        <div class="space-y-3">
          <?php foreach ($suggested as $s): ?>
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-full overflow-hidden bg-gray-700">
                  <?php if (!empty($s['profile_picture'])): ?>
                    <img src="<?= htmlspecialchars($s['profile_picture']) ?>" class="w-full h-full object-cover" alt="avatar">
                  <?php endif; ?>
                </div>
                <a href="profile.php?user_id=<?= (int)$s['id'] ?>" class="truncate hover:text-gray-300">@<?= htmlspecialchars($s['username']) ?></a>
              </div>
              <?php $following = !empty($s['is_following']); ?>
              <button
                class="px-3 py-1 rounded-lg text-sm <?= $following ? 'glass' : 'bg-white text-slate-950' ?>"
                onclick="toggleFollow(<?= (int)$s['id'] ?>, this)"
                data-following="<?= $following ? '1' : '0' ?>">
                <?= $following ? 'Following' : 'Follow' ?>
              </button>
            </div>
          <?php endforeach; ?>

          <?php if (empty($suggested)): ?>
            <p class="text-sm text-gray-500">You’re all caught up.</p>
          <?php endif; ?>
        </div>
      </div>
    </aside>
  </div>

  <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>