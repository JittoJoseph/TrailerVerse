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
    // Load cached genres
    $stmt = $this->db->prepare('SELECT id, name FROM genres ORDER BY name');
    $stmt->execute();
    $cached = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Always attempt to refresh cache from API
    $url = TMDB_BASE_URL . '/genre/movie/list?api_key=' . TMDB_API_KEY;
    $response = @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 3]]));
    if (!$response && function_exists('curl_version')) {
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 3);
      $response = curl_exec($ch);
      curl_close($ch);
    }
    $apiGenres = [];
    if ($response) {
      $data = json_decode($response, true);
      $apiGenres = $data['genres'] ?? [];
      $apiGenres = array_filter($apiGenres, function ($g) {
        return !in_array($g['id'], [10749, 18, 14, 9648]);
      });
      if (!empty($apiGenres)) {
        $this->cacheGenres($apiGenres);
      }
    }
    // Return cache if available, else API data
    if (!empty($cached)) {
      return $cached;
    }
    return $apiGenres;
  }

  /**
   * Stores genres into local database for caching.
   *
   * @param array $genres
   */
  private function cacheGenres(array $genres)
  {
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
