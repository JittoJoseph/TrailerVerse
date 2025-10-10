<?php
require_once __DIR__ . '/../config/tmdb_config.php';
require_once __DIR__ . '/../config/database.php';

class MovieService
{
  private $db;

  public function __construct()
  {
    $database = new Database();
    $this->db = $database->connect();
  }

  public function getTrendingMovies()
  {
    // Fetch latest trending from API and update cache
    $url = TMDB_BASE_URL . '/trending/movie/week?api_key=' . TMDB_API_KEY . '&include_adult=false';
    // Try file_get_contents
    $response = @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 3]]));
    // Fallback to cURL if needed
    if (!$response && function_exists('curl_version')) {
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 3);
      $response = curl_exec($ch);
      curl_close($ch);
    }
    if ($response) {
      $data = json_decode($response, true);
      if (!empty($data['results'])) {
        $this->updateMovieCache($data['results']);
        // Return fresh API data formatted to match cached format
        $filtered = array_filter($data['results'], fn($m) => !($m['adult'] ?? false) && $m['id'] != 7451);
        $formatted = array_map(function ($movie) {
          return [
            'id' => $movie['id'],
            'title' => $movie['title'],
            'overview' => $movie['overview'],
            'poster_path' => $movie['poster_path'],
            'backdrop_path' => $movie['backdrop_path'],
            'release_date' => $movie['release_date'],
            'vote_average' => $movie['vote_average'],
            'vote_count' => $movie['vote_count']
          ];
        }, array_slice($filtered, 0, 20));
        return ['results' => $formatted];
      }
    }
    // Fallback to cached movies if API fails
    return $this->getCachedMovies();
  }

  private function updateMovieCache($movies)
  {
    // Filter out adult movies and blocked movies
    $movies = array_filter($movies, fn($movie) => !($movie['adult'] ?? false) && $movie['id'] != 7451);

    // Prepare insert statement with named placeholders
    $sql = 'INSERT INTO movie_cache (movie_id, title, overview, poster_path, backdrop_path, release_date, runtime, vote_average, vote_count, genre_ids, trailer_key, trending_order, cached_at)
            VALUES (:id, :title, :overview, :poster, :backdrop, :release, :runtime, :avg, :count, :genres, :trailer, :order, NOW())
            ON DUPLICATE KEY UPDATE
              title = VALUES(title), overview = VALUES(overview), poster_path = VALUES(poster_path),
              backdrop_path = VALUES(backdrop_path), release_date = VALUES(release_date), runtime = COALESCE(VALUES(runtime), runtime),
              vote_average = VALUES(vote_average), vote_count = VALUES(vote_count), genre_ids = VALUES(genre_ids),
              trailer_key = COALESCE(VALUES(trailer_key), trailer_key), trending_order = VALUES(trending_order), cached_at = NOW()';
    $stmt = $this->db->prepare($sql);
    foreach (array_slice($movies, 0, 20) as $index => $movie) {
      $stmt->execute([
        ':id' => $movie['id'],
        ':title' => $movie['title'],
        ':overview' => $movie['overview'] ?? '',
        ':poster' => $movie['poster_path'] ?? '',
        ':backdrop' => $movie['backdrop_path'] ?? '',
        ':release' => $movie['release_date'] ?? null,
        ':runtime' => $movie['runtime'] ?? null,
        ':avg' => $movie['vote_average'] ?? 0,
        ':count' => $movie['vote_count'] ?? 0,
        ':genres' => json_encode($movie['genre_ids'] ?? []),
        ':trailer' => $movie['trailer_key'] ?? null,
        ':order' => $index,
      ]);
    }
  }

  private function updateMovieCacheGeneral($movies)
  {
    // Filter out adult movies and blocked movies
    $movies = array_filter($movies, fn($movie) => !($movie['adult'] ?? false) && $movie['id'] != 7451);

    // Prepare insert statement with named placeholders - DON'T touch trending_order for trending movies
    $sql = 'INSERT INTO movie_cache (movie_id, title, overview, poster_path, backdrop_path, release_date, runtime, vote_average, vote_count, genre_ids, trailer_key, trending_order, cached_at)
            VALUES (:id, :title, :overview, :poster, :backdrop, :release, :runtime, :avg, :count, :genres, :trailer, 999, NOW())
            ON DUPLICATE KEY UPDATE
              title = VALUES(title), overview = VALUES(overview), poster_path = VALUES(poster_path),
              backdrop_path = VALUES(backdrop_path), release_date = VALUES(release_date), runtime = COALESCE(VALUES(runtime), runtime),
              vote_average = VALUES(vote_average), vote_count = VALUES(vote_count), genre_ids = VALUES(genre_ids),
              trailer_key = COALESCE(VALUES(trailer_key), trailer_key), cached_at = NOW()';
    $stmt = $this->db->prepare($sql);
    foreach ($movies as $movie) {
      $stmt->execute([
        ':id' => $movie['id'],
        ':title' => $movie['title'],
        ':overview' => $movie['overview'] ?? '',
        ':poster' => $movie['poster_path'] ?? '',
        ':backdrop' => $movie['backdrop_path'] ?? '',
        ':release' => $movie['release_date'] ?? null,
        ':runtime' => $movie['runtime'] ?? null,
        ':avg' => $movie['vote_average'] ?? 0,
        ':count' => $movie['vote_count'] ?? 0,
        ':genres' => json_encode($movie['genre_ids'] ?? []),
        ':trailer' => $movie['trailer_key'] ?? null,
      ]);
    }
  }

  private function formatMovies($movies)
  {
    return [
      'results' => array_map(function ($movie) {
        return [
          'id' => $movie['movie_id'],
          'title' => $movie['title'],
          'overview' => $movie['overview'],
          'poster_path' => $movie['poster_path'],
          'backdrop_path' => $movie['backdrop_path'],
          'release_date' => $movie['release_date'],
          'vote_average' => $movie['vote_average'],
          'vote_count' => $movie['vote_count']
        ];
      }, $movies)
    ];
  }

  // Fetch cached movies from database and format
  private function getCachedMovies()
  {
    $stmt = $this->db->prepare("SELECT * FROM movie_cache WHERE movie_id != 7451 AND trending_order < 100 ORDER BY trending_order ASC LIMIT 20");
    $stmt->execute();
    $rows = $stmt->fetchAll();
    return $this->formatMovies($rows);
  }

  /**
   * Fetch cached movies for a specific genre and format them.
   * Uses JSON_CONTAINS on the genre_ids JSON column.
   *
   * @param int $genreId
   * @return array
   */
  private function getCachedMoviesByGenre(int $genreId)
  {
    // Use JSON_CONTAINS to find numeric genre ID in JSON array (MariaDB/MySQL syntax)
    // Cast :id to integer for JSON_CONTAINS to match stored genre_ids type
    $sql = 'SELECT * FROM movie_cache WHERE JSON_CONTAINS(genre_ids, JSON_ARRAY(CAST(:id AS UNSIGNED))) ORDER BY cached_at DESC LIMIT 20';
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':id' => $genreId]);
    $rows = $stmt->fetchAll();
    return $this->formatMovies($rows);
  }

  private function getCachedMovieDetails($id)
  {
    $stmt = $this->db->prepare('SELECT * FROM movie_cache WHERE movie_id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
      return null;
    }
    // Decode genre IDs and fetch genre names
    $genreIds = json_decode($row['genre_ids'], true) ?: [];
    $genres = [];
    if ($genreIds) {
      $placeholders = implode(',', array_fill(0, count($genreIds), '?'));
      $stmt2 = $this->db->prepare("SELECT id, name FROM genres WHERE id IN ($placeholders)");
      $stmt2->execute($genreIds);
      $genres = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }
    return [
      'id' => (int)$row['movie_id'],
      'title' => $row['title'],
      'overview' => $row['overview'],
      'poster_path' => $row['poster_path'],
      'backdrop_path' => $row['backdrop_path'],
      'release_date' => $row['release_date'],
      'runtime' => isset($row['runtime']) ? (int)$row['runtime'] : null,
      'vote_average' => isset($row['vote_average']) ? (float)$row['vote_average'] : null,
      'vote_count' => isset($row['vote_count']) ? (int)$row['vote_count'] : null,
      'trailer_key' => $row['trailer_key'] ?? null,
      'similar_movies' => isset($row['similar_movies']) ? json_decode($row['similar_movies'], true) : null,
      'genres' => $genres
    ];
  }
  private function updateMovieDetailCache(array $movie)
  {
    $sql = 'INSERT INTO movie_cache (movie_id, title, overview, poster_path, backdrop_path, release_date, runtime, vote_average, vote_count, genre_ids, trailer_key, similar_movies, trending_order, cached_at)
            VALUES (:id, :title, :overview, :poster, :backdrop, :release, :runtime, :avg, :count, :genres, :trailer, :similar, 0, NOW())
            ON DUPLICATE KEY UPDATE
              title = VALUES(title), overview = VALUES(overview), poster_path = VALUES(poster_path),
              backdrop_path = VALUES(backdrop_path), release_date = VALUES(release_date), runtime = VALUES(runtime),
              vote_average = VALUES(vote_average), vote_count = VALUES(vote_count), genre_ids = VALUES(genre_ids), 
              trailer_key = VALUES(trailer_key), similar_movies = VALUES(similar_movies), cached_at = NOW()';
    $stmt = $this->db->prepare($sql);
    $genreIds = array_map(fn($g) => $g['id'], $movie['genres'] ?? []);
    $stmt->execute([
      ':id' => $movie['id'],
      ':title' => $movie['title'] ?? '',
      ':overview' => $movie['overview'] ?? '',
      ':poster' => $movie['poster_path'] ?? '',
      ':backdrop' => $movie['backdrop_path'] ?? '',
      ':release' => $movie['release_date'] ?? null,
      ':runtime' => $movie['runtime'] ?? null,
      ':avg' => $movie['vote_average'] ?? 0,
      ':count' => $movie['vote_count'] ?? 0,
      ':genres' => json_encode($genreIds),
      ':trailer' => $movie['trailer_key'] ?? null,
      ':similar' => isset($movie['similar_movies']) ? json_encode($movie['similar_movies']) : null,
    ]);
  }
  public function getMovieDetails($id)
  {
    // Always return cached data first
    $cached = $this->getCachedMovieDetails($id);
    
    // Try to fetch fresh data from API in background
    $url = TMDB_BASE_URL . "/movie/{$id}?api_key=" . TMDB_API_KEY . "&include_adult=false";
    $response = @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 3]]));
    if (!$response && function_exists('curl_version')) {
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 3);
      $response = curl_exec($ch);
      curl_close($ch);
    }
    if ($response) {
      $data = json_decode($response, true);
      // Fetch trailer/video data if not already cached
      if (empty($cached['trailer_key'])) {
        $trailerKey = $this->getMovieTrailer($id);
        if ($trailerKey) {
          $data['trailer_key'] = $trailerKey;
        }
      } else {
        $data['trailer_key'] = $cached['trailer_key'];
      }
      // Update cache with detailed info
      $this->updateMovieDetailCache($data);
      return $data;
    }
    // Return cached data if API fails
    return $cached ? $cached : [];
  }

  public function getMovieTrailer($id)
  {
    $url = TMDB_BASE_URL . "/movie/{$id}/videos?api_key=" . TMDB_API_KEY;
    $response = @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 3]]));
    if (!$response && function_exists('curl_version')) {
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 3);
      $response = curl_exec($ch);
      curl_close($ch);
    }
    if ($response) {
      $data = json_decode($response, true);
      if (!empty($data['results'])) {
        // Find YouTube trailer - prefer official trailers
        foreach ($data['results'] as $video) {
          if ($video['site'] === 'YouTube' && $video['type'] === 'Trailer') {
            return $video['key'];
          }
        }
        // Fallback to any YouTube video
        foreach ($data['results'] as $video) {
          if ($video['site'] === 'YouTube') {
            return $video['key'];
          }
        }
      }
    }
    return null;
  }

  public function getMoviesByGenre($genreId)
  {
    // Always show cached movies first
    $cached = $this->getCachedMoviesByGenre($genreId);
    // Try to update cache in background
    $url = TMDB_BASE_URL . "/discover/movie?api_key=" . TMDB_API_KEY . "&with_genres={$genreId}&sort_by=popularity.desc&include_adult=false";
    $response = @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 3]]));
    if (!$response && function_exists('curl_version')) {
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 3);
      $response = curl_exec($ch);
      curl_close($ch);
    }
    if ($response) {
      $data = json_decode($response, true) ?: ['results' => []];
      if (!empty($data['results'])) {
        $this->updateMovieCacheGeneral($data['results']);
      }
    }
    // Always return cached movies for display
    return $cached;
  }

  public function getSimilarMovies($movieId)
  {
    // Check cache first
    $cached = $this->getCachedMovieDetails($movieId);
    if ($cached && !empty($cached['similar_movies'])) {
      // Return cached similar movies
      return ['results' => $cached['similar_movies']];
    }

    // Fetch from API and cache
    $url = TMDB_BASE_URL . "/movie/{$movieId}/similar?api_key=" . TMDB_API_KEY . "&include_adult=false";
    $response = @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 3]]));
    if (!$response && function_exists('curl_version')) {
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 3);
      $response = curl_exec($ch);
      curl_close($ch);
    }
    if ($response) {
      $data = json_decode($response, true);
      if (!empty($data['results'])) {
        // Filter out adult content and blocked movies, then limit to 12 movies
        $filtered = array_filter($data['results'], fn($m) => !($m['adult'] ?? false) && $m['id'] != 7451);
        $similarMovies = array_slice($filtered, 0, 12);

        // Cache the similar movies with the movie details
        if ($cached) {
          $cached['similar_movies'] = $similarMovies;
          $this->updateMovieDetailCache($cached);
        }

        return ['results' => $similarMovies];
      }
    }
    return ['results' => []];
  }

  public function searchMovies($query, $page = 1)
  {
    // Always return cached search results immediately
    $stmt = $this->db->prepare("SELECT * FROM movie_cache WHERE title LIKE ? ORDER BY vote_average DESC, vote_count DESC LIMIT 20");
    $stmt->execute(['%' . $query . '%']);
    $cached = $stmt->fetchAll();
    $cachedResults = $this->formatMovies($cached)['results'];

    // Update cache in background with fresh API data
    $url = TMDB_BASE_URL . "/search/movie?api_key=" . TMDB_API_KEY . "&query=" . urlencode($query) . "&page=" . $page . "&include_adult=false";
    $response = @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 3]]));
    if (!$response && function_exists('curl_version')) {
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 3);
      $response = curl_exec($ch);
      curl_close($ch);
    }
    if ($response) {
      $data = json_decode($response, true);
      if (!empty($data['results'])) {
        // Cache search results without affecting trending order
        $this->updateMovieCacheGeneral($data['results']);
      }
    }

    // Always return cached results for immediate display
    return ['results' => $cachedResults, 'total_pages' => 1, 'page' => 1];
  }

  public function discoverMovies($filters = [])
  {
    // Try to return cached movies that might match the filters
    $query = "SELECT * FROM movie_cache WHERE trending_order >= 999"; // Get general cached movies
    $params = [];

    if (!empty($filters['genre'])) {
      $query .= " AND JSON_CONTAINS(genre_ids, JSON_ARRAY(?))";
      $params[] = $filters['genre'];
    }

    if (!empty($filters['year'])) {
      $query .= " AND YEAR(release_date) = ?";
      $params[] = $filters['year'];
    }

    if (!empty($filters['min_rating'])) {
      $query .= " AND vote_average >= ?";
      $params[] = $filters['min_rating'];
    }

    $query .= " ORDER BY vote_average DESC, vote_count DESC LIMIT 20";

    $stmt = $this->db->prepare($query);
    $stmt->execute($params);
    $cached = $stmt->fetchAll();
    $cachedResults = $this->formatMovies($cached)['results'];

    // If no cached results match, return trending movies
    if (empty($cachedResults)) {
      $cachedResults = $this->getCachedMovies()['results'];
    }

    // Update cache in background with fresh API data
    $params = [
      'api_key' => TMDB_API_KEY,
      'sort_by' => $filters['sort_by'] ?? 'popularity.desc',
      'include_adult' => 'false',
      'page' => $filters['page'] ?? 1
    ];

    if (!empty($filters['genre'])) {
      $params['with_genres'] = $filters['genre'];
    }
    if (!empty($filters['year'])) {
      $params['primary_release_year'] = $filters['year'];
    }
    if (!empty($filters['min_rating'])) {
      $params['vote_average.gte'] = $filters['min_rating'];
    }

    $queryString = http_build_query($params);
    $url = TMDB_BASE_URL . "/discover/movie?" . $queryString;

    $response = @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 3]]));
    if (!$response && function_exists('curl_version')) {
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 3);
      $response = curl_exec($ch);
      curl_close($ch);
    }

    if ($response) {
      $data = json_decode($response, true);
      if (!empty($data['results'])) {
        // Cache discovered movies without affecting trending order
        $this->updateMovieCacheGeneral($data['results']);
      }
    }

    // Always return cached results for immediate display
    return ['results' => $cachedResults, 'total_pages' => 1, 'page' => 1];
  }
}
