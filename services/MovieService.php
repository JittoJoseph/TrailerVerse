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
    // Try API first
    $url = TMDB_BASE_URL . '/trending/movie/week?api_key=' . TMDB_API_KEY;
    $response = @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 3]]));
    if ($response) {
      $data = json_decode($response, true);
      if (!empty($data['results'])) {
        // Store order in session
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['trending_ids'] = array_column($data['results'], 'id');
        // Update cache
        $this->updateMovieCache($data['results']);
        return $data;
      }
    }
    // Fallback to cached data
    $cached = $this->getCachedMovies();
    $ids = $_SESSION['trending_ids'] ?? [];
    if (!empty($ids) && !empty($cached['results'])) {
      // Order by stored ids
      $ordered = [];
      foreach ($ids as $id) {
        foreach ($cached['results'] as $movie) {
          if ($movie['id'] == $id) {
            $ordered[] = $movie;
            break;
          }
        }
      }
      return ['results' => array_slice($ordered, 0, 20)];
    }
    return $cached;
  }

  private function updateMovieCache($movies)
  {
    // Prepare insert statement with named placeholders
    $sql = 'INSERT INTO movie_cache (movie_id, title, overview, poster_path, backdrop_path, release_date, vote_average, vote_count, cached_at)
            VALUES (:id, :title, :overview, :poster, :backdrop, :release, :avg, :count, NOW())
            ON DUPLICATE KEY UPDATE
              title = VALUES(title), overview = VALUES(overview), poster_path = VALUES(poster_path),
              backdrop_path = VALUES(backdrop_path), release_date = VALUES(release_date),
              vote_average = VALUES(vote_average), vote_count = VALUES(vote_count), cached_at = NOW()';
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
}
