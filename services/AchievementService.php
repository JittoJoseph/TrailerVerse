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

    $sql = "SELECT name, description, points FROM achievements ORDER BY id ASC LIMIT 6";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
