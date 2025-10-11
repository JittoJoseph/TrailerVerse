<?php
require_once 'config/app.php';
require_once 'config/tmdb_config.php';
require_once 'services/MovieService.php';
require_once 'services/GenreService.php';

$movieService = new MovieService();
$genreService = new GenreService();
$genres = $genreService->getGenres();

// Get filter parameters
$searchQuery = $_GET['search'] ?? '';
$selectedGenre = $_GET['genre'] ?? '';
$selectedYear = $_GET['year'] ?? '';
$selectedSort = $_GET['sort'] ?? 'popularity.desc';
$minRating = $_GET['rating'] ?? '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Fetch movies based on filters
if (!empty($searchQuery)) {
  $result = $movieService->searchMovies($searchQuery, $page);
} else {
  $filters = [
    'genre' => $selectedGenre,
    'year' => $selectedYear,
    'sort_by' => $selectedSort,
    'min_rating' => $minRating,
    'page' => $page
  ];
  $result = $movieService->discoverMovies($filters);
}

$movies = $result['results'] ?? [];
$totalPages = $result['total_pages'] ?? 1;
$currentPage = $result['page'] ?? 1;

// Generate year options
$currentYear = date('Y');
$years = range($currentYear, 1950);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title>Explore Movies - TrailerVerse</title>
  <?php include 'includes/head.php'; ?>
</head>

<body class="bg-slate-950 text-white min-h-screen">

  <?php include 'includes/header.php'; ?>

  <!-- Main Content -->
  <div class="pt-24 max-w-7xl mx-auto px-6 pb-12">
    
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-4xl font-bold mb-2">
        <?php if (!empty($searchQuery)): ?>
          Search Results for "<?= htmlspecialchars($searchQuery) ?>"
        <?php else: ?>
          Explore Movies
        <?php endif; ?>
      </h1>
      <p class="text-gray-400">Discover your next favorite movie</p>
    </div>

    <!-- Filters Section -->
    <div class="glass rounded-2xl p-6 mb-8">
      <form method="GET" action="explore.php" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        
        <!-- Search Input -->
        <div>
          <label class="block text-sm text-gray-400 mb-2">Search</label>
          <input type="text" 
                 name="search" 
                 value="<?= htmlspecialchars($searchQuery) ?>"
                 placeholder="Movie title..." 
                 class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-white/50">
        </div>

        <!-- Genre Filter -->
        <div>
          <label class="block text-sm text-gray-400 mb-2">Genre</label>
          <select name="genre" class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-white/50">
            <option value="">All Genres</option>
            <?php foreach ($genres as $genre): ?>
              <option value="<?= $genre['id'] ?>" <?= $selectedGenre == $genre['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($genre['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Year Filter -->
        <div>
          <label class="block text-sm text-gray-400 mb-2">Year</label>
          <select name="year" class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-white/50">
            <option value="">All Years</option>
            <?php foreach ($years as $year): ?>
              <option value="<?= $year ?>" <?= $selectedYear == $year ? 'selected' : '' ?>>
                <?= $year ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Rating Filter -->
        <div>
          <label class="block text-sm text-gray-400 mb-2">Min Rating</label>
          <select name="rating" class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-white/50">
            <option value="">Any Rating</option>
            <option value="7" <?= $minRating == '7' ? 'selected' : '' ?>>7+ ⭐</option>
            <option value="8" <?= $minRating == '8' ? 'selected' : '' ?>>8+ ⭐⭐</option>
            <option value="9" <?= $minRating == '9' ? 'selected' : '' ?>>9+ ⭐⭐⭐</option>
          </select>
        </div>

        <!-- Sort By -->
        <div>
          <label class="block text-sm text-gray-400 mb-2">Sort By</label>
          <select name="sort" class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-white/50">
            <option value="popularity.desc" <?= $selectedSort == 'popularity.desc' ? 'selected' : '' ?>>Popularity</option>
            <option value="vote_average.desc" <?= $selectedSort == 'vote_average.desc' ? 'selected' : '' ?>>Rating</option>
            <option value="release_date.desc" <?= $selectedSort == 'release_date.desc' ? 'selected' : '' ?>>Release Date</option>
            <option value="title.asc" <?= $selectedSort == 'title.asc' ? 'selected' : '' ?>>Title (A-Z)</option>
          </select>
        </div>

        <!-- Submit Buttons -->
        <div class="md:col-span-5 flex gap-4">
          <button type="submit" class="px-6 py-2 bg-white text-slate-950 rounded-lg hover:bg-gray-100 transition-colors font-medium">
            <i class="fas fa-filter mr-2"></i>Apply Filters
          </button>
          <a href="explore.php" class="px-6 py-2 glass rounded-lg hover:bg-white/10 transition-colors">
            <i class="fas fa-redo mr-2"></i>Clear All
          </a>
        </div>
      </form>
    </div>

    <!-- Results Count -->
    <?php if (!empty($movies)): ?>
    <div class="mb-6 text-gray-400">
      Found <?= count($movies) ?> movies
    </div>
    <?php endif; ?>

    <!-- Movies Grid -->
    <?php if (!empty($movies)): ?>
      <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-8">
        <?php foreach ($movies as $movie): ?>
          <a href="movie.php?id=<?= htmlspecialchars($movie['id']) ?>" class="group cursor-pointer">
            <div class="relative rounded-lg overflow-hidden mb-3">
              <img src="<?= getTMDBPosterUrl($movie['poster_path'] ?? '') ?>"
                alt="<?= htmlspecialchars($movie['title']) ?>"
                class="w-full h-64 object-cover transition-transform group-hover:scale-105">

              <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                <button class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center glass">
                  <i class="fas fa-play text-white"></i>
                </button>
              </div>

              <div class="absolute top-2 right-2 glass px-2 py-1 rounded text-xs">
                <i class="fas fa-star text-yellow-400 mr-1"></i>
                <?= number_format($movie['vote_average'] ?? 0, 1) ?>
              </div>
            </div>

            <h3 class="text-sm font-medium group-hover:text-gray-300 transition-colors">
              <?= strlen($movie['title']) > 20 ? substr($movie['title'], 0, 20) . '...' : $movie['title'] ?>
            </h3>
            <p class="text-xs text-gray-500">
              <?= !empty($movie['release_date']) ? date('Y', strtotime($movie['release_date'])) : 'N/A' ?>
            </p>
          </a>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
      <div class="flex justify-center items-center space-x-4">
        <?php if ($currentPage > 1): ?>
          <a href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])) ?>" 
             class="px-4 py-2 glass rounded-lg hover:bg-white/10 transition-colors">
            <i class="fas fa-chevron-left"></i> Previous
          </a>
        <?php endif; ?>

        <span class="text-gray-400">Page <?= $currentPage ?> of <?= min($totalPages, 500) ?></span>

        <?php if ($currentPage < $totalPages && $currentPage < 500): ?>
          <a href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>" 
             class="px-4 py-2 glass rounded-lg hover:bg-white/10 transition-colors">
            Next <i class="fas fa-chevron-right"></i>
          </a>
        <?php endif; ?>
      </div>
      <?php endif; ?>

    <?php else: ?>
      <!-- No Results -->
      <div class="text-center py-16">
        <div class="w-24 h-24 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-6">
          <i class="fas fa-film text-gray-400 text-4xl"></i>
        </div>
        <h3 class="text-2xl font-semibold mb-2">No Movies Found</h3>
        <p class="text-gray-400 mb-6">Try adjusting your filters or search query</p>
        <a href="explore.php" class="px-6 py-3 bg-white text-slate-950 rounded-lg hover:bg-gray-100 transition-colors font-medium inline-block">
          <i class="fas fa-redo mr-2"></i>Start Fresh
        </a>
      </div>
    <?php endif; ?>

  </div>

  <?php include 'includes/footer.php'; ?>

</body>

</html>
