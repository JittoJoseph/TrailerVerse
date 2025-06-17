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
    title VARCHAR(255) NOT NULL,
    overview TEXT,
    poster_path VARCHAR(255),
    backdrop_path VARCHAR(255),
    release_date DATE,
    runtime INT,
    vote_average DECIMAL(3,1),
    vote_count INT,
    genre_ids JSON,
    director VARCHAR(100),
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
    CONSTRAINT chk_rating_range CHECK (rating >= 1.0 AND rating <= 10.0)
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
    name VARCHAR(50) NOT NULL UNIQUE,
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

-- ============================================
-- INITIAL DATA INSERTS
-- ============================================

-- Insert popular genres
INSERT INTO genres (id, name) VALUES
(28, 'Action'),
(12, 'Adventure'),
(16, 'Animation'),
(35, 'Comedy'),
(80, 'Crime'),
(99, 'Documentary'),
(18, 'Drama'),
(10751, 'Family'),
(14, 'Fantasy'),
(36, 'History'),
(27, 'Horror'),
(10402, 'Music'),
(9648, 'Mystery'),
(10749, 'Romance'),
(878, 'Science Fiction'),
(10770, 'TV Movie'),
(53, 'Thriller'),
(10752, 'War'),
(37, 'Western');

-- Insert achievement templates
INSERT INTO achievements (name, description, icon, achievement_type, criteria_value, points) VALUES
('First Steps', 'Watch your first movie', 'first-movie.png', 'movies_watched', 1, 10),
('Movie Buff', 'Watch 10 movies', 'movie-buff.png', 'movies_watched', 10, 25),
('Cinephile', 'Watch 50 movies', 'cinephile.png', 'movies_watched', 50, 100),
('Movie Master', 'Watch 100 movies', 'movie-master.png', 'movies_watched', 100, 200),
('Review Rookie', 'Write your first review', 'first-review.png', 'reviews_written', 1, 15),
('Critic', 'Write 10 reviews', 'critic.png', 'reviews_written', 10, 50),
('Expert Reviewer', 'Write 25 reviews', 'expert-reviewer.png', 'reviews_written', 25, 100),
('Rating Starter', 'Rate your first movie', 'first-rating.png', 'ratings_given', 1, 5),
('Rating Expert', 'Rate 25 movies', 'rating-expert.png', 'ratings_given', 25, 50),
('Taste Maker', 'Rate 100 movies', 'taste-maker.png', 'ratings_given', 100, 150),
('Selective Viewer', 'Maintain high average rating (8.5+)', 'selective.png', 'high_ratings', 85, 75),
('Quality Seeker', 'Maintain very high average rating (9.0+)', 'quality.png', 'high_ratings', 90, 150),
('Genre Explorer', 'Watch movies from 5 different genres', 'explorer.png', 'genre_diversity', 5, 40),
('Genre Master', 'Watch movies from 10 different genres', 'genre-master.png', 'genre_diversity', 10, 80),
('Classic Connoisseur', 'Watch 10 movies from before 1990', 'classic.png', 'classic_movies', 10, 60),
('Vintage Enthusiast', 'Watch 25 classic movies', 'vintage.png', 'classic_movies', 25, 120),
('Trend Follower', 'Watch 10 movies from current year', 'trending.png', 'new_releases', 10, 30),
('Early Adopter', 'Watch 25 new releases', 'early-adopter.png', 'new_releases', 25, 60);
