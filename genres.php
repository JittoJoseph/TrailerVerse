<?php
require_once 'config/app.php';
require_once 'config/tmdb_config.php';
require_once 'services/GenreService.php';

$genreService = new GenreService();
$genres = $genreService->getGenres();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title>Genres - TrailerVerse</title>
  <?php include 'includes/head.php'; ?>
</head>

<body class="bg-slate-950 text-white min-h-screen">

  <?php include 'includes/header.php'; ?>

  <!-- Main Content -->
  <div class="pt-24 max-w-7xl mx-auto px-6 pb-12">
    <h1 class="text-3xl font-bold mb-8">Browse Genres</h1>

    <?php if (!empty($genres)): ?>
      <?php
      // Predefined gradient backgrounds
      $gradients = [
        'from-indigo-600 to-purple-600',
        'from-green-500 to-teal-500',
        'from-pink-500 to-red-500',
        'from-yellow-500 to-orange-500',
        'from-blue-500 to-indigo-500',
        'from-purple-700 to-pink-700',
        'from-red-600 to-yellow-600',
        'from-teal-400 to-green-400'
      ];
      ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php foreach ($genres as $index => $genre):
          $grad = $gradients[$index % count($gradients)];
        ?>
         <a href="genre-movies.php?id=<?= $genre['id'] ?>"
            class="flex items-center justify-center rounded-lg h-48 bg-gradient-to-r <?= $grad ?> hover:scale-105 transition-transform">
            <h3 class="text-2xl font-bold text-white text-center px-4"><?= htmlspecialchars($genre['name']) ?></h3>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="text-gray-400">No genres available at this time.</p>
    <?php endif; ?>
  </div>

  <?php include 'includes/footer.php'; ?>

</body>

</html>