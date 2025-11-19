<?php
require_once __DIR__ . '/../config/database.php';

class StatsService
{
  private $db;

  public function __construct()
  {
    $database = new Database();
    $this->db = $database->connect();
  }

  public function getTotalMoviesTracked()
  {
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM movie_cache");
    $stmt->execute();
    return $stmt->fetchColumn();
  }

  public function getTotalUsers()
  {
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM users");
    $stmt->execute();
    return $stmt->fetchColumn();
  }

  public function getTotalReviews()
  {
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM movie_reviews");
    $stmt->execute();
    return $stmt->fetchColumn();
  }

  public function getTotalMoviesWatched()
  {
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM movie_status WHERE status = 'watched'");
    $stmt->execute();
    return $stmt->fetchColumn();
  }
}
?>