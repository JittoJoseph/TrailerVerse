<?php
require_once __DIR__ . '/../config/database.php';

class MovieStatusService
{
  private $db;

  public function __construct($dbConn)
  {
    $this->db = $dbConn;
  }

  /**
   * Rate a movie and automatically mark as watched.
   * @return bool
   */
  public function rateMovie(int $userId, int $movieId, int $rating): bool
  {
    $sql = 'INSERT INTO movie_ratings (user_id, movie_id, rating, created_at, updated_at)
                VALUES (:user, :movie, :rate, NOW(), NOW())
                ON DUPLICATE KEY UPDATE rating = :rate, updated_at = NOW()';
    $stmt = $this->db->prepare($sql);
    $ok = $stmt->execute([':user' => $userId, ':movie' => $movieId, ':rate' => $rating]);
    if (!$ok) {
      return false;
    }
    // Mark as watched
    $sql2 = "INSERT INTO movie_status (user_id, movie_id, status, date_watched)
                 VALUES (?, ?, 'watched', NOW())
                 ON DUPLICATE KEY UPDATE status='watched', date_watched=NOW()";
    $stmt2 = $this->db->prepare($sql2);
    $stmt2->execute([$userId, $movieId]);
    return true;
  }

  /**
   * Toggle watchlist status.
   * @return bool new state (in watchlist)
   */
  public function toggleWatchlist(int $userId, int $movieId): bool
  {
    $check = $this->db->prepare('SELECT status FROM movie_status WHERE user_id=? AND movie_id=?');
    $check->execute([$userId, $movieId]);
    $status = $check->fetchColumn();
    if ($status === 'want_to_watch') {
      $del = $this->db->prepare('DELETE FROM movie_status WHERE user_id=? AND movie_id=?');
      $del->execute([$userId, $movieId]);
      return false;
    }
    $ins = $this->db->prepare("INSERT INTO movie_status (user_id, movie_id, status) VALUES (?, ?, 'want_to_watch') ON DUPLICATE KEY UPDATE status='want_to_watch', date_added=NOW()");
    $ins->execute([$userId, $movieId]);
    return true;
  }

  /**
   * Toggle watched status.
   * @return bool new state (watched)
   */
  public function toggleWatched(int $userId, int $movieId): bool
  {
    $check = $this->db->prepare('SELECT status FROM movie_status WHERE user_id=? AND movie_id=?');
    $check->execute([$userId, $movieId]);
    $status = $check->fetchColumn();
    if ($status === 'watched') {
      $del = $this->db->prepare('DELETE FROM movie_status WHERE user_id=? AND movie_id=?');
      $del->execute([$userId, $movieId]);
      return false;
    }
    $ins = $this->db->prepare("INSERT INTO movie_status (user_id, movie_id, status, date_watched) VALUES (?, ?, 'watched', NOW()) ON DUPLICATE KEY UPDATE status='watched', date_watched=NOW()");
    $ins->execute([$userId, $movieId]);
    return true;
  }

  /**
   * Get user status (watchlist, watched, rating).
   */
  public function getStatus(int $userId, int $movieId): array
  {
    $status = ['inWatchlist' => false, 'watched' => false, 'rating' => 0];
    $sr = $this->db->prepare('SELECT status FROM movie_status WHERE user_id=? AND movie_id=?');
    $sr->execute([$userId, $movieId]);
    $st = $sr->fetchColumn();
    if ($st === 'want_to_watch') {
      $status['inWatchlist'] = true;
    } elseif ($st === 'watched') {
      $status['watched'] = true;
    }
    $rr = $this->db->prepare('SELECT rating FROM movie_ratings WHERE user_id=? AND movie_id=?');
    $rr->execute([$userId, $movieId]);
    $status['rating'] = (int)$rr->fetchColumn() ?: 0;
    return $status;
  }
  /**
   * Handle AJAX POST actions for status: rate, watchlist, watched.
   * Outputs JSON and exits if handled.
   */
  public static function handleAjax($dbConn)
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      return;
    }
    $action = $_POST['action'] ?? '';
    if (!in_array($action, ['rate', 'watchlist', 'watched'], true)) {
      return;
    }
    if (!isset($_SESSION['user_id'])) {
      http_response_code(401);
      echo json_encode(['error' => 'Not logged in']);
      exit;
    }
    $uid = (int) $_SESSION['user_id'];
    $service = new self($dbConn);
    switch ($action) {
      case 'rate':
        $movieId = filter_input(INPUT_POST, 'movie_id', FILTER_VALIDATE_INT);
        $rating  = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
        if (!$movieId || $rating < 1 || $rating > 5) {
          http_response_code(400);
          echo json_encode(['error' => 'Invalid data']);
          exit;
        }
        $success = $service->rateMovie($uid, $movieId, $rating);
        $status  = $service->getStatus($uid, $movieId);
        echo json_encode([
          'success'     => $success,
          'watched'     => $status['watched'],
          'inWatchlist' => $status['inWatchlist'],
        ]);
        exit;

      case 'watchlist':
        $movieId = filter_input(INPUT_POST, 'movie_id', FILTER_VALIDATE_INT);
        $inList  = $service->toggleWatchlist($uid, $movieId);
        echo json_encode(['success' => true, 'inWatchlist' => $inList]);
        exit;

      case 'watched':
        $movieId = filter_input(INPUT_POST, 'movie_id', FILTER_VALIDATE_INT);
        $watched = $service->toggleWatched($uid, $movieId);
        echo json_encode(['success' => true, 'watched' => $watched]);
        exit;
    }
  }
}
