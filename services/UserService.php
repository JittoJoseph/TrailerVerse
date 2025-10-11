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
    // Get basic counts
    $stats = [
      'movies_watched'      => 0,
      'movies_in_watchlist' => 0,
      'reviews_written'     => 0,
      'ratings_given'       => 0,
      'average_rating'      => 0.00,
      'favorite_genre_name' => null,
      'achievement_points'  => 0,
    ];

    // Movies watched count
    $stmt = $this->db->prepare('SELECT COUNT(*) as cnt FROM movie_status WHERE user_id = ? AND status = ?');
    $stmt->execute([$id, 'watched']);
    $stats['movies_watched'] = (int)$stmt->fetchColumn();

    // Movies in watchlist count
    $stmt = $this->db->prepare('SELECT COUNT(*) as cnt FROM movie_status WHERE user_id = ? AND status = ?');
    $stmt->execute([$id, 'want_to_watch']);
    $stats['movies_in_watchlist'] = (int)$stmt->fetchColumn();

    // Reviews written count
    $stmt = $this->db->prepare('SELECT COUNT(*) as cnt FROM movie_reviews WHERE user_id = ?');
    $stmt->execute([$id]);
    $stats['reviews_written'] = (int)$stmt->fetchColumn();

    // Ratings given count and average
    $stmt = $this->db->prepare('SELECT COUNT(*) as cnt, AVG(rating) as avg_rating FROM movie_ratings WHERE user_id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['ratings_given'] = (int)($row['cnt'] ?? 0);
    $stats['average_rating'] = (float)($row['avg_rating'] ?? 0.00);

    // Achievement points
    $stmt = $this->db->prepare('
      SELECT SUM(a.points) as total_points
      FROM user_achievements ua
      JOIN achievements a ON ua.achievement_id = a.id
      WHERE ua.user_id = ?
    ');
    $stmt->execute([$id]);
    $stats['achievement_points'] = (int)($stmt->fetchColumn() ?? 0);

    // Favorite genre (most common genre from watched movies)
    $stmt = $this->db->prepare('
      SELECT g.name, COUNT(*) as genre_count
      FROM movie_status ms
      JOIN movie_cache mc ON ms.movie_id = mc.movie_id
      JOIN genres g ON JSON_CONTAINS(mc.genre_ids, JSON_ARRAY(CAST(g.id AS UNSIGNED)))
      WHERE ms.user_id = ? AND ms.status = ?
      GROUP BY g.id, g.name
      ORDER BY genre_count DESC
      LIMIT 1
    ');
    $stmt->execute([$id, 'watched']);
    $genreRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['favorite_genre_name'] = $genreRow['name'] ?? null;

    return $stats;
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
      'SELECT a.id, a.name, a.description, a.icon, a.points, ua.earned_at
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

  /**
   * Fetch user's watched movies
   *
   * @param int $id
   * @param int $limit
   * @return array
   */
  public function getWatchedMovies(int $id, int $limit = 16): array
  {
    $limit = (int)$limit;
    $sql = 'SELECT ms.date_watched, mc.movie_id, mc.title, mc.poster_path, mc.vote_average, mc.release_date,
                   mr.rating as user_rating
            FROM movie_status ms
            JOIN movie_cache mc ON ms.movie_id = mc.movie_id
            LEFT JOIN movie_ratings mr ON ms.user_id = mr.user_id AND ms.movie_id = mr.movie_id
            WHERE ms.user_id = ? AND ms.status = ?
            ORDER BY ms.date_watched DESC
            LIMIT ' . $limit;
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$id, 'watched']);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Check if one user follows another
   */
  public function isFollowing(int $followerId, int $followingId): bool
  {
    $stmt = $this->db->prepare('SELECT 1 FROM user_follows WHERE follower_id = ? AND following_id = ? LIMIT 1');
    $stmt->execute([$followerId, $followingId]);
    return (bool)$stmt->fetchColumn();
  }

  /**
   * Follow a user (no-op if already following)
   */
  public function followUser(int $followerId, int $followingId): bool
  {
    if ($followerId === $followingId) return false;
    try {
      $stmt = $this->db->prepare('INSERT IGNORE INTO user_follows (follower_id, following_id, created_at) VALUES (?, ?, NOW())');
      return $stmt->execute([$followerId, $followingId]);
    } catch (PDOException $e) {
      return false;
    }
  }

  /**
   * Unfollow a user (no-op if not following)
   */
  public function unfollowUser(int $followerId, int $followingId): bool
  {
    $stmt = $this->db->prepare('DELETE FROM user_follows WHERE follower_id = ? AND following_id = ?');
    return $stmt->execute([$followerId, $followingId]);
  }

  /**
   * Get followers list
   */
  public function getFollowers(int $userId, int $limit = 20): array
  {
    $limit = (int)$limit;
    $sql = 'SELECT u.id, u.username, u.profile_picture
            FROM user_follows uf
            JOIN users u ON uf.follower_id = u.id
            WHERE uf.following_id = ?
            ORDER BY uf.created_at DESC
            LIMIT ' . $limit;
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Get following list
   */
  public function getFollowing(int $userId, int $limit = 20): array
  {
    $limit = (int)$limit;
    $sql = 'SELECT u.id, u.username, u.profile_picture
            FROM user_follows uf
            JOIN users u ON uf.following_id = u.id
            WHERE uf.follower_id = ?
            ORDER BY uf.created_at DESC
            LIMIT ' . $limit;
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Get activity feed for a user (their own + people they follow)
   */
  public function getFeed(int $userId, int $limit = 20, int $offset = 0): array
  {
    $limit = (int)$limit;
    $offset = (int)$offset;
    // Build a unified feed that shows at most one activity per (user, movie).
    // Preference: if the user rated the movie, show the rating activity. Otherwise show watched.
    // Implementation uses a UNION of ratings and watched rows, then window function to pick one row
    // per (user_id, movie_id) prioritizing rated rows and newest timestamp.
    $sql = "WITH feed_raw AS (
                SELECT mr.user_id, mr.movie_id, 'rated_movie' AS activity_type, mr.rating AS rating, mr.updated_at AS ts
                FROM movie_ratings mr
                WHERE mr.movie_id IS NOT NULL
              UNION ALL
                SELECT ms.user_id, ms.movie_id, 'watched_movie' AS activity_type, NULL AS rating, ms.date_watched AS ts
                FROM movie_status ms
                WHERE ms.status = 'watched' AND ms.movie_id IS NOT NULL
              ),
              ranked AS (
                SELECT fr.user_id, fr.movie_id, fr.activity_type, fr.rating, fr.ts,
                       u.username, u.profile_picture, mc.title AS movie_title, mc.poster_path,
                       ROW_NUMBER() OVER (PARTITION BY fr.user_id, fr.movie_id
                                          ORDER BY CASE WHEN fr.activity_type = 'rated_movie' THEN 1 ELSE 0 END DESC, fr.ts DESC) rn
                FROM feed_raw fr
                JOIN users u ON u.id = fr.user_id
                LEFT JOIN movie_cache mc ON mc.movie_id = fr.movie_id
                WHERE u.is_public = TRUE
              )
            SELECT user_id, username, profile_picture, activity_type, movie_id, movie_title, poster_path, rating, ts AS created_at
            FROM ranked
            WHERE rn = 1
              AND (user_id = :uid OR user_id IN (SELECT following_id FROM user_follows WHERE follower_id = :uid))
            ORDER BY created_at DESC
            LIMIT :lim OFFSET :off";

    $stmt = $this->db->prepare($sql);
    // Bind values explicitly. MySQL requires integer bind for LIMIT/OFFSET.
    $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Simple suggested users (public, not followed yet)
   */
  public function getSuggestedUsers(int $userId, int $limit = 6): array
  {
    // Strategy: show public users excluding self. Prioritize people not yet followed,
    // but include already-followed users to keep the list filled when the network is small.
    $limit = (int)$limit;
    $sql = 'SELECT u.id, u.username, u.profile_picture,
                   CASE WHEN uf.follower_id IS NULL THEN 0 ELSE 1 END AS is_following,
                   CASE WHEN uf.follower_id IS NULL THEN 0 ELSE 1 END AS priority
            FROM users u
            LEFT JOIN user_follows uf
              ON uf.follower_id = :uid AND uf.following_id = u.id
            WHERE u.id <> :uid AND u.is_public = TRUE
            ORDER BY priority ASC, RAND()
            LIMIT ' . $limit;
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
