<?php
require_once 'config/app.php';
require_once 'config/tmdb_config.php';
require_once 'services/MovieService.php';

// Validate movie ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
  header('Location: index.php');
  exit;
}

$movieService = new MovieService();
$movie = $movieService->getMovieDetails($id);
$credits = $movieService->getMovieCredits($id);
$similar = $movieService->getSimilarMovies($id); // assume new method
$cast = $credits['cast'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title><?= htmlspecialchars($movie['title'] ?? 'Movie Details') ?> - TrailerVerse</title>
  <?php include 'includes/head.php'; ?>
</head>

<body class="bg-black text-white overflow-x-hidden">
  <?php include 'includes/header_detail.php'; ?>

  <!-- Fullscreen Backdrop Section -->
  <section class="absolute top-0 left-0 h-screen w-full">
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
      <li><button data-tab="cast" class="pb-2 text-lg text-gray-400 hover:text-white hover:border-white">Cast</button></li>
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
    <div id="cast" class="tab-content hidden pt-8">
      <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-6">
        <?php foreach (array_slice($cast, 0, 12) as $c): ?>
          <div class="text-center">
            <img src="<?= getTMDBImageUrl($c['profile_path'], 'w185') ?>" alt="" class="w-full h-48 object-cover rounded-lg mb-2">
            <div class="font-medium"><?= htmlspecialchars($c['name']) ?></div>
            <div class="text-gray-400 text-sm"><?= htmlspecialchars($c['character']) ?></div>
          </div>
        <?php endforeach; ?>
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
  </div>

  <?php include 'includes/footer.php'; ?>

  <script>

  </script>
</body>

</html>