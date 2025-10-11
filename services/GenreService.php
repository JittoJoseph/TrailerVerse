<?php
require_once __DIR__ . '/../config/tmdb_config.php';
require_once __DIR__ . '/../config/database.php';
class GenreService
{
  /** @var PDO */
  private $db;

  public function __construct()
  {
    $database = new Database();
    $this->db = $database->connect();
  }

  /**
   * Fetches the list of movie genres, using cache if available.
   *
   * @return array List of genres (each with id and name).
   */
  public function getGenres()
  {
    // Always return cached genres first
    $stmt = $this->db->prepare('SELECT id, name FROM genres WHERE id NOT IN (10749, 18, 14, 9648, 99, 10770, 36) ORDER BY name');
    $stmt->execute();
    $cached = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Update cache in background with fresh API data
    $url = TMDB_BASE_URL . '/genre/movie/list?api_key=' . TMDB_API_KEY;
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
      $apiGenres = $data['genres'] ?? [];
      // Filter out blocked genres (Romance, Drama, Fantasy, Mystery, genre 99, TV Movie, History)
      $apiGenres = array_filter($apiGenres, function ($g) {
        return !in_array($g['id'], [10749, 18, 14, 9648, 99, 10770, 36]);
      });
      if (!empty($apiGenres)) {
        $this->cacheGenres($apiGenres);
      }
    }

    // Always return cached genres for immediate display
    return $cached;
  }

  /**
   * Stores genres into local database for caching.
   *
   * @param array $genres
   */
  private function cacheGenres(array $genres)
  {
    // Filter out blocked genres before caching
    $genres = array_filter($genres, function ($g) {
      return !in_array($g['id'], [10749, 18, 14, 9648, 99, 10770, 36]);
    });

    $sql = 'INSERT INTO genres (id, name) VALUES (:id, :name)
            ON DUPLICATE KEY UPDATE name = VALUES(name)';
    $stmt = $this->db->prepare($sql);
    foreach ($genres as $genre) {
      $stmt->execute([
        ':id' => $genre['id'],
        ':name' => $genre['name'],
      ]);
    }
  }
}
