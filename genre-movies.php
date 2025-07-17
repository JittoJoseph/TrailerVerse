<?php
require_once 'config/app.php';
require_once 'services/GenreService.php';
require_once 'services/MovieService.php';

$genreService = new GenreService();
$movieService = new MovieService();

$genreId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$genreName = '';
$movies = [];

if ($genreId) {
  $genres = $genreService->getGenres();
  foreach ($genres as $g) {
    if ($g['id'] == $genreId) {
      $genreName = $g['name'];
      break;
    }
  }
  $movies = $movieService->getMoviesByGenre($genreId);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title><?= htmlspecialchars($genreName ?: 'Genre') ?> Movies - TrailerVerse</title>
  <?php include 'includes/head.php'; ?>
</head>

<body class="bg-slate-950 text-white min-h-screen">
  <?php include 'includes/header.php'; ?>

  <div class="pt-24 max-w-7xl mx-auto px-6 pb-12">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold"><?= htmlspecialchars($genreName ?: 'Genre') ?> Movies</h1>
      <a href="genres.php" class="text-blue-400 hover:underline">&larr; Back to Genres</a>
    </div>

    <?php if (!empty($movies['results'])): ?>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
        <?php foreach ($movies['results'] as $movie): ?>
          <a href="movie.php?id=<?= htmlspecialchars($movie['id']) ?>" class="group cursor-pointer">
            <div class="relative rounded-lg overflow-hidden mb-3">
              <img src="<?= getTMDBPosterUrl($movie['poster_path']) ?>"
                alt="<?= htmlspecialchars($movie['title']) ?>"
                class="w-full h-72 object-cover transition-transform group-hover:scale-105">
              <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                <button class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center glass">
                  <i class="fas fa-play text-white"></i>
                </button>
              </div>
              <div class="absolute top-2 right-2 glass px-2 py-1 rounded text-xs">
                <?= number_format($movie['vote_average'], 1) ?>
              </div>
            </div>
            <h3 class="text-sm font-medium group-hover:text-gray-300 transition-colors">
              <?= strlen($movie['title']) > 20 ? substr($movie['title'], 0, 20) . '...' : htmlspecialchars($movie['title']) ?>
            </h3>
            <p class="text-xs text-gray-500"><?= date('Y', strtotime($movie['release_date'] ?? '')) ?></p>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="text-gray-400 mt-6">No movies found for this genre.</p>
    <?php endif; ?>
  </div>

  <?php include 'includes/footer.php'; ?>
</body>

</html>