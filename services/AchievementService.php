<?php
require_once __DIR__ . '/../config/database.php';

class AchievementService
{
  private $db;

  public function __construct()
  {
    $database = new Database();
    $this->db = $database->connect();
  }

  public function getLatestAchievements()
  {
    if (!$this->db) return [];

    $sql = "SELECT 
              a.id,
              a.name, 
              a.description, 
              a.icon,
              u.username,
              ua.earned_at
            FROM user_achievements ua
            JOIN achievements a ON ua.achievement_id = a.id  
            JOIN users u ON ua.user_id = u.id
            WHERE u.is_public = 1
            ORDER BY ua.earned_at DESC 
            LIMIT 6";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Get user's earned achievements for profile display
   */
  public function getUserAchievements(int $userId): array
  {
    $stmt = $this->db->prepare(
      'SELECT a.name, a.description, a.icon, a.points, ua.earned_at
       FROM user_achievements ua
       JOIN achievements a ON ua.achievement_id = a.id
       WHERE ua.user_id = ?
       ORDER BY ua.earned_at DESC'
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Simple achievement checking - only check basic milestones
   */
  public function checkAndAwardAchievements(int $userId, string $actionType): array
  {
    $newAchievements = [];

    switch ($actionType) {
      case 'watched':
        $newAchievements = $this->checkWatchedAchievements($userId);
        break;
      case 'rated':
        $newAchievements = $this->checkRatingAchievements($userId);
        break;
      case 'reviewed':
        $newAchievements = $this->checkReviewAchievements($userId);
        break;
    }

    // Award new achievements
    foreach ($newAchievements as $achievementId) {
      $this->awardAchievement($userId, $achievementId);
    }

    return $this->getAchievementDetails($newAchievements);
  }

  private function checkWatchedAchievements(int $userId): array
  {
    $achievements = [];
    $watchedCount = $this->getWatchedCount($userId);

    $milestones = [1 => 'First Steps', 10 => 'Movie Buff', 50 => 'Cinephile', 100 => 'Movie Master'];

    foreach ($milestones as $count => $name) {
      if ($watchedCount >= $count) {
        $achievementId = $this->getAchievementIdByName($name);
        if ($achievementId && !$this->hasAchievement($userId, $achievementId)) {
          $achievements[] = $achievementId;
        }
      }
    }

    return $achievements;
  }

  private function checkRatingAchievements(int $userId): array
  {
    $achievements = [];
    $ratingsCount = $this->getRatingsCount($userId);

    $milestones = [1 => 'Rating Starter', 25 => 'Rating Expert', 100 => 'Taste Maker'];

    foreach ($milestones as $count => $name) {
      if ($ratingsCount >= $count) {
        $achievementId = $this->getAchievementIdByName($name);
        if ($achievementId && !$this->hasAchievement($userId, $achievementId)) {
          $achievements[] = $achievementId;
        }
      }
    }

    return $achievements;
  }

  private function checkReviewAchievements(int $userId): array
  {
    $achievements = [];
    $reviewCount = $this->getReviewCount($userId);

    $milestones = [1 => 'Review Rookie', 10 => 'Critic', 25 => 'Expert Reviewer'];

    foreach ($milestones as $count => $name) {
      if ($reviewCount >= $count) {
        $achievementId = $this->getAchievementIdByName($name);
        if ($achievementId && !$this->hasAchievement($userId, $achievementId)) {
          $achievements[] = $achievementId;
        }
      }
    }

    return $achievements;
  }

  // Helper methods
  private function awardAchievement(int $userId, int $achievementId): bool
  {
    $stmt = $this->db->prepare("INSERT IGNORE INTO user_achievements (user_id, achievement_id) VALUES (?, ?)");
    return $stmt->execute([$userId, $achievementId]);
  }

  private function getWatchedCount(int $userId): int
  {
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM movie_status WHERE user_id = ? AND status = 'watched'");
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
  }

  private function getRatingsCount(int $userId): int
  {
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM movie_ratings WHERE user_id = ?");
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
  }

  private function getReviewCount(int $userId): int
  {
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM movie_reviews WHERE user_id = ?");
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
  }

  private function hasAchievement(int $userId, int $achievementId): bool
  {
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM user_achievements WHERE user_id = ? AND achievement_id = ?");
    $stmt->execute([$userId, $achievementId]);
    return $stmt->fetchColumn() > 0;
  }

  private function getAchievementIdByName(string $name): ?int
  {
    $stmt = $this->db->prepare("SELECT id FROM achievements WHERE name = ?");
    $stmt->execute([$name]);
    $result = $stmt->fetchColumn();
    return $result ? (int)$result : null;
  }

  private function getAchievementDetails(array $achievementIds): array
  {
    if (empty($achievementIds)) return [];

    $placeholders = str_repeat('?,', count($achievementIds) - 1) . '?';
    $stmt = $this->db->prepare("SELECT id, name, description, points, icon FROM achievements WHERE id IN ($placeholders)");
    $stmt->execute($achievementIds);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
