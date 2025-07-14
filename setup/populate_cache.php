<?php
require_once '../config/app.php';
require_once '../services/MovieService.php';

echo "Populating movie cache...\n";

$movieService = new MovieService();
$result = $movieService->getTrendingMovies();

if ($result && $result['results']) {
  echo "✅ Successfully cached " . count($result['results']) . " movies\n";
  echo "Cache populated with trending movies!\n";
} else {
  echo "❌ Failed to populate cache. Check your API connection.\n";
}
