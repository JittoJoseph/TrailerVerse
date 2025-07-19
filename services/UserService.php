<?php
require_once __DIR__ . '/../config/database.php';

class UserService
{
  private $db;

  public function __construct()
  {
    $database = new Database();
    $this->db = $database->connect();
  }

  /**
   * Fetch user details by ID
   *
   * @param int $id
   * @return array
   */
  public function getUserById(int $id): array
  {
    $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
  }

  /**
   * Fetch aggregated user statistics
   *
   * @param int $id
   * @return array
   */
  public function getUserStats(int $id): array
  {
    $stmt = $this->db->prepare('SELECT * FROM user_stats_view WHERE user_id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    // Return view data or defaults
    return $row ?: [
      'movies_watched'      => 0,
      'movies_in_watchlist' => 0,
      'reviews_written'     => 0,
      'ratings_given'       => 0,
      'average_rating'      => 0.00,
      'favorite_genre_name' => null,
      'achievement_points'  => 0,
    ];
  }

  /**
   * Count how many achievements the user has earned
   *
   * @param int $id
   * @return int
   */
  public function getAchievementCount(int $id): int
  {
    $stmt = $this->db->prepare('SELECT COUNT(*) as cnt FROM user_achievements WHERE user_id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int) ($row['cnt'] ?? 0);
  }

  /**
   * Fetch recent user activities (feed entries)
   *
   * @param int $id
   * @param int $limit
   * @return array
   */
  public function getRecentActivities(int $id, int $limit = 10): array
  {
    $limit = (int)$limit;
    $sql = 'SELECT * FROM user_feed_view WHERE user_id = ? ORDER BY created_at DESC LIMIT ' . $limit;
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Fetch recent reviews written by user
   *
   * @param int $id
   * @param int $limit
   * @return array
   */
  public function getRecentReviews(int $id, int $limit = 5): array
  {
    $limit = (int)$limit;
    $sql = 'SELECT r.review_text, r.created_at, mc.movie_id, mc.title, mc.poster_path
         FROM movie_reviews r
         JOIN movie_cache mc ON r.movie_id = mc.movie_id
         WHERE r.user_id = ?
         ORDER BY r.created_at DESC
         LIMIT ' . $limit;
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Fetch user's watchlist movies
   *
   * @param int $id
   * @param int $limit
   * @return array
   */
  public function getWatchlist(int $id, int $limit = 8): array
  {
    $limit = (int)$limit;
    $sql = 'SELECT ms.date_added, mc.movie_id, mc.title, mc.poster_path, mc.vote_average, mc.release_date
         FROM movie_status ms
         JOIN movie_cache mc ON ms.movie_id = mc.movie_id
         WHERE ms.user_id = ? AND ms.status = ?
         ORDER BY ms.date_added DESC
            LIMIT ' . $limit;
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$id, 'want_to_watch']);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Fetch user's achievements with icons
   *
   * @param int $id
   * @return array
   */
  public function getAchievements(int $id): array
  {
    $stmt = $this->db->prepare(
      'SELECT a.name, a.icon, ua.earned_at
       FROM user_achievements ua
       JOIN achievements a ON ua.achievement_id = a.id
       WHERE ua.user_id = ?
       ORDER BY ua.earned_at DESC'
    );
    $stmt->execute([$id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Count how many users follow this user
   *
   * @param int $id
   * @return int
   */
  public function getFollowersCount(int $id): int
  {
    $stmt = $this->db->prepare('SELECT COUNT(*) FROM user_follows WHERE following_id = ?');
    $stmt->execute([$id]);
    return (int)$stmt->fetchColumn();
  }

  /**
   * Count how many users this user is following
   *
   * @param int $id
   * @return int
   */
  public function getFollowingCount(int $id): int
  {
    $stmt = $this->db->prepare('SELECT COUNT(*) FROM user_follows WHERE follower_id = ?');
    $stmt->execute([$id]);
    return (int)$stmt->fetchColumn();
  }
}
