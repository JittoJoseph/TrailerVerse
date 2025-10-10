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
    $url = TMDB_BASE_URL . '/trending/movie/week?api_key=' . TMDB_API_KEY;
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
      }
    }
    // Return cached movies for display
    return $this->getCachedMovies();
  }

  private function updateMovieCache($movies)
  {
    // Filter out adult movies
    $movies = array_filter($movies, fn($movie) => !($movie['adult'] ?? false));

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
    $stmt = $this->db->prepare("SELECT * FROM movie_cache WHERE movie_id != 7451 ORDER BY trending_order ASC LIMIT 20");
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
      'genres' => $genres
    ];
  }
  private function updateMovieDetailCache(array $movie)
  {
    $sql = 'INSERT INTO movie_cache (movie_id, title, overview, poster_path, backdrop_path, release_date, runtime, vote_average, vote_count, genre_ids, trailer_key, trending_order, cached_at)
            VALUES (:id, :title, :overview, :poster, :backdrop, :release, :runtime, :avg, :count, :genres, :trailer, 0, NOW())
            ON DUPLICATE KEY UPDATE
              title = VALUES(title), overview = VALUES(overview), poster_path = VALUES(poster_path),
              backdrop_path = VALUES(backdrop_path), release_date = VALUES(release_date), runtime = VALUES(runtime),
              vote_average = VALUES(vote_average), vote_count = VALUES(vote_count), genre_ids = VALUES(genre_ids), 
              trailer_key = VALUES(trailer_key), cached_at = NOW()';
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
    ]);
  }
  public function getMovieDetails($id)
  {
    // Always return cached data first
    $cached = $this->getCachedMovieDetails($id);
    
    // Try to fetch fresh data from API in background
    $url = TMDB_BASE_URL . "/movie/{$id}?api_key=" . TMDB_API_KEY;
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
        $this->updateMovieCache($data['results']);
      }
    }
    // Always return cached movies for display
    return $cached;
  }

  public function getSimilarMovies($movieId)
  {
    $url = TMDB_BASE_URL . "/movie/{$movieId}/similar?api_key=" . TMDB_API_KEY;
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
        // Filter out adult content and limit to 12 movies
        $filtered = array_filter($data['results'], fn($m) => !($m['adult'] ?? false));
        return ['results' => array_slice($filtered, 0, 12)];
      }
    }
    return ['results' => []];
  }

  public function searchMovies($query, $page = 1)
  {
    // First, search in cached movies
    $stmt = $this->db->prepare("SELECT * FROM movie_cache WHERE title LIKE ? ORDER BY vote_average DESC, vote_count DESC LIMIT 20");
    $stmt->execute(['%' . $query . '%']);
    $cached = $stmt->fetchAll();
    
    // Also fetch from API
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
        // Cache search results
        $this->updateMovieCache($data['results']);
        // Filter adult content
        $filtered = array_filter($data['results'], fn($m) => !($m['adult'] ?? false));
        return [
          'results' => $filtered,
          'total_pages' => $data['total_pages'] ?? 1,
          'page' => $data['page'] ?? 1
        ];
      }
    }
    
    // Return cached results if API fails
    return ['results' => $this->formatMovies($cached)['results'], 'total_pages' => 1, 'page' => 1];
  }

  public function discoverMovies($filters = [])
  {
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
        $this->updateMovieCache($data['results']);
        $filtered = array_filter($data['results'], fn($m) => !($m['adult'] ?? false));
        return [
          'results' => $filtered,
          'total_pages' => $data['total_pages'] ?? 1,
          'page' => $data['page'] ?? 1
        ];
      }
    }
    
    // Fallback to cached trending movies
    return $this->getTrendingMovies();
  }
}
