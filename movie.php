<?php
require_once 'config/app.php';
require_once 'config/tmdb_config.php';
require_once 'services/MovieService.php';
require_once 'config/database.php';

$dbConn = (new Database())->connect();
// Handle AJAX rating submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'rate') {
  if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
  }
  $userId = (int)$_SESSION['user_id'];
  $movieId = filter_input(INPUT_POST, 'movie_id', FILTER_VALIDATE_INT);
  $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
  if (!$movieId || $rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data']);
    exit;
  }
  $sql = 'INSERT INTO movie_ratings (user_id, movie_id, rating, created_at, updated_at) VALUES (:user, :movie, :rate, NOW(), NOW()) ON DUPLICATE KEY UPDATE rating = :rate, updated_at = NOW()';
  $stmt = $dbConn->prepare($sql);
  $stmt->execute([':user' => $userId, ':movie' => $movieId, ':rate' => $rating]);
  echo json_encode(['success' => true]);
  exit;
}
// Fetch current user rating
$userRating = 0;
if (isset($_SESSION['user_id'])) {
  $uid = (int)$_SESSION['user_id'];
  $mid = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
  $sr = $dbConn->prepare('SELECT rating FROM movie_ratings WHERE user_id=? AND movie_id=?');
  $sr->execute([$uid, $mid]);
  $userRating = (int)$sr->fetchColumn() ?: 0;
}
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
  header('Location: index.php');
  exit;
}

$movieService = new MovieService();
$movie = $movieService->getMovieDetails($id);
$similar = $movieService->getSimilarMovies($id);
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
      <div class="flex space-x-4">
        <button class="flex items-center px-6 py-3 bg-white text-black rounded-md hover:bg-gray-100 transition">
          <i class="fas fa-play mr-2"></i> Play Trailer
        </button>
        <button class="flex items-center px-6 py-3 glass rounded-md hover:bg-white/10 transition">
          <i class="fas fa-plus mr-2"></i> My List
        </button>
      </div>
    </div>
    <div class="absolute bottom-0 left-0 w-full h-48 bg-gradient-to-t from-black to-transparent"></div>
  </section>

  <!-- Details Tabs -->
  <div class="max-w-7xl mx-auto px-6 py-12">
    <ul id="tabs" class="flex space-x-8 border-b border-gray-700">
      <li><button data-tab="details" class="pb-2 text-lg text-white border-b-2 border-transparent hover:border-white">Details</button></li>
      <li><button data-tab="similar" class="pb-2 text-lg text-gray-400 hover:text-white hover:border-white">Similar Movies</button></li>
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
    <!-- Inject 5-star rating in Details via JS -->
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
    // Inject 5-star rating under Details
    document.addEventListener('DOMContentLoaded', function() {
      const detailsTab = document.getElementById('details');
      const ratingDiv = document.createElement('div');
      ratingDiv.className = 'mt-6 flex items-center space-x-2';
      ratingDiv.innerHTML = '<span class="text-white font-medium">Your Rating:</span>' +
        Array.from({
            length: 5
          }, (_, i) =>
          `<i class="fa-star ${i<<?= $userRating ?>?'fas':'far'} cursor-pointer text-yellow-400 rating-star" data-value="${i+1}"></i>`
        ).join('');
      detailsTab.appendChild(ratingDiv);
      const stars = ratingDiv.querySelectorAll('.rating-star');
      stars.forEach(star => {
        star.addEventListener('click', function() {
          const val = this.getAttribute('data-value');
          fetch('', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `action=rate&movie_id=<?= $id ?>&rating=${val}`
          }).then(res => res.json()).then(data => {
            if (data.success) {
              stars.forEach(s => {
                s.classList.toggle('fas', s.getAttribute('data-value') <= val);
                s.classList.toggle('far', s.getAttribute('data-value') > val);
              });
            }
          });
        });
      });
    });
  </script>
</body>

</html>