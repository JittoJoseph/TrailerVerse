# TrailerVerse - Social Movie Discovery Platform

## Overview

TrailerVerse is a comprehensive social movie discovery platform built with PHP and MySQL. It integrates with The Movie Database (TMDB) API to provide users with an extensive collection of movies, trailers, and social features. The platform allows users to discover movies, rate them, write reviews, maintain watchlists, track watched movies, earn achievements, and follow other users to see their movie activities.

## Architecture

### Technology Stack

- **Backend**: PHP 7.4+ with PDO for database interactions
- **Database**: MySQL/MariaDB with 3NF normalized schema
- **Frontend**: HTML5, Tailwind CSS, JavaScript (ES6+)
- **External APIs**: TMDB API for movie data
- **Caching**: Local database caching of TMDB responses
- **Session Management**: PHP sessions for user authentication

### Project Structure

```
TrailerVerse/
├── config/           # Configuration files
│   ├── app.php       # App constants and session helpers
│   ├── database.php  # Database connection class
│   └── tmdb_config.php # TMDB API configuration
├── services/         # Business logic layer
│   ├── MovieService.php      # Movie data and TMDB integration
│   ├── UserService.php       # User management and social features
│   ├── MovieStatusService.php # Watchlist/watched/rating management
│   ├── MovieReviewService.php # Review functionality
│   ├── AchievementService.php # Achievement system
│   └── GenreService.php      # Genre management
├── includes/         # Reusable HTML components
│   ├── head.php      # HTML head with meta tags and styles
│   ├── header.php    # Main navigation header
│   ├── header_detail.php # Movie detail page header
│   └── footer.php    # Site footer
├── auth/             # Authentication pages
│   ├── signin.php    # Login page
│   ├── signup.php    # Registration page
│   └── logout.php    # Logout handler
├── setup/            # Database setup and utilities
│   ├── database_setup.sql    # Complete database schema
│   └── populate_cache.php    # Sample data population
└── *.php             # Main application pages
    ├── index.php     # Homepage with trending movies
    ├── movie.php     # Movie detail page
    ├── explore.php   # Search and discovery page
    ├── profile.php   # User profile page
    ├── feed.php      # Social activity feed
    ├── social.php    # AJAX handlers for social actions
    ├── genres.php    # Genre browsing page
    ├── genre-movies.php # Movies by genre page
    └── edit_profile.php # Profile editing page
```

## Database Schema

The database follows 3rd Normal Form (3NF) principles with the following key tables:

### Core Entities

- **`users`**: User accounts with profile information
- **`movie_cache`**: Cached movie data from TMDB API
- **`genres`**: Movie genre master data

### User-Movie Interactions

- **`movie_status`**: User's watchlist and watched status for movies
- **`movie_ratings`**: User ratings (1-10 scale)
- **`movie_reviews`**: User-written reviews

### Social Features

- **`user_follows`**: Follow relationships between users
- **`user_activities`**: Activity feed entries (watched, rated, reviewed movies)

### Gamification

- **`achievements`**: Achievement templates with criteria
- **`user_achievements`**: Earned achievements by users

### Key Design Decisions

- **JSON Storage**: Genre IDs stored as JSON arrays for flexible querying
- **Caching Strategy**: Movie data cached locally to reduce API calls
- **Activity Feed**: Unified feed using UNION queries with ranking
- **Achievement System**: Milestone-based with automatic awarding

### Table Structures

#### users

| Field Name | Datatype  | Length | Description                   |
| ---------- | --------- | ------ | ----------------------------- |
| id         | INT       | -      | Primary key, auto-increment   |
| username   | VARCHAR   | 20     | Unique username               |
| password   | VARCHAR   | 255    | Hashed password               |
| first_name | VARCHAR   | 15     | User's first name             |
| last_name  | VARCHAR   | 20     | User's last name              |
| is_public  | BOOLEAN   | -      | Profile visibility setting    |
| bio        | TEXT      | -      | User biography                |
| created_at | TIMESTAMP | -      | Account creation timestamp    |
| updated_at | TIMESTAMP | -      | Last profile update timestamp |

#### user_follows

| Field Name   | Datatype  | Length | Description                            |
| ------------ | --------- | ------ | -------------------------------------- |
| id           | INT       | -      | Primary key, auto-increment            |
| follower_id  | INT       | -      | ID of the user following               |
| following_id | INT       | -      | ID of the user being followed          |
| created_at   | TIMESTAMP | -      | Follow relationship creation timestamp |

#### movie_cache

| Field Name     | Datatype  | Length | Description                |
| -------------- | --------- | ------ | -------------------------- |
| movie_id       | INT       | -      | Primary key, TMDB movie ID |
| title          | VARCHAR   | 255    | Movie title                |
| overview       | TEXT      | -      | Movie description          |
| poster_path    | VARCHAR   | 255    | Path to poster image       |
| backdrop_path  | VARCHAR   | 255    | Path to backdrop image     |
| release_date   | DATE      | -      | Movie release date         |
| runtime        | INT       | -      | Movie runtime in minutes   |
| vote_average   | DECIMAL   | (3,1)  | Average TMDB rating        |
| vote_count     | INT       | -      | Number of TMDB votes       |
| genre_ids      | JSON      | -      | Array of genre IDs         |
| director       | VARCHAR   | 100    | Movie director name        |
| cast_info      | JSON      | -      | Cast information           |
| trailer_key    | VARCHAR   | 50     | YouTube trailer key        |
| similar_movies | JSON      | -      | Similar movies data        |
| trending_order | INT       | -      | Trending display order     |
| cached_at      | TIMESTAMP | -      | Cache timestamp            |

#### movie_status

| Field Name   | Datatype  | Length | Description                          |
| ------------ | --------- | ------ | ------------------------------------ |
| id           | INT       | -      | Primary key, auto-increment          |
| user_id      | INT       | -      | User ID                              |
| movie_id     | INT       | -      | Movie ID                             |
| status       | ENUM      | -      | Status: 'want_to_watch' or 'watched' |
| date_added   | TIMESTAMP | -      | Date added to watchlist              |
| date_watched | TIMESTAMP | -      | Date marked as watched               |

#### movie_ratings

| Field Name | Datatype  | Length | Description                 |
| ---------- | --------- | ------ | --------------------------- |
| id         | INT       | -      | Primary key, auto-increment |
| user_id    | INT       | -      | User ID                     |
| movie_id   | INT       | -      | Movie ID                    |
| rating     | DECIMAL   | (2,1)  | User rating (1.0-10.0)      |
| created_at | TIMESTAMP | -      | Rating creation timestamp   |
| updated_at | TIMESTAMP | -      | Rating update timestamp     |

#### movie_reviews

| Field Name  | Datatype  | Length | Description                 |
| ----------- | --------- | ------ | --------------------------- |
| id          | INT       | -      | Primary key, auto-increment |
| user_id     | INT       | -      | User ID                     |
| movie_id    | INT       | -      | Movie ID                    |
| review_text | TEXT      | -      | Review content              |
| created_at  | TIMESTAMP | -      | Review creation timestamp   |
| updated_at  | TIMESTAMP | -      | Review update timestamp     |

#### achievements

| Field Name       | Datatype  | Length | Description                                 |
| ---------------- | --------- | ------ | ------------------------------------------- |
| id               | INT       | -      | Primary key, auto-increment                 |
| name             | VARCHAR   | 50     | Achievement name                            |
| description      | TEXT      | -      | Achievement description                     |
| icon             | VARCHAR   | 255    | Achievement icon                            |
| achievement_type | ENUM      | -      | Type: movies_watched, reviews_written, etc. |
| criteria_value   | INT       | -      | Threshold value for achievement             |
| points           | INT       | -      | Points awarded                              |
| is_active        | BOOLEAN   | -      | Achievement active status                   |
| created_at       | TIMESTAMP | -      | Achievement creation timestamp              |

#### user_achievements

| Field Name     | Datatype  | Length | Description                 |
| -------------- | --------- | ------ | --------------------------- |
| id             | INT       | -      | Primary key, auto-increment |
| user_id        | INT       | -      | User ID                     |
| achievement_id | INT       | -      | Achievement ID              |
| earned_at      | TIMESTAMP | -      | Date achievement earned     |

#### user_activities

| Field Name     | Datatype  | Length | Description                                   |
| -------------- | --------- | ------ | --------------------------------------------- |
| id             | INT       | -      | Primary key, auto-increment                   |
| user_id        | INT       | -      | User ID                                       |
| activity_type  | ENUM      | -      | Type: watched_movie, added_to_watchlist, etc. |
| movie_id       | INT       | -      | Related movie ID (nullable)                   |
| achievement_id | INT       | -      | Related achievement ID (nullable)             |
| metadata       | JSON      | -      | Additional activity data                      |
| created_at     | TIMESTAMP | -      | Activity timestamp                            |

#### genres

| Field Name | Datatype  | Length | Description                |
| ---------- | --------- | ------ | -------------------------- |
| id         | INT       | -      | Primary key, TMDB genre ID |
| name       | VARCHAR   | 50     | Genre name                 |
| created_at | TIMESTAMP | -      | Genre creation timestamp   |

## Core Features

### 1. Movie Discovery

- **Trending Movies**: Weekly trending movies from TMDB
- **Search Functionality**: Full-text search with fuzzy matching
- **Genre Browsing**: Movies organized by genre categories
- **Advanced Filters**: Year, rating, popularity sorting
- **Pagination**: Efficient loading of large result sets

### 2. Movie Details & Interaction

- **Rich Movie Pages**: Posters, backdrops, trailers, cast info
- **Rating System**: 1-10 star rating with visual feedback
- **Review System**: User-generated reviews with timestamps
- **Watchlist Management**: Add/remove movies from personal lists
- **Watched Tracking**: Mark movies as watched with dates

### 3. Social Features

- **User Profiles**: Comprehensive profile pages with stats
- **Follow System**: Follow/unfollow other users
- **Activity Feed**: See movie activities from followed users
- **User Suggestions**: Discover new users to follow

### 4. Achievement System

- **Milestone Achievements**: Based on movies watched, rated, reviewed
- **Genre Diversity**: Rewards for exploring different genres
- **Quality Metrics**: High average ratings, classic movies
- **Social Recognition**: Points and badges for engagement

### 5. User Management

- **Secure Authentication**: Password hashing with bcrypt
- **Profile Customization**: Bio, public/private settings
- **Statistics Dashboard**: Movies watched, ratings given, reviews written
- **Privacy Controls**: Public/private profile visibility

## API Integration

### TMDB Integration

- **API Endpoints**: Movies, search, genres, videos, similar movies
- **Caching Layer**: Reduces API calls, improves performance
- **Fallback Handling**: Graceful degradation when API unavailable
- **Content Filtering**: Blocks adult content and specific movies

### Key Integration Points

```php
// Example: Fetch trending movies with caching
$url = TMDB_BASE_URL . '/trending/movie/week?api_key=' . TMDB_API_KEY;
$response = @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 3]]));
// Cache results in movie_cache table
$this->updateMovieCache($data['results']);
```

## Security Considerations

### Authentication & Authorization

- **Session-Based Auth**: Secure PHP session management
- **Password Security**: bcrypt hashing for passwords
- **Input Validation**: Prepared statements prevent SQL injection
- **CSRF Protection**: POST requests validated

### Data Protection

- **API Key Security**: TMDB keys stored in config files
- **User Data Privacy**: Public/private profile settings
- **Content Filtering**: Blocked movie IDs and adult content

## Performance Optimizations

### Caching Strategies

- **Movie Data Caching**: TMDB responses cached in database
- **Genre Caching**: Genre lists cached locally
- **Query Optimization**: Indexed database queries

### Frontend Optimizations

- **Lazy Loading**: Images loaded on demand
- **Responsive Design**: Mobile-first approach with Tailwind CSS
- **AJAX Interactions**: Seamless rating/review updates

### Database Optimizations

- **Prepared Statements**: Parameterized queries for security and performance
- **JSON Queries**: Efficient genre filtering with JSON_CONTAINS
- **Pagination**: Limited result sets for large datasets

## Development Workflow

### Local Setup

```bash
# Database setup
mysql -u root -p < setup/database_setup.sql
php setup/populate_cache.php

# Start development server
php -S localhost:8000
```

### Key Development Patterns

- **Service Layer**: Business logic separated from presentation
- **AJAX Handlers**: Asynchronous updates for better UX
- **Template Includes**: Reusable HTML components
- **Error Handling**: Graceful fallbacks for API failures

## Business Rules & Constraints

### Achievement Criteria

- **Automatic Awarding**: Achievements granted when criteria met
- **Milestone Tracking**: Progressive achievements (1, 10, 50, 100 movies)
- **Quality Metrics**: Average rating thresholds for quality badges

### Social Features

- **Public Profiles**: Only public users appear in feeds
- **Self-Follow Prevention**: Users cannot follow themselves
- **Activity Privacy**: Activities only from followed users

## Conclusion

TrailerVerse represents a comprehensive social movie platform that successfully combines movie discovery, social interaction, and gamification elements. The clean architecture, robust caching, and user-centric features create an engaging experience for movie enthusiasts. The codebase demonstrates good practices in PHP development, database design, and API integration, making it a solid foundation for further expansion and feature development.
