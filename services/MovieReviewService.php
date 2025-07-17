<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

class MovieReviewService
{
  private $db;
  public function __construct($dbConn)
  {
    $this->db = $dbConn;
  }

  public function addReview(int $userId, int $movieId, string $text): bool
  {
    $sql = 'INSERT INTO movie_reviews (user_id, movie_id, review_text, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW()) ON DUPLICATE KEY UPDATE review_text = ?, updated_at = NOW()';
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([$userId, $movieId, $text, $text]);
  }
  public function getReviews(int $movieId): array
  {
    $sql = 'SELECT r.review_text, r.created_at, u.username
                FROM movie_reviews r
                JOIN users u ON u.id = r.user_id
                WHERE r.movie_id = ?
                ORDER BY r.created_at DESC';
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$movieId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  /**
   * Handle AJAX review submission.
   * Outputs JSON and exits if handled.
   */
  public static function handleAjax($dbConn)
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['action'] ?? '') !== 'review') {
      return;
    }
    if (!isset($_SESSION['user_id'])) {
      http_response_code(401);
      echo json_encode(['error' => 'Not logged in']);
      exit;
    }
    $uid = (int) $_SESSION['user_id'];
    $movieId = filter_input(INPUT_POST, 'movie_id', FILTER_VALIDATE_INT);
    $text    = trim($_POST['review_text'] ?? '');
    if (!$movieId || $text === '') {
      http_response_code(400);
      echo json_encode(['error' => 'Invalid review']);
      exit;
    }
    $service = new self($dbConn);
    $ok      = $service->addReview($uid, $movieId, $text);
    $reviews = $service->getReviews($movieId);
    echo json_encode(['success' => $ok, 'reviews' => $reviews]);
    exit;
  }
}
