<?php
require_once __DIR__ . '/../config/tmdb_config.php';

class MovieService
{

  private function makeRequest($url)
  {
    // Create context with SSL options to fix the connection error
    $context = stream_context_create([
      'http' => [
        'timeout' => 10,
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
      ],
      'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false
      ]
    ]);

    $response = @file_get_contents($url, false, $context);

    if ($response === false) {
      return null;
    }

    return json_decode($response, true);
  }

  public function getPopularMovies()
  {
    $url = TMDB_BASE_URL . '/movie/popular?api_key=' . TMDB_API_KEY;
    return $this->makeRequest($url);
  }

  public function getTrendingMovies()
  {
    $url = TMDB_BASE_URL . '/trending/movie/week?api_key=' . TMDB_API_KEY;
    return $this->makeRequest($url);
  }

  public function getTopRatedMovies()
  {
    $url = TMDB_BASE_URL . '/movie/top_rated?api_key=' . TMDB_API_KEY;
    return $this->makeRequest($url);
  }
}
