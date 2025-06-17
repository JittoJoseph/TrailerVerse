# **TrailerVerse - Project Requirements Document (v2.0)**

## **Project Overview**

**Project Name:** TrailerVerse \
 **Technology Stack:** PHP, MySQL, Apache (XAMPP), Tailwind CSS \
 **External APIs:** The Movie Database (TMDB) API \
 **Project Type:** BCA 5th Semester Mini Project

**Description: \
** TrailerVerse has evolved from a simple movie browsing tool into a **socially immersive movie platform**, inspired by modern user behaviors such as sharing watch history and opinions online. The platform combines Netflix-style movie discovery with social features, gamified achievements, and personalized recommendations to create an engaging community-driven movie experience.

## **🚀 Core Features**

### **1. Landing Page (Enhanced Netflix-style Interface)**

- **Hero Section \
  **
  _ Featured movie with cinematic background image
  _ Play trailer button overlay
  _ Movie title, brief description, and community rating
  _ Quick action buttons (Add to Watchlist, Mark as Watched)
- **Personalized Movie Sections \
  **
  _ "Trending Now" (TMDB trending/movie/week endpoint)
  _ "Popular Among Friends" (based on followed users' activities)
  _ "Because You Liked..." (personalized recommendations)
  _ "Top Rated Movies" (TMDB movie/top_rated endpoint)
  _ Genre-based sections with user preference weighting
  _ Horizontal scrolling cards with enhanced hover effects \* Status indicators (watched/want to watch) on movie cards

### **2. Enhanced Movie Detail Page**

- **Top Section \
  **
  _ Embedded YouTube trailer player
  _ Movie poster and backdrop display
  _ Comprehensive metadata (title, rating, year, runtime, genres, director)
  _ **Watch Status Actions**: Mark as "Want to Watch" or "Watched" \* **Rating System**: 1-10 scale rating (only for watched movies)
- **Content Sections \
  **
  _ Plot synopsis with expandable text
  _ Cast carousel with actor photos and names
  _ Similar movies recommendations (TMDB + user preference based)
  _ Community reviews from platform users
  _ TMDB user reviews integration
  _ **User Review Section**: Write and display personal reviews
- **Social Elements \
  **
  _ Friends' ratings and reviews prominently displayed
  _ "X friends have watched this movie" indicator \* Activity timeline showing when friends interacted with the movie

### **3. Advanced User Authentication & Profiles**

- **Authentication System \
  **
  _ User registration with email validation
  _ Secure login/logout with session management \* Password encryption and security measures
- **Enhanced User Profiles \
  **
  _ Profile picture upload and management
  _ Customizable bio section
  _ Public/Private profile visibility settings
  _ **User Statistics Dashboard**
  _ Movies watched counter
  _ Average rating given
  _ Reviews written count
  _ Favorite genre analysis
  _ Total watch time tracking
  _ Achievement points and badges display

### **4. Watch Status & Tracking System**

- **Status Management \
  **
  _ "Want to Watch" list (replaces simple watchlist)
  _ "Watched" movies with date tracking
  _ Visual status indicators across the platform
  _ Bulk status updates and management tools
- **Progress Tracking \
  **
  _ Watch history with timestamps
  _ Genre preference analysis
  _ Monthly/yearly watching statistics
  _ Personal movie journal functionality

### **5. Rating & Review System**

- **Rating Features \
  **
  _ 1-10 scale rating system (decimal precision)
  _ Only allow ratings for "Watched" movies
  _ Edit/update rating functionality
  _ Personal rating history and trends
- **Review System \
  **
  _ Rich text review composition
  _ One review per movie per user
  _ Edit and update review capabilities
  _ Review visibility based on profile settings \* Community review browsing and interaction

### **6. Social Features & Following System**

- **User Connections \
  **
  _ Follow/unfollow other users
  _ Follower and following lists
  _ User discovery through movie interactions
  _ Privacy controls for social features
- **Activity Feed \
  **
  _ Personalized feed showing friends' movie activities
  _ Activity types: watched movie, rated movie, reviewed movie, achieved badge
  _ Real-time activity generation and display
  _ Filter options for activity types
- **Social Movie Discovery \
  **
  _ "Popular Among Friends" movie sections
  _ Friend recommendation engine \* Shared movie lists and collections

### **7. Gamification & Achievement System**

- **Achievement Categories \
  **
  _ **Movies Watched**: Milestones for total movies watched
  _ **Reviews Written**: Recognition for active reviewers
  _ **Ratings Given**: Engagement-based achievements
  _ **High Ratings**: Quality taste recognition
  _ **Genre Diversity**: Encouraging varied movie consumption
  _ **Classic Movies**: Historical cinema appreciation \* **New Releases**: Current movie engagement
- **Achievement Features \
  **
  _ Visual badge system with custom icons
  _ Point-based achievement scoring
  _ Progress tracking toward next achievements
  _ Achievement showcase on user profiles \* Notification system for earned achievements

### **8. Enhanced Search & Discovery**

- **Advanced Search \
  **
  _ Real-time movie search using TMDB API
  _ Filter by genre, year, rating, watch status
  _ Social filters (friends' ratings, popular among network)
  _ Search history and saved searches
- **Personalized Recommendations \
  **
  _ Machine learning-inspired recommendation engine
  _ "Because you rated X highly" suggestions
  _ Genre preference-based recommendations
  _ Friend activity-influenced suggestions

## **📐 Technical Specifications**

### **Enhanced Database Schema (3NF Compliant)**

-- ============================================

-- TrailerVerse Database Schema (3NF Compliant)

-- ============================================

-- 1. USERS TABLE

CREATE TABLE users (

    id INT PRIMARY KEY AUTO_INCREMENT,

    username VARCHAR(20) UNIQUE NOT NULL,

    email VARCHAR(50) UNIQUE NOT NULL,

    password VARCHAR(255) NOT NULL,

    first_name VARCHAR(15),

    last_name VARCHAR(20),

    profile_picture VARCHAR(255),

    is_public BOOLEAN DEFAULT TRUE,

    bio TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

);

-- 2. USER SESSIONS TABLE

CREATE TABLE user_sessions (

    id INT PRIMARY KEY AUTO_INCREMENT,

    user_id INT NOT NULL,

    session_token VARCHAR(255) NOT NULL UNIQUE,

    expires_at TIMESTAMP NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE

);

-- 3. USER FOLLOWS TABLE

CREATE TABLE user_follows (

    id INT PRIMARY KEY AUTO_INCREMENT,

    follower_id INT NOT NULL,

    following_id INT NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,

    FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE,

    UNIQUE KEY unique_follow (follower_id, following_id),

    CONSTRAINT chk_no_self_follow CHECK (follower_id != following_id)

);

-- 4. MOVIE CACHE TABLE

CREATE TABLE movie_cache (

    movie_id INT PRIMARY KEY,

    title VARCHAR(30) NOT NULL,

    overview TEXT,

    poster_path VARCHAR(255),

    backdrop_path VARCHAR(255),

    release_date DATE,

    runtime INT,

    vote_average DECIMAL(3,1),

    vote_count INT,

    genre_ids JSON,

    director VARCHAR(60),

    cast_info JSON,

    trailer_key VARCHAR(50),

    cached_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);

-- 5. MOVIE STATUS TABLE

CREATE TABLE movie_status (

    id INT PRIMARY KEY AUTO_INCREMENT,

    user_id INT NOT NULL,

    movie_id INT NOT NULL,

    status ENUM('want_to_watch', 'watched') NOT NULL,

    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    date_watched TIMESTAMP NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    UNIQUE KEY unique_user_movie (user_id, movie_id)

);

-- 6. MOVIE RATINGS TABLE

CREATE TABLE movie_ratings (

    id INT PRIMARY KEY AUTO_INCREMENT,

    user_id INT NOT NULL,

    movie_id INT NOT NULL,

    rating DECIMAL(2,1) NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    UNIQUE KEY unique_user_movie_rating (user_id, movie_id),

    CONSTRAINT chk_rating_range CHECK (rating >= 1.0 AND rating &lt;= 10.0)

);

-- 7. MOVIE REVIEWS TABLE

CREATE TABLE movie_reviews (

    id INT PRIMARY KEY AUTO_INCREMENT,

    user_id INT NOT NULL,

    movie_id INT NOT NULL,

    review_text TEXT NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    UNIQUE KEY unique_user_movie_review (user_id, movie_id)

);

-- 8. ACHIEVEMENTS TABLE

CREATE TABLE achievements (

    id INT PRIMARY KEY AUTO_INCREMENT,

    name VARCHAR(50) NOT NULL UNIQUE,

    description TEXT NOT NULL,

    icon VARCHAR(255),

    achievement_type ENUM('movies_watched', 'reviews_written', 'ratings_given', 'watchlist_size', 'high_ratings', 'genre_diversity', 'classic_movies', 'new_releases') NOT NULL,

    criteria_value INT NOT NULL,

    points INT DEFAULT 10,

    is_active BOOLEAN DEFAULT TRUE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);

-- 9. USER ACHIEVEMENTS TABLE

CREATE TABLE user_achievements (

    id INT PRIMARY KEY AUTO_INCREMENT,

    user_id INT NOT NULL,

    achievement_id INT NOT NULL,

    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE,

    UNIQUE KEY unique_user_achievement (user_id, achievement_id)

);

-- 10. USER ACTIVITIES TABLE

CREATE TABLE user_activities (

    id INT PRIMARY KEY AUTO_INCREMENT,

    user_id INT NOT NULL,

    activity_type ENUM('watched_movie', 'added_to_watchlist', 'rated_movie', 'reviewed_movie', 'achieved_badge') NOT NULL,

    movie_id INT NULL,

    achievement_id INT NULL,

    metadata JSON,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE SET NULL

);

-- 11. GENRE MASTER TABLE

CREATE TABLE genres (

    id INT PRIMARY KEY,

    name VARCHAR(20) NOT NULL UNIQUE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);

-- 12. USER STATISTICS TABLE

CREATE TABLE user_statistics (

    id INT PRIMARY KEY AUTO_INCREMENT,

    user_id INT NOT NULL,

    movies_watched INT DEFAULT 0,

    movies_in_watchlist INT DEFAULT 0,

    reviews_written INT DEFAULT 0,

    ratings_given INT DEFAULT 0,

    average_rating DECIMAL(3,2) DEFAULT 0.00,

    favorite_genre_id INT,

    total_watch_time_minutes INT DEFAULT 0,

    achievement_points INT DEFAULT 0,

    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    FOREIGN KEY (favorite_genre_id) REFERENCES genres(id) ON DELETE SET NULL,

    UNIQUE KEY unique_user_stats (user_id)

);

-- ============================================

-- VIEWS FOR COMMON QUERIES

-- ============================================

CREATE VIEW user_feed_view AS

SELECT

    ua.id,

    ua.user_id,

    u.username,

    u.profile_picture,

    u.is_public,

    ua.activity_type,

    ua.movie_id,

    mc.title as movie_title,

    mc.poster_path,

    ua.achievement_id,

    a.name as achievement_name,

    a.icon as achievement_icon,

    ua.metadata,

    ua.created_at

FROM user_activities ua

JOIN users u ON ua.user_id = u.id

LEFT JOIN movie_cache mc ON ua.movie_id = mc.movie_id

LEFT JOIN achievements a ON ua.achievement_id = a.id

WHERE u.is_public = TRUE

ORDER BY ua.created_at DESC;

CREATE VIEW user_stats_view AS

SELECT

    us.*,

    g.name as favorite_genre_name

FROM user_statistics us

LEFT JOIN genres g ON us.favorite_genre_id = g.id;

### **Enhanced TMDB API Integration**

**Core Endpoints:**

- `/movie/popular` - Popular movies for homepage
- `/movie/top_rated` - Top rated movies section
- `/trending/movie/week` - Trending movies
- `/discover/movie?with_genres={id}` - Genre-based movie lists
- `/movie/{id}` - Individual movie details
- `/movie/{id}/videos` - Movie trailers and videos
- `/movie/{id}/credits` - Cast and crew information
- `/movie/{id}/similar` - Similar movie recommendations
- `/movie/{id}/reviews` - External user reviews
- `/search/movie` - Movie search functionality
- `/genre/movie/list` - Available movie genres

**Advanced Integration Features:**

- Intelligent caching strategy with 24-48 hour expiration
- Batch API requests for performance optimization
- Error handling and fallback mechanisms
- Rate limiting compliance and monitoring

### **Enhanced Backend Architecture**

**Expanded MVC Structure:**

/trailerverse

├── /config

│ ├── database.php

│ ├── tmdb_config.php

│ └── app_config.php

├── /controllers

│ ├── AuthController.php

│ ├── MovieController.php

│ ├── UserController.php

│ ├── SocialController.php

│ ├── AchievementController.php

│ └── ActivityController.php

├── /models

│ ├── User.php

│ ├── Movie.php

│ ├── MovieStatus.php

│ ├── Rating.php

│ ├── Review.php

│ ├── Achievement.php

│ ├── Activity.php

│ └── Statistics.php

├── /views

│ ├── /layouts

│ ├── home.php

│ ├── movie-detail.php

│ ├── profile.php

│ ├── activity-feed.php

│ ├── achievements.php

│ └── search.php

├── /services

│ ├── TMDBService.php

│ ├── RecommendationEngine.php

│ ├── AchievementService.php

│ └── NotificationService.php

├── /assets

│ ├── /css

│ ├── /js

│ ├── /images

│ └── /achievement-icons

├── /includes

│ ├── header.php

│ ├── footer.php

│ ├── navigation.php

│ └── functions.php

└── index.php

## **🎨 Enhanced UI/UX Features**

### **Modern Design Elements**

- Dark theme with Netflix-inspired color palette
- Smooth micro-animations and transitions
- Card-based layouts with enhanced hover effects
- Modal windows for trailers and detailed views
- Toast notifications for user actions
- Loading skeletons for better perceived performance

### **Social UI Components**

- Activity feed with timeline design
- User profile cards and avatars
- Achievement badge displays
- Rating visualizations (stars, progress bars)
- Social action buttons (follow, rate, review)
- Friend activity indicators

### **Responsive Design**

- Mobile-first approach with touch-friendly interfaces
- Adaptive layouts for tablets and desktops
- Swipe gestures for mobile movie carousels
- Optimized image loading and sizing
- Progressive Web App (PWA) considerations

## **🔒 Security & Privacy**

### **Enhanced Security Measures**

- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- CSRF token implementation
- Secure session management
- Password hashing with modern algorithms
- Rate limiting for API endpoints

### **Privacy Controls**

- Granular privacy settings for user profiles
- Activity visibility controls
- Data anonymization options
- GDPR compliance considerations
- User data export/deletion capabilities

## **🚀 Development Phases**

### **Phase 1: Enhanced Foundation (2 weeks)**

- XAMPP setup with optimized configuration
- Complete database schema implementation
- TMDB API integration and testing
- Enhanced project architecture setup
- Basic authentication system

### **Phase 2: Core Movie Features (3 weeks)**

- Movie browsing and detail pages
- Watch status system implementation
- Rating and review functionality
- Search and filtering capabilities
- Recommendation engine basics

### **Phase 3: Social Features (3 weeks)**

- User profiles and customization
- Following system implementation
- Activity feed development
- Social movie discovery features
- Privacy and visibility controls

### **Phase 4: Gamification & Polish (2 weeks)**

- Achievement system implementation
- User statistics and dashboards
- Advanced recommendation tuning
- UI/UX refinements and animations
- Performance optimization

### **Phase 5: Testing & Deployment (1 week)**

- Comprehensive testing
- Performance testing and optimization
- Documentation completion
- Production deployment
