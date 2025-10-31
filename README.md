# TrailerVerse

A complete, modern movie discovery platform built with PHP, MySQL, and TMDB API. Discover trending movies, build watchlists, connect with fellow movie enthusiasts, and track cinematic achievements.

![Status](https://img.shields.io/badge/Status-Complete-brightgreen) ![PHP](https://img.shields.io/badge/PHP-8.0+-blue) ![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange) ![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-38B2AC)

## Live Demo

**[View TrailerVerse Live](https://trailerverse.infinityfreeapp.com/?i=1)**

## Features

- **Movie Discovery**: Browse trending movies, search, and filter by genre/year/rating
- **User Authentication**: Secure signup/signin with session management
- **Watchlist & Reviews**: Personal watchlists, ratings, and movie reviews
- **Social Features**: Follow users, activity feeds, and community interaction
- **Achievement System**: Gamified progress tracking with badges and points
- **Modern UI**: Glassmorphism design with responsive dark theme

## Quick Start

### Prerequisites

- XAMPP/WAMP or PHP 8.0+ with MySQL extension
- MySQL 8.0+

### Installation

1. **Clone & Setup**

   ```bash
   git clone https://github.com/JittoJoseph/TrailerVerse.git
   cd TrailerVerse
   ```

2. **Database Setup**

   ```bash
   # Copy config and edit database credentials
   cp config/database.php.example config/database.php

   # Import schema
   mysql -u root -p < setup/database_setup.sql

   # Populate sample movie data
   php setup/populate_cache.php
   ```

3. **Configure API**

   - Add your TMDB API key to `config/tmdb_config.php`
   - Update `APP_URL` in `config/app.php` if needed

4. **Run**

   ```bash
   # Using XAMPP: Place in htdocs, start Apache/MySQL
   # Or use PHP built-in server
   php -S localhost:8000
   ```

   Visit: `http://localhost/TrailerVerse` (or `http://localhost:8000`)

## Project Structure

```
TrailerVerse/
├── auth/                 # Authentication (signin.php, signup.php, logout.php)
├── config/               # Configuration (app.php, database.php, tmdb_config.php)
├── includes/             # Reusable components (head.php, header.php, footer.php)
├── services/             # Business logic layer
│   ├── MovieService.php
│   ├── UserService.php
│   ├── GenreService.php
│   ├── MovieReviewService.php
│   ├── MovieStatusService.php
│   └── AchievementService.php
├── setup/                # Database setup (database_setup.sql, populate_cache.php)
├── index.php             # Homepage/Discover
├── explore.php           # Movie search & filters
├── movie.php             # Movie details
├── profile.php           # User profiles
├── feed.php              # Social activity feed
└── genres.php            # Genre browsing
```

## Tech Stack

**Backend**: PHP 8.0+ • MySQL 8.0+ • PDO  
**Frontend**: HTML5 • Tailwind CSS • JavaScript • Font Awesome  
**APIs**: TMDB API

## Security

- Input validation & sanitization
- Prepared statements (PDO)
- Password hashing (bcrypt)
- XSS protection
- Session security

## Database Schema

Normalized 3NF design with tables for users, movies, reviews, achievements, and social features. Uses JSON columns for flexible genre storage and intelligent caching for TMDB data.

## Key Components

- **Movie Discovery**: TMDB integration with smart caching
- **Social System**: User following, activity feeds, reviews
- **Achievement Engine**: Progress tracking and gamification
- **Responsive UI**: Mobile-first design with glassmorphism effects

---

**Ready to explore movies?** Clone the repo and start your cinematic journey!
