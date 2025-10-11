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
  <section id="hero-section" class="relative min-h-screen w-full">
    <!-- Backdrop Image -->
    <div id="backdrop-container" class="absolute inset-0 transition-opacity duration-500">
      <img src="<?= getTMDBBackdropUrl($movie['backdrop_path'] ?? '') ?>" alt="" class="absolute top-0 left-0 w-full h-full object-cover object-center md:object-top">
      <div class="absolute inset-0 bg-black bg-opacity-40 md:bg-opacity-50"></div>
      <div class="absolute inset-0 bg-gradient-to-b from-transparent via-black/20 to-black md:to-black/80"></div>
      
      <!-- Play Button (only show if trailer exists) -->
      <?php if (!empty($movie['trailer_key'])): ?>
      <div class="absolute inset-0 flex items-center justify-center z-20">
        <button id="play-trailer-btn" class="flex items-center justify-center w-16 h-16 md:w-20 md:h-20 border-2 border-white/80 rounded-full bg-black/20 backdrop-blur-sm hover:border-white hover:bg-white/10 transition-all hover:scale-110 shadow-lg">
          <i class="fas fa-play text-white text-xl md:text-2xl ml-0.5"></i>
        </button>
      </div>
      <?php endif; ?>
      
      <!-- Movie Info Overlay -->
      <div id="movie-info-overlay" class="absolute bottom-0 left-0 right-0 p-4 md:p-8 max-w-none md:max-w-2xl space-y-2 md:space-y-4 z-20 transition-opacity duration-500">
        <h1 class="text-3xl md:text-6xl font-bold gradient-text leading-tight"><?= htmlspecialchars($movie['title'] ?? '') ?></h1>
        <p class="text-gray-200 md:text-gray-300 max-w-none md:max-w-lg leading-relaxed text-sm md:text-base line-clamp-3 md:line-clamp-none"><?= htmlspecialchars($movie['overview'] ?? '') ?></p>
        <div class="mt-3 md:mt-4 flex flex-wrap gap-2 md:gap-4">
          <?php if (isset($_SESSION['user_id'])): ?>
            <button id="btn-watchlist" class="flex items-center px-3 py-2 md:px-4 md:py-2 text-sm md:text-base <?php echo $inWatchlist ? 'bg-red-600' : 'bg-white text-black'; ?> rounded-md hover:opacity-90 transition">
              <i class="fas fa-list mr-1 md:mr-2"></i><span class="hidden sm:inline"><?= $inWatchlist ? 'Remove from List' : 'Add to Watchlist' ?></span><span class="sm:hidden">List</span>
            </button>
            <button id="btn-watched" class="flex items-center px-3 py-2 md:px-4 md:py-2 text-sm md:text-base <?php echo $watched ? 'bg-green-600' : 'glass'; ?> rounded-md hover:opacity-90 transition">
              <i class="fas fa-check mr-1 md:mr-2"></i><span class="hidden sm:inline"><?= $watched ? 'Unmark Watched' : 'Mark as Watched' ?></span><span class="sm:hidden">Watched</span>
            </button>
          <?php else: ?>
            <a href="auth/signin.php" class="px-3 py-2 md:px-4 md:py-2 text-sm md:text-base bg-blue-500 rounded-md hover:opacity-90 transition">Sign in</a>
          <?php endif; ?>
        </div>
      </div>
      
      <div class="absolute bottom-0 left-0 w-full h-32 md:h-48 bg-gradient-to-t from-black to-transparent"></div>
    </div>

    <!-- YouTube Trailer Player (Hidden Initially) -->
    <div id="trailer-container" class="absolute inset-0 opacity-0 pointer-events-none transition-opacity duration-500 flex items-center justify-center bg-black z-30">
      <div class="relative w-full max-w-7xl mx-auto md:px-4" style="aspect-ratio: 16/9;">
        <iframe id="trailer-iframe" 
                class="w-full h-full rounded-lg md:rounded-lg shadow-2xl" 
                frameborder="0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                allowfullscreen>
        </iframe>
        <!-- Close Trailer Button -->
        <button id="close-trailer-btn" class="absolute -top-14 md:-top-12 right-4 md:right-0 z-30 w-10 h-10 md:w-12 md:h-12 bg-black/50 hover:bg-black/70 rounded-full flex items-center justify-center transition backdrop-blur-sm">
          <i class="fas fa-times text-white text-lg md:text-xl"></i>
        </button>
      </div>
    </div>
  </section>

  <!-- Details Tabs -->
  <div id="details-tabs-section" class="max-w-7xl mx-auto px-4 md:px-6 pt-6 md:pt-8 lg:pt-24 pb-12">
    <ul id="tabs" class="flex space-x-4 md:space-x-8 border-b border-gray-700 overflow-x-auto">
      <li><button data-tab="details" class="pb-2 text-base md:text-lg text-white border-b-2 border-white hover:border-white whitespace-nowrap">Details</button></li>
      <li><button data-tab="reviews" class="pb-2 text-base md:text-lg text-gray-400 border-b-2 border-transparent hover:text-white hover:border-white whitespace-nowrap">Reviews</button></li>
    </ul>
    <div id="details" class="tab-content pt-6 md:pt-8">
      <!-- Movie Overview Card -->
      <div class="glass rounded-2xl p-6 md:p-8 mb-6 md:mb-8 border border-white/10">
        <div class="flex flex-col lg:flex-row lg:items-start lg:space-x-8 space-y-6 lg:space-y-0">
          <!-- Poster -->
          <div class="flex-shrink-0 mx-auto lg:mx-0">
            <img src="<?= getTMDBPosterUrl($movie['poster_path'] ?? '') ?>" 
                 alt="<?= htmlspecialchars($movie['title']) ?>" 
                 class="w-48 md:w-56 lg:w-64 rounded-2xl shadow-2xl border border-white/10">
          </div>

          <!-- Movie Info -->
          <div class="flex-1 text-center lg:text-left">
            <h2 class="text-2xl md:text-3xl lg:text-4xl font-bold gradient-text mb-4 md:mb-6">
              <?= htmlspecialchars($movie['title'] ?? '') ?>
            </h2>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-8">
              <div class="glass rounded-xl p-3 md:p-4 text-center">
                <div class="text-2xl md:text-3xl font-bold text-white mb-1">
                  <?= ($movie['runtime'] ?? 0) ?>m
                </div>
                <div class="text-xs md:text-sm text-gray-400 uppercase tracking-wide">Runtime</div>
              </div>
              
              <div class="glass rounded-xl p-3 md:p-4 text-center">
                <div class="text-2xl md:text-3xl font-bold text-white mb-1 flex items-center justify-center">
                  <i class="fas fa-star text-yellow-400 mr-1"></i>
                  <?= number_format($movie['vote_average'], 1) ?>
                </div>
                <div class="text-xs md:text-sm text-gray-400 uppercase tracking-wide">Rating</div>
              </div>
              
              <div class="glass rounded-xl p-3 md:p-4 text-center">
                <div class="text-2xl md:text-3xl font-bold text-white mb-1">
                  <?= date('Y', strtotime($movie['release_date'] ?? 'now')) ?>
                </div>
                <div class="text-xs md:text-sm text-gray-400 uppercase tracking-wide">Year</div>
              </div>
              
              <div class="glass rounded-xl p-3 md:p-4 text-center">
                <div class="text-2xl md:text-3xl font-bold text-white mb-1">
                  <?= htmlspecialchars($movie['original_language'] ?? 'EN') ?>
                </div>
                <div class="text-xs md:text-sm text-gray-400 uppercase tracking-wide">Language</div>
              </div>
            </div>

            <!-- Genres -->
            <div class="mb-6 md:mb-8">
              <h3 class="text-lg md:text-xl font-semibold text-white mb-3 md:mb-4">Genres</h3>
              <div class="flex flex-wrap justify-center lg:justify-start gap-2 md:gap-3">
                <?php foreach ($movie['genres'] ?? [] as $genre): ?>
                  <span class="px-3 md:px-4 py-1.5 md:py-2 bg-gradient-to-r from-blue-500/20 to-purple-500/20 border border-blue-500/30 rounded-full text-sm md:text-base font-medium text-white hover:from-blue-500/30 hover:to-purple-500/30 transition-all duration-300">
                    <?= htmlspecialchars($genre['name']) ?>
                  </span>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Overview -->
            <div class="mb-6 md:mb-8">
              <h3 class="text-lg md:text-xl font-semibold text-white mb-3 md:mb-4">Overview</h3>
              <p class="text-gray-300 leading-relaxed text-sm md:text-base">
                <?= nl2br(htmlspecialchars($movie['overview'] ?? '')) ?>
              </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap justify-center lg:justify-start gap-3 md:gap-4">
              <?php if (isset($_SESSION['user_id'])): ?>
                <button id="btn-watchlist" class="flex items-center px-4 py-2 md:px-6 md:py-3 text-sm md:text-base <?php echo $inWatchlist ? 'bg-red-600 hover:bg-red-700' : 'glass hover:bg-white/10'; ?> rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                  <i class="fas fa-list mr-2"></i><span class="hidden sm:inline"><?= $inWatchlist ? 'Remove from List' : 'Add to Watchlist' ?></span><span class="sm:hidden">List</span>
                </button>
                <button id="btn-watched" class="flex items-center px-4 py-2 md:px-6 md:py-3 text-sm md:text-base <?php echo $watched ? 'bg-green-600 hover:bg-green-700' : 'glass hover:bg-white/10'; ?> rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                  <i class="fas fa-check mr-2"></i><span class="hidden sm:inline"><?= $watched ? 'Unmark Watched' : 'Mark as Watched' ?></span><span class="sm:hidden">Watched</span>
                </button>
              <?php else: ?>
                <a href="auth/signin.php" class="flex items-center px-4 py-2 md:px-6 md:py-3 text-sm md:text-base bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                  <i class="fas fa-sign-in-alt mr-2"></i>Sign in to manage list
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- User Rating Section -->
      <div class="glass rounded-2xl p-6 md:p-8 border border-white/10">
        <div class="text-center lg:text-left">
          <h3 class="text-xl md:text-2xl font-semibold text-white mb-4 md:mb-6">Rate this movie</h3>
          
          <?php if (isset($_SESSION['user_id'])): ?>
            <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start space-y-4 sm:space-y-0 sm:space-x-6">
              <div class="flex space-x-1">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <i class="rating-star <?php echo ($i <= $userRating ? 'fas' : 'far'); ?> fa-star cursor-pointer text-yellow-400 text-2xl md:text-3xl hover:text-yellow-300 transition-colors" data-value="<?= $i ?>"></i>
                <?php endfor; ?>
              </div>
              <div class="text-sm md:text-base text-gray-400">
                <?php if ($userRating > 0): ?>
                  You rated this movie <span class="text-yellow-400 font-semibold"><?= $userRating ?>/5 stars</span>
                <?php else: ?>
                  Click on a star to rate this movie
                <?php endif; ?>
              </div>
            </div>
          <?php else: ?>
            <div class="flex flex-col items-center space-y-4">
              <div class="flex space-x-1">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <i class="far fa-star text-gray-600 text-2xl md:text-3xl" data-value="<?= $i ?>"></i>
                <?php endfor; ?>
              </div>
              <div class="text-center">
                <p class="text-gray-400 mb-4">Want to rate this movie?</p>
                <a href="auth/signin.php" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                  <i class="fas fa-sign-in-alt mr-2"></i>Sign In to Rate
                </a>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div id="reviews" class="tab-content hidden pt-6 md:pt-8">
      <!-- Write Review Section -->
      <div class="glass rounded-2xl p-4 md:p-6 mb-6 md:mb-8 border border-white/10">
        <div class="flex items-start space-x-3 md:space-x-4 mb-4">
          <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center flex-shrink-0">
            <?php if (isset($_SESSION['user_id'])): ?>
              <span class="text-white font-bold text-sm md:text-base">
                <?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)) ?>
              </span>
            <?php else: ?>
              <i class="fas fa-user text-white text-sm md:text-base"></i>
            <?php endif; ?>
          </div>
          <div class="flex-1">
            <h3 class="text-lg md:text-xl font-semibold text-white mb-2">
              <?php if (isset($_SESSION['user_id'])): ?>
                Share your thoughts
              <?php else: ?>
                Sign in to write a review
              <?php endif; ?>
            </h3>
            <p class="text-gray-400 text-sm mb-4">
              <?php if (isset($_SESSION['user_id'])): ?>
                What did you think of this movie? Share your review with the community.
              <?php else: ?>
                Join the conversation and share your movie reviews.
              <?php endif; ?>
            </p>
          </div>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
          <div class="relative">
            <textarea id="review-textarea"
                      rows="4"
                      maxlength="1000"
                      class="w-full p-4 md:p-5 bg-slate-800/50 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 resize-none backdrop-blur-sm"
                      placeholder="Write your review here..."></textarea>
            <div class="absolute bottom-3 right-3 text-xs text-gray-500">
              <span id="char-count">0</span>/1000
            </div>
          </div>

          <div class="flex items-center justify-between mt-4">
            <div class="flex items-center space-x-2 text-sm text-gray-400">
              <i class="fas fa-info-circle"></i>
              <span>Your review will be visible to all users</span>
            </div>
            <button id="btn-submit-review"
                    class="px-6 py-2.5 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
              <i class="fas fa-paper-plane mr-2"></i>
              Post Review
            </button>
          </div>
        <?php else: ?>
          <div class="text-center py-6">
            <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
              <i class="fas fa-comment-dots text-gray-400 text-2xl"></i>
            </div>
            <p class="text-gray-400 mb-4">Want to share your thoughts on this movie?</p>
            <a href="auth/signin.php" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
              <i class="fas fa-sign-in-alt mr-2"></i>
              Sign In to Review
            </a>
          </div>
        <?php endif; ?>
      </div>

      <!-- Reviews List -->
      <div id="reviews-list" class="space-y-4 md:space-y-6">
        <?php if (!empty($reviews)): ?>
          <?php foreach ($reviews as $index => $r): ?>
            <div class="glass rounded-2xl p-4 md:p-6 border border-white/10 hover:border-white/20 transition-all duration-300 group">
              <div class="flex items-start space-x-3 md:space-x-4">
                <!-- User Avatar -->
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center flex-shrink-0">
                  <span class="text-white font-bold text-sm md:text-base">
                    <?= strtoupper(substr($r['username'], 0, 1)) ?>
                  </span>
                </div>

                <!-- Review Content -->
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-2">
                      <h4 class="font-semibold text-white text-sm md:text-base group-hover:text-gray-200 transition-colors">
                        @<?= htmlspecialchars($r['username']) ?>
                      </h4>
                      <div class="w-1 h-1 bg-gray-500 rounded-full"></div>
                      <span class="text-xs md:text-sm text-gray-400">
                        <?= date('M j, Y \a\t g:i A', strtotime($r['created_at'])) ?>
                      </span>
                    </div>

                    <!-- Review Actions (for own reviews) -->
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['username'] === $r['username']): ?>
                      <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center space-x-2">
                        <button class="text-gray-400 hover:text-blue-400 transition-colors p-1" title="Edit">
                          <i class="fas fa-edit text-xs"></i>
                        </button>
                        <button class="text-gray-400 hover:text-red-400 transition-colors p-1" title="Delete">
                          <i class="fas fa-trash text-xs"></i>
                        </button>
                      </div>
                    <?php endif; ?>
                  </div>

                  <!-- Review Text -->
                  <div class="text-gray-100 leading-relaxed whitespace-pre-wrap text-sm md:text-base">
                    <?= htmlspecialchars($r['review_text']) ?>
                  </div>

                  <!-- Review Footer -->
                  <div class="flex items-center justify-end mt-4 pt-3 border-t border-white/10">
                    <!-- Review Rating (if available) -->
                    <?php if (isset($r['rating']) && $r['rating'] > 0): ?>
                      <div class="flex items-center space-x-1">
                        <div class="flex space-x-0.5">
                          <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star text-xs md:text-sm <?= $i <= $r['rating'] ? 'text-yellow-400' : 'text-gray-600' ?>"></i>
                          <?php endfor; ?>
                        </div>
                        <span class="text-xs md:text-sm text-gray-400 ml-1"><?= $r['rating'] ?>/5</span>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>

          <!-- Load More Button (if more reviews exist) -->
          <?php if (count($reviews) >= 10): ?>
            <div class="text-center pt-4">
              <button class="px-6 py-3 glass rounded-xl hover:bg-white/10 transition-all duration-300 text-sm md:text-base font-medium">
                <i class="fas fa-chevron-down mr-2"></i>
                Load More Reviews
              </button>
            </div>
          <?php endif; ?>

        <?php else: ?>
          <!-- Empty State -->
          <div class="glass rounded-2xl p-8 md:p-12 text-center border border-white/10">
            <div class="w-20 h-20 md:w-24 md:h-24 bg-white/10 rounded-3xl flex items-center justify-center mx-auto mb-6">
              <i class="fas fa-comments text-gray-400 text-3xl md:text-4xl"></i>
            </div>
            <h3 class="text-xl md:text-2xl font-semibold text-white mb-3">No reviews yet</h3>
            <p class="text-gray-400 text-sm md:text-base mb-6 max-w-md mx-auto">
              Be the first to share your thoughts about this movie. Your review could help other movie lovers discover great films!
            </p>
            <?php if (isset($_SESSION['user_id'])): ?>
              <button onclick="document.querySelector('#reviews .tab-content').scrollIntoView({behavior: 'smooth'})"
                      class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                <i class="fas fa-pen mr-2"></i>
                Write First Review
              </button>
            <?php else: ?>
              <a href="auth/signin.php"
                 class="inline-block px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                <i class="fas fa-sign-in-alt mr-2"></i>
                Sign In to Review
              </a>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Similar Movies Section -->
  <?php if (!empty($similarMoviesResults)): ?>
  <section class="max-w-7xl mx-auto px-4 md:px-6 py-8 md:py-12">
    <h2 class="text-xl md:text-2xl font-semibold mb-4 md:mb-6">More Like This</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3 md:gap-4">
      <?php foreach ($similarMoviesResults as $similar): ?>
        <a href="movie.php?id=<?= htmlspecialchars($similar['id']) ?>" class="group cursor-pointer">
          <div class="relative rounded-lg overflow-hidden mb-2 md:mb-3 aspect-[2/3]">
            <img src="<?= getTMDBPosterUrl($similar['poster_path'] ?? '') ?>"
              alt="<?= htmlspecialchars($similar['title']) ?>"
              class="w-full h-full object-cover transition-transform group-hover:scale-105">

            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
              <button class="w-10 h-10 md:w-12 md:h-12 bg-white/20 rounded-full flex items-center justify-center glass">
                <i class="fas fa-play text-white text-sm md:text-base"></i>
              </button>
            </div>

            <div class="absolute top-1 md:top-2 right-1 md:right-2 glass px-1 md:px-2 py-0.5 md:py-1 rounded text-xs">
              <i class="fas fa-star text-yellow-400 mr-0.5 md:mr-1"></i>
              <?= number_format($similar['vote_average'] ?? 0, 1) ?>
            </div>
          </div>

          <h3 class="text-xs md:text-sm font-medium group-hover:text-gray-300 transition-colors line-clamp-2 leading-tight">
            <?= htmlspecialchars($similar['title']) ?>
          </h3>
          <p class="text-xs text-gray-500 mt-0.5 md:mt-1">
            <?= date('Y', strtotime($similar['release_date'] ?? 'now')) ?>
          </p>
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
    document.addEventListener('click', function(e) {
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
          // Update star display in details section
          const detailsSection = document.getElementById('details');
          if (detailsSection) {
            detailsSection.querySelectorAll('.rating-star').forEach(s => {
              const v = s.getAttribute('data-value');
              s.classList.toggle('fas', v <= val);
              s.classList.toggle('far', v > val);
            });
          }

          // Update watched button state
          const watchedBtn = document.getElementById('btn-watched');
          if (watchedBtn) {
            if (data.watched) {
              watchedBtn.classList.remove('glass');
              watchedBtn.classList.add('bg-green-600', 'hover:bg-green-700');
              watchedBtn.innerHTML = '<i class="fas fa-check mr-2"></i><span class="hidden sm:inline">Unmark Watched</span><span class="sm:hidden">Watched</span>';
            } else {
              watchedBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
              watchedBtn.classList.add('glass', 'hover:bg-white/10');
              watchedBtn.innerHTML = '<i class="fas fa-check mr-2"></i><span class="hidden sm:inline">Mark as Watched</span><span class="sm:hidden">Watched</span>';
            }
          }

          // Update watchlist button state
          const watchlistBtn = document.getElementById('btn-watchlist');
          if (watchlistBtn) {
            if (data.inWatchlist) {
              watchlistBtn.classList.remove('glass', 'hover:bg-white/10');
              watchlistBtn.classList.add('bg-red-600', 'hover:bg-red-700');
              watchlistBtn.innerHTML = '<i class="fas fa-list mr-2"></i><span class="hidden sm:inline">Remove from List</span><span class="sm:hidden">List</span>';
            } else {
              watchlistBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
              watchlistBtn.classList.add('glass', 'hover:bg-white/10');
              watchlistBtn.innerHTML = '<i class="fas fa-list mr-2"></i><span class="hidden sm:inline">Add to Watchlist</span><span class="sm:hidden">List</span>';
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
          // Update button state
          if (d.inWatchlist) {
            this.classList.remove('glass', 'hover:bg-white/10');
            this.classList.add('bg-red-600', 'hover:bg-red-700');
            this.innerHTML = '<i class="fas fa-list mr-2"></i><span class="hidden sm:inline">Remove from List</span><span class="sm:hidden">List</span>';
          } else {
            this.classList.remove('bg-red-600', 'hover:bg-red-700');
            this.classList.add('glass', 'hover:bg-white/10');
            this.innerHTML = '<i class="fas fa-list mr-2"></i><span class="hidden sm:inline">Add to Watchlist</span><span class="sm:hidden">List</span>';
          }
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
          // Update button state
          if (d.watched) {
            this.classList.remove('glass', 'hover:bg-white/10');
            this.classList.add('bg-green-600', 'hover:bg-green-700');
            this.innerHTML = '<i class="fas fa-check mr-2"></i><span class="hidden sm:inline">Unmark Watched</span><span class="sm:hidden">Watched</span>';
          } else {
            this.classList.remove('bg-green-600', 'hover:bg-green-700');
            this.classList.add('glass', 'hover:bg-white/10');
            this.innerHTML = '<i class="fas fa-check mr-2"></i><span class="hidden sm:inline">Mark as Watched</span><span class="sm:hidden">Watched</span>';
          }
        }
      });
    });
    // Review submission
    document.getElementById('btn-submit-review')?.addEventListener('click', function() {
      const text = document.getElementById('review-textarea').value.trim();
      const button = this;

      if (!text) {
        alert('Please write a review before submitting.');
        return;
      }

      if (text.length > 1000) {
        alert('Review is too long. Please keep it under 1000 characters.');
        return;
      }

      // Disable button and show loading state
      button.disabled = true;
      button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Posting...';

      fetch(window.location.href, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=review&movie_id=<?= $id ?>&review_text=${encodeURIComponent(text)}`
      }).then(res => res.json()).then(data => {
        if (data.success) {
          const list = document.getElementById('reviews-list');

          // Clear existing reviews and add new ones
          list.innerHTML = '';
          data.reviews.forEach(r => {
            const div = document.createElement('div');
            div.className = 'glass rounded-2xl p-4 md:p-6 border border-white/10 hover:border-white/20 transition-all duration-300 group';
            div.innerHTML = `
              <div class="flex items-start space-x-3 md:space-x-4">
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center flex-shrink-0">
                  <span class="text-white font-bold text-sm md:text-base">${r.username.charAt(0).toUpperCase()}</span>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-2">
                      <h4 class="font-semibold text-white text-sm md:text-base group-hover:text-gray-200 transition-colors">
                        @${r.username}
                      </h4>
                      <div class="w-1 h-1 bg-gray-500 rounded-full"></div>
                      <span class="text-xs md:text-sm text-gray-400">
                        ${new Date(r.created_at).toLocaleDateString('en-US', {
                          month: 'short',
                          day: 'numeric',
                          year: 'numeric'
                        })} at ${new Date(r.created_at).toLocaleTimeString('en-US', {
                          hour: 'numeric',
                          minute: '2-digit',
                          hour12: true
                        })}
                      </span>
                    </div>
                  </div>
                  <div class="text-gray-100 leading-relaxed whitespace-pre-wrap text-sm md:text-base">
                    ${r.review_text.replace(/\n/g, '<br>')}
                  </div>
                  <div class="flex items-center justify-end mt-4 pt-3 border-t border-white/10">
                  </div>
                </div>
              </div>
            `;
            list.appendChild(div);
          });

          // Clear textarea and reset character count
          document.getElementById('review-textarea').value = '';
          document.getElementById('char-count').textContent = '0';

          // Reset button
          button.disabled = false;
          button.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Post Review';

          // Achievement checking handled server-side
        } else {
          alert(data.error || 'Failed to submit review');
          button.disabled = false;
          button.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Post Review';
        }
      }).catch(() => {
        alert('Failed to submit review');
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Post Review';
      });
    });

    // Character counter for textarea
    document.getElementById('review-textarea')?.addEventListener('input', function() {
      const count = this.value.length;
      document.getElementById('char-count').textContent = count;

      // Change color based on character count
      const charCountEl = document.getElementById('char-count');
      if (count > 900) {
        charCountEl.className = 'absolute bottom-3 right-3 text-xs text-red-400';
      } else if (count > 800) {
        charCountEl.className = 'absolute bottom-3 right-3 text-xs text-yellow-400';
      } else {
        charCountEl.className = 'absolute bottom-3 right-3 text-xs text-gray-500';
      }
    });
  </script>
</body>

</html>