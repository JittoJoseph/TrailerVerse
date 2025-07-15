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
    // Prepare insert statement with named placeholders
    $sql = 'INSERT INTO movie_cache (movie_id, title, overview, poster_path, backdrop_path, release_date, vote_average, vote_count, genre_ids, cached_at)
            VALUES (:id, :title, :overview, :poster, :backdrop, :release, :avg, :count, :genres, NOW())
            ON DUPLICATE KEY UPDATE
              title = VALUES(title), overview = VALUES(overview), poster_path = VALUES(poster_path),
              backdrop_path = VALUES(backdrop_path), release_date = VALUES(release_date),
              vote_average = VALUES(vote_average), vote_count = VALUES(vote_count), genre_ids = VALUES(genre_ids), cached_at = NOW()';
    $stmt = $this->db->prepare($sql);
    foreach (array_slice($movies, 0, 20) as $movie) {
      $stmt->execute([
        ':id' => $movie['id'],
        ':title' => $movie['title'],
        ':overview' => $movie['overview'] ?? '',
        ':poster' => $movie['poster_path'] ?? '',
        ':backdrop' => $movie['backdrop_path'] ?? '',
        ':release' => $movie['release_date'] ?? null,
        ':avg' => $movie['vote_average'] ?? 0,
        ':count' => $movie['vote_count'] ?? 0,
        ':genres' => json_encode($movie['genre_ids'] ?? []),
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
    $stmt = $this->db->prepare("SELECT * FROM movie_cache ORDER BY cached_at DESC LIMIT 20");
    $stmt->execute();
    $rows = $stmt->fetchAll();
    return $this->formatMovies($rows);
  }

  /**
   * Fetch detailed movie information from TMDB API.
   *
   * @param int $id
   * @return array
   */
  public function getMovieDetails($id)
  {
    $url = TMDB_BASE_URL . "/movie/{$id}?api_key=" . TMDB_API_KEY;
    $response = @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 3]]));
    if (!$response && function_exists('curl_version')) {
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 3);
      $response = curl_exec($ch);
      curl_close($ch);
    }
    return $response ? json_decode($response, true) : [];
  }

  /**
   * Fetch movie cast information from TMDB API.
   *
   * @param int $id
   * @return array
   */
  public function getMovieCredits($id)
  {
    $url = TMDB_BASE_URL . "/movie/{$id}/credits?api_key=" . TMDB_API_KEY;
    $response = @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 3]]));
    if (!$response && function_exists('curl_version')) {
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 3);
      $response = curl_exec($ch);
      curl_close($ch);
    }
    return $response ? json_decode($response, true) : [];
  }

  /**
   * Fetch movie reviews from TMDB API.
   *
   * @param int $id
   * @return array
   */
  public function getMovieReviews($id)
  {
    $url = TMDB_BASE_URL . "/movie/{$id}/reviews?api_key=" . TMDB_API_KEY;
    $response = @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 3]]));
    if (!$response && function_exists('curl_version')) {
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 3);
      $response = curl_exec($ch);
      curl_close($ch);
    }
    return $response ? json_decode($response, true) : [];
  }

  /**
   * Fetch similar movies from TMDB API.
   *
   * @param int $id
   * @return array
   */
  public function getSimilarMovies($id)
  {
    $url = TMDB_BASE_URL . "/movie/{$id}/similar?api_key=" . TMDB_API_KEY;
    $response = @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 3]]));
    if (!$response && function_exists('curl_version')) {
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 3);
      $response = curl_exec($ch);
      curl_close($ch);
    }
    return $response ? json_decode($response, true) : [];
  }
}
