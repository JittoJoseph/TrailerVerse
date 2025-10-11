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
$reviewService = new MovieReviewService($dbConn);
$reviews = $reviewService->getReviews($id);
$similarMovies = $movieService->getSimilarMovies($id);
$similarMoviesResults = $similarMovies['results'] ?? [];

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

  <!-- Backdrop/Trailer Section -->
  <section id="hero-section" class="relative h-screen w-full">
    <!-- Backdrop Image -->
    <div id="backdrop-container" class="absolute inset-0 transition-opacity duration-500">
      <img src="<?= getTMDBBackdropUrl($movie['backdrop_path'] ?? '') ?>" alt="" class="absolute top-0 left-0 w-full h-full object-cover">
      <div class="absolute inset-0 bg-black bg-opacity-50"></div>
      <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black"></div>
      
      <!-- Play Button (only show if trailer exists) -->
      <?php if (!empty($movie['trailer_key'])): ?>
      <div class="absolute inset-0 flex items-center justify-center z-20">
        <button id="play-trailer-btn" class="flex items-center justify-center w-20 h-20 border-2 border-white/70 rounded-full hover:border-white hover:bg-white/10 transition hover:scale-110">
          <i class="fas fa-play text-2xl text-white ml-0.5"></i>
        </button>
      </div>
      <?php endif; ?>
      
      <!-- Movie Info Overlay -->
      <div id="movie-info-overlay" class="absolute bottom-0 left-0 p-8 max-w-2xl space-y-4 z-20 transition-opacity duration-500">
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
    </div>

    <!-- YouTube Trailer Player (Hidden Initially) -->
    <div id="trailer-container" class="absolute inset-0 opacity-0 pointer-events-none transition-opacity duration-500 flex items-center justify-center bg-black">
      <div class="relative w-full max-w-7xl mx-auto" style="aspect-ratio: 16/9;">
        <iframe id="trailer-iframe" 
                class="w-full h-full rounded-lg" 
                frameborder="0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                allowfullscreen>
        </iframe>
        <!-- Close Trailer Button -->
        <button id="close-trailer-btn" class="absolute -top-12 right-0 z-30 w-12 h-12 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center transition">
          <i class="fas fa-times text-white text-xl"></i>
        </button>
      </div>
    </div>
  </section>

  <!-- Movie Info Section (Shown when trailer is playing) -->
  <div id="movie-info-section" class="hidden max-w-7xl mx-auto px-6 py-8">
    <div class="glass rounded-2xl p-6">
      <h2 class="text-4xl font-bold mb-4"><?= htmlspecialchars($movie['title'] ?? '') ?></h2>
      <p class="text-gray-300 leading-relaxed mb-6"><?= htmlspecialchars($movie['overview'] ?? '') ?></p>
      <div class="flex flex-wrap gap-4">
        <?php if (isset($_SESSION['user_id'])): ?>
          <button onclick="document.getElementById('btn-watchlist').click()" class="flex items-center px-4 py-2 <?php echo $inWatchlist ? 'bg-red-600' : 'bg-white text-black'; ?> rounded-md hover:opacity-90 transition">
            <i class="fas fa-list mr-2"></i><?= $inWatchlist ? 'Remove from List' : 'Add to Watchlist' ?>
          </button>
          <button onclick="document.getElementById('btn-watched').click()" class="flex items-center px-4 py-2 <?php echo $watched ? 'bg-green-600' : 'glass'; ?> rounded-md hover:opacity-90 transition">
            <i class="fas fa-check mr-2"></i><?= $watched ? 'Unmark Watched' : 'Mark as Watched' ?>
          </button>
        <?php else: ?>
          <a href="auth/signin.php" class="px-4 py-2 bg-blue-500 rounded-md hover:opacity-90 transition">Sign in to manage list</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Details Tabs -->
  <div class="max-w-7xl mx-auto px-6 pt-8 lg:pt-24 pb-12">
    <ul id="tabs" class="flex space-x-8 border-b border-gray-700">
      <li><button data-tab="details" class="pb-2 text-lg text-white border-b-2 border-white hover:border-white">Details</button></li>
      <li><button data-tab="reviews" class="pb-2 text-lg text-gray-400 border-b-2 border-transparent hover:text-white hover:border-white">Reviews</button></li>
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
              <span>@<?= htmlspecialchars($r['username']) ?></span>
              <span><?= date('M j, Y H:i', strtotime($r['created_at'])) ?></span>
            </div>
            <p class="text-gray-100 leading-relaxed whitespace-pre-wrap"><?= htmlspecialchars($r['review_text']) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Similar Movies Section -->
  <?php if (!empty($similarMoviesResults)): ?>
  <section class="max-w-7xl mx-auto px-6 py-12">
    <h2 class="text-2xl font-semibold mb-6">More Like This</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
      <?php foreach ($similarMoviesResults as $similar): ?>
        <a href="movie.php?id=<?= htmlspecialchars($similar['id']) ?>" class="group cursor-pointer">
          <div class="relative rounded-lg overflow-hidden mb-3">
            <img src="<?= getTMDBPosterUrl($similar['poster_path'] ?? '') ?>"
              alt="<?= htmlspecialchars($similar['title']) ?>"
              class="w-full h-64 object-cover transition-transform group-hover:scale-105">

            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
              <button class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center glass">
                <i class="fas fa-play text-white"></i>
              </button>
            </div>

            <div class="absolute top-2 right-2 glass px-2 py-1 rounded text-xs">
              <?= number_format($similar['vote_average'] ?? 0, 1) ?>
            </div>
          </div>

          <h3 class="text-sm font-medium group-hover:text-gray-300 transition-colors">
            <?= strlen($similar['title']) > 20 ? substr($similar['title'], 0, 20) . '...' : $similar['title'] ?>
          </h3>
          <p class="text-xs text-gray-500"><?= date('Y', strtotime($similar['release_date'] ?? 'now')) ?></p>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php include 'includes/footer.php'; ?>

  <script>
    // Trailer functionality
    const trailerKey = '<?= $movie['trailer_key'] ?? '' ?>';
    const playBtn = document.getElementById('play-trailer-btn');
    const closeBtn = document.getElementById('close-trailer-btn');
    const backdropContainer = document.getElementById('backdrop-container');
    const trailerContainer = document.getElementById('trailer-container');
    const movieInfoOverlay = document.getElementById('movie-info-overlay');
    const movieInfoSection = document.getElementById('movie-info-section');
    const trailerIframe = document.getElementById('trailer-iframe');

    if (playBtn && trailerKey) {
      playBtn.addEventListener('click', () => {
        // Set iframe source
        trailerIframe.src = `https://www.youtube.com/embed/${trailerKey}?autoplay=1&rel=0`;
        
        // Fade out backdrop and info
        backdropContainer.style.opacity = '0';
        movieInfoOverlay.style.opacity = '0';
        
        setTimeout(() => {
          backdropContainer.style.pointerEvents = 'none';
          trailerContainer.style.opacity = '1';
          trailerContainer.style.pointerEvents = 'auto';
          movieInfoSection.classList.remove('hidden');
        }, 500);
      });
    }

    if (closeBtn) {
      closeBtn.addEventListener('click', () => {
        // Stop video
        trailerIframe.src = '';
        
        // Fade out trailer
        trailerContainer.style.opacity = '0';
        trailerContainer.style.pointerEvents = 'none';
        
        setTimeout(() => {
          backdropContainer.style.opacity = '1';
          backdropContainer.style.pointerEvents = 'auto';
          movieInfoOverlay.style.opacity = '1';
          movieInfoSection.classList.add('hidden');
        }, 500);
      });
    }

    // Tab switching
    const tabs = document.querySelectorAll('[data-tab]');
    tabs.forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
        document.getElementById(btn.getAttribute('data-tab')).classList.remove('hidden');
        tabs.forEach(b => {
          b.classList.remove('text-white', 'border-white');
          b.classList.add('text-gray-400', 'border-transparent');
        });
        btn.classList.remove('text-gray-400', 'border-transparent');
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
        if (d.success) {
          location.reload();
          // Achievement checking handled server-side
        }
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
        if (d.success) {
          location.reload();
          // Achievement checking handled server-side
        }
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
            div.className = 'bg-gray-900 p-6 rounded-lg border border-gray-700 shadow-inner';
            div.innerHTML = `
            <div class="flex items-center justify-between text-sm text-gray-400 mb-2">
              <span>@${r.username}</span>
              <span>${new Date(r.created_at).toLocaleString()}</span>
            </div>
            <p class="text-gray-100 leading-relaxed whitespace-pre-wrap">${r.review_text.replace(/\n/g, '<br>')}</p>
          `;
            list.appendChild(div);
          });
          document.getElementById('review-textarea').value = '';
          // Achievement checking handled server-side
        } else {
          alert(data.error || 'Failed to submit review');
        }
      }).catch(() => alert('Failed to submit review'));
    });
  </script>
</body>

</html>