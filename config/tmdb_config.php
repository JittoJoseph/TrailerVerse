<?php
// TMDB API Configuration
define('TMDB_API_KEY', '5328e32cdcc8b2ddc3fc5f6563991499');
define('TMDB_BASE_URL', 'https://api.themoviedb.org/3');
define('TMDB_IMAGE_BASE_URL', 'https://image.tmdb.org/t/p/w300');
define('TMDB_BACKDROP_BASE_URL', 'https://image.tmdb.org/t/p/w1280');
define('TMDB_POSTER_BASE_URL', 'https://image.tmdb.org/t/p/w500');

// Helper function to get full image URL
function getTMDBImageUrl($path, $size = 'w300')
{
  if (!$path) return '/assets/images/placeholder.jpg';
  return "https://image.tmdb.org/t/p/{$size}{$path}";
}

// Helper function to get backdrop URL
function getTMDBBackdropUrl($path)
{
  return getTMDBImageUrl($path, 'w1280');
}

// Helper function to get poster URL
function getTMDBPosterUrl($path)
{
  return getTMDBImageUrl($path, 'w500');
}
