<?php
require_once 'config/app.php';
require_once 'config/tmdb_config.php';
require_once 'services/MovieService.php';
require_once 'services/MovieStatusService.php';
require_once 'services/MovieReviewService.php';
require_once 'config/database.php';

$dbConn = (new Database())->connect();
// Delegate AJAX POST actions to the services
MovieStatusService::handleAjax($dbConn);
MovieReviewService::handleAjax($dbConn);

// Instantiate status service for rendering the page
$movieStatusService = new MovieStatusService($dbConn);
// Fetch movie and user-specific status/rating via service
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
  header('Location: index.php');
  exit;
}
$movieService = new MovieService();
$movie = $movieService->getMovieDetails($id);
// Fetch similar movies
$similar = $movieService->getSimilarMovies($id);
$reviewService = new MovieReviewService($dbConn);
$reviews = $reviewService->getReviews($id);

$inWatchlist = false;
$watched     = false;
$userRating  = 0;
if (isset($_SESSION['user_id'])) {
  $uid = (int)$_SESSION['user_id'];
  $statusArr = $movieStatusService->getStatus($uid, $id);
  $inWatchlist = $statusArr['inWatchlist'];
  $watched     = $statusArr['watched'];
  $userRating  = $statusArr['rating'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title><?= htmlspecialchars($movie['title'] ?? 'Movie Details') ?> - TrailerVerse</title>
  <?php include 'includes/head.php'; ?>
</head>

<body class="bg-black text-white overflow-x-hidden overflow-y-auto">
  <?php include 'includes/header_detail.php'; ?>

  <!-- Fullscreen Backdrop Section -->
  <section class="relative h-screen w-full">
    <img src="<?= getTMDBBackdropUrl($movie['backdrop_path'] ?? '') ?>" alt="" class="absolute top-0 left-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black"></div>
    <div class="absolute inset-0 flex items-center justify-center z-20">
      <button class="flex items-center justify-center w-20 h-20 border-2 border-white/70 rounded-full hover:border-white hover:bg-white/10 transition">
        <i class="fas fa-play text-2xl text-white ml-0.5"></i>
      </button>
    </div>
    <div class="absolute bottom-0 left-0 p-8 max-w-2xl space-y-4 z-20">
      <h1 class="text-6xl font-bold gradient-text"><?= htmlspecialchars($movie['title'] ?? '') ?></h1>
      <p class="text-gray-300 max-w-lg leading-relaxed"><?= htmlspecialchars($movie['overview'] ?? '') ?></p>
      <div class="mt-4 flex space-x-4">
        <?php if (isset($_SESSION['user_id'])): ?>
          <button id="btn-watchlist" class="flex items-center px-4 py-2 <?php echo $inWatchlist ? 'bg-red-600' : 'bg-white text-black'; ?> rounded-md hover:opacity-90 transition">
            <i class="fas fa-list mr-2"></i><?= $inWatchlist ? 'Remove from List' : 'Add to Watchlist' ?>
          </button>
          <button id="btn-watched" class="flex items-center px-4 py-2 <?php echo $watched ? 'bg-green-600' : 'glass'; ?> rounded-md hover:opacity-90 transition">
            <i class="fas fa-check mr-2"></i><?= $watched ? 'Unmark Watched' : 'Mark as Watched' ?>
          </button>
        <?php else: ?>
          <a href="auth/signin.php" class="px-4 py-2 bg-blue-500 rounded-md hover:opacity-90 transition">Sign in to manage list</a>
        <?php endif; ?>
      </div>
    </div>
    <div class="absolute bottom-0 left-0 w-full h-48 bg-gradient-to-t from-black to-transparent"></div>
  </section>

  <!-- Details Tabs -->
  <div class="max-w-7xl mx-auto px-6 py-12">
    <ul id="tabs" class="flex space-x-8 border-b border-gray-700">
      <li><button data-tab="details" class="pb-2 text-lg text-white border-b-2 border-transparent hover:border-white">Details</button></li>
      <li><button data-tab="similar" class="pb-2 text-lg text-gray-400 hover:text-white hover:border-white">Similar Movies</button></li>
      <li><button data-tab="reviews" class="pb-2 text-lg text-gray-400 hover:text-white hover:border-white">Reviews</button></li>
    </ul>
    <div id="details" class="tab-content pt-8">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="space-y-4">
          <div><span class="text-gray-400">Release Date:</span> <?= htmlspecialchars($movie['release_date'] ?? 'N/A') ?></div>
          <div><span class="text-gray-400">Runtime:</span> <?= ($movie['runtime'] ?? 0) ?> min</div>
          <div><span class="text-gray-400">Rating:</span> <?= number_format($movie['vote_average'], 1) ?> / 10</div>
          <div><span class="text-gray-400">Genres:</span> <?= implode(', ', array_map(fn($g) => $g['name'], $movie['genres'] ?? [])) ?></div>
        </div>
        <div>
          <h2 class="text-2xl font-semibold mb-4">Overview</h2>
          <p class="text-gray-300 leading-relaxed"><?= nl2br(htmlspecialchars($movie['overview'] ?? '')) ?></p>
        </div>
      </div> <!-- end details grid -->
      <!-- User rating section -->
      <div id="user-rating" class="mt-6 flex items-center space-x-2">
        <span class="text-white font-medium">Rate this movie:</span>
        <?php if (isset($_SESSION['user_id'])): ?>
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <i class="rating-star <?php echo ($i <= $userRating ? 'fas' : 'far'); ?> fa-star cursor-pointer text-yellow-400 text-2xl hover:text-yellow-300" data-value="<?= $i ?>"></i>
          <?php endfor; ?>
        <?php else: ?>
          <a href="auth/signin.php" class="text-blue-400 hover:underline">Sign in to rate</a>
        <?php endif; ?>
      </div>
    </div>
    <div id="similar" class="tab-content hidden pt-8">
      <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4">
        <?php foreach (array_slice($similar['results'] ?? [], 0, 12) as $s): ?>
          <a href="movie.php?id=<?= $s['id'] ?>" class="block">
            <img src="<?= getTMDBPosterUrl($s['poster_path']) ?>" alt="" class="w-full h-48 object-cover rounded-lg">
            <h3 class="mt-2 text-sm font-medium hover:text-gray-200"><?= htmlspecialchars($s['title']) ?></h3>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
    <div id="reviews" class="tab-content hidden pt-8">
      <div class="bg-gray-800 p-6 rounded-lg space-y-4">
        <?php if (isset($_SESSION['user_id'])): ?>
          <textarea id="review-textarea" rows="4" class="w-full p-4 bg-gray-700 text-white rounded-lg border border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Share your thoughts..."></textarea>
          <button id="btn-submit-review" class="px-6 py-2 bg-blue-500 hover:bg-blue-400 text-white font-semibold rounded-lg transition">Post Review</button>
        <?php else: ?>
          <a href="auth/signin.php" class="inline-block text-blue-400 hover:underline">Sign in to write a review</a>
        <?php endif; ?>
      </div>
      <div id="reviews-list" class="mt-6 space-y-6">
        <?php foreach ($reviews as $r): ?>
          <div class="bg-gray-900 p-6 rounded-lg border border-gray-700 shadow-inner">
            <div class="flex items-center justify-between text-sm text-gray-400 mb-2">
              <span><?= htmlspecialchars($r['username']) ?></span>
              <span><?= date('M j, Y H:i', strtotime($r['created_at'])) ?></span>
            </div>
            <p class="text-gray-100 leading-relaxed whitespace-pre-wrap"><?= htmlspecialchars($r['review_text']) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <?php include 'includes/footer.php'; ?>

  <script>
    // Tab switching
    document.querySelectorAll('[data-tab]').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
        document.getElementById(btn.getAttribute('data-tab')).classList.remove('hidden');
        document.querySelectorAll('[data-tab]').forEach(b => b.classList.remove('text-white', 'border-white'));
        btn.classList.add('text-white', 'border-white');
      });
    });
    // Rating click handler (event delegation)
    document.getElementById('user-rating')?.addEventListener('click', function(e) {
      const star = e.target.closest('.rating-star');
      if (!star) return;
      const val = star.getAttribute('data-value');
      fetch('', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=rate&movie_id=<?= $id ?>&rating=${val}`
      }).then(res => res.json()).then(data => {
        if (data.success) {
          // Update star display
          this.querySelectorAll('.rating-star').forEach(s => {
            const v = s.getAttribute('data-value');
            s.classList.toggle('fas', v <= val);
            s.classList.toggle('far', v > val);
          });
          // Update watched button state
          const watchedBtn = document.getElementById('btn-watched');
          if (watchedBtn) {
            if (data.watched) {
              watchedBtn.classList.remove('glass');
              watchedBtn.classList.add('bg-green-600');
              watchedBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Unmark Watched';
            } else {
              watchedBtn.classList.remove('bg-green-600');
              watchedBtn.classList.add('glass');
              watchedBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Mark as Watched';
            }
          }
          // Update watchlist button state
          const watchlistBtn = document.getElementById('btn-watchlist');
          if (watchlistBtn) {
            if (data.inWatchlist) {
              watchlistBtn.classList.remove('bg-white', 'text-black');
              watchlistBtn.classList.add('bg-red-600');
              watchlistBtn.innerHTML = '<i class="fas fa-list mr-2"></i>Remove from List';
            } else {
              watchlistBtn.classList.remove('bg-red-600');
              watchlistBtn.classList.add('bg-white', 'text-black');
              watchlistBtn.innerHTML = '<i class="fas fa-list mr-2"></i>Add to Watchlist';
            }
          }
        } else if (data.error) {
          alert(data.error);
        }
      }).catch(() => alert('Failed to save rating'));
    });
    // Watchlist toggle
    document.getElementById('btn-watchlist')?.addEventListener('click', function() {
      fetch('', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=watchlist&movie_id=<?= $id ?>`
      }).then(r => r.json()).then(d => {
        if (d.success) location.reload();
      });
    });
    // Watched toggle
    document.getElementById('btn-watched')?.addEventListener('click', function() {
      fetch('', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=watched&movie_id=<?= $id ?>`
      }).then(r => r.json()).then(d => {
        if (d.success) location.reload();
      });
    });
    // Review submission
    document.getElementById('btn-submit-review')?.addEventListener('click', function() {
      const text = document.getElementById('review-textarea').value;
      console.log('Submitting review:', text);
      fetch(window.location.href, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=review&movie_id=<?= $id ?>&review_text=${encodeURIComponent(text)}`
      }).then(res => res.json()).then(data => {
        if (data.success) {
          const list = document.getElementById('reviews-list');
          list.innerHTML = '';
          data.reviews.forEach(r => {
            const div = document.createElement('div');
            div.className = 'p-4 bg-gray-900 rounded';
            div.innerHTML = `<div class="text-sm text-gray-400">${r.username} • ${new Date(r.created_at).toLocaleString()}</div><p class="mt-2 text-white">${r.review_text.replace(/\n/g, '<br>')}</p>`;
            list.appendChild(div);
          });
          document.getElementById('review-textarea').value = '';
        } else {
          alert(data.error || 'Failed to submit review');
        }
      }).catch(() => alert('Failed to submit review'));
    });
  </script>
</body>

</html>