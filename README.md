# 🎬 TrailerVerse

A modern, social movie discovery platform built with PHP, MySQL, and TMDB API. Discover trending movies, build your watchlist, connect with fellow movie enthusiasts, and track your cinematic achievements.

![TrailerVerse](https://img.shields.io/badge/Status-Active-brightgreen) ![PHP](https://img.shields.io/badge/PHP-8.0+-blue) ![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange) ![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-38B2AC)

## ✨ Features

### 🎯 Core Features

- **Movie Discovery**: Browse trending movies, search by title, filter by genre, year, and rating
- **User Authentication**: Secure signup/signin with session management
- **Personal Profiles**: Customizable user profiles with bio, profile pictures, and privacy settings
- **Watchlist Management**: Add/remove movies to your personal watchlist
- **Movie Reviews**: Rate and review movies you've watched
- **Social Feed**: Follow other users and see their activity in your personalized feed

### 🏆 Achievement System

- **Gamification**: Unlock achievements for watching movies, leaving reviews, and social interactions
- **Progress Tracking**: Visual progress bars and achievement badges
- **Community Stats**: View community-wide statistics and leaderboards

### 🎨 Modern UI/UX

- **Glassmorphism Design**: Beautiful glass-like UI elements with backdrop blur effects
- **Responsive Design**: Fully responsive across desktop, tablet, and mobile devices
- **Dark Theme**: Sleek dark theme optimized for movie browsing
- **Smooth Animations**: CSS transitions and hover effects for enhanced interactivity

### 🔧 Technical Features

- **TMDB Integration**: Real-time movie data from The Movie Database API
- **Database Caching**: Intelligent caching system for improved performance
- **RESTful Architecture**: Clean, maintainable PHP codebase
- **Security**: Input validation, prepared statements, and secure session handling

## 🚀 Quick Start

### Prerequisites

- **XAMPP/WAMP** or similar PHP development environment
- **PHP 8.0+** with MySQL extension
- **MySQL 8.0+**
- **Composer** (optional, for dependency management)

### Installation

1. **Clone the Repository**

   ```bash
   git clone https://github.com/yourusername/TrailerVerse.git
   cd TrailerVerse
   ```

2. **Database Setup**

   ```bash
   # Copy database configuration
   cp config/database.php.example config/database.php

   # Import database schema
   mysql -u root -p < setup/database_setup.sql

   # Populate with sample data (optional)
   php setup/populate_cache.php
   ```

3. **Configure Environment**

   - Update `config/database.php` with your MySQL credentials
   - Ensure TMDB API key is configured in `config/tmdb_config.php`
   - Set correct `APP_URL` in `config/app.php`

4. **Start Development Server**

   ```bash
   # Using XAMPP: Place in htdocs and start Apache/MySQL
   # Or use PHP built-in server
   php -S localhost:8000
   ```

5. **Access Application**
   ```
   http://localhost/TrailerVerse
   ```

## 📁 Project Structure

```
TrailerVerse/
├── 📁 api/                 # API endpoints (future expansion)
├── 📁 assets/              # Static assets (CSS, JS, images)
├── 📁 auth/                # Authentication pages
│   ├── signin.php         # User login
│   ├── signup.php         # User registration
│   └── logout.php         # Session termination
├── 📁 config/              # Configuration files
│   ├── app.php            # Application settings
│   ├── database.php       # Database connection
│   └── tmdb_config.php    # TMDB API configuration
├── 📁 includes/            # Reusable components
│   ├── head.php           # HTML head section
│   ├── header.php         # Navigation header
│   └── footer.php         # Site footer
├── 📁 services/            # Business logic layer
│   ├── MovieService.php   # Movie data management
│   ├── UserService.php    # User management
│   ├── GenreService.php   # Genre handling
│   ├── MovieReviewService.php # Review system
│   ├── MovieStatusService.php # Watch status tracking
│   └── AchievementService.php # Achievement system
├── 📁 setup/               # Database setup files
│   ├── database_setup.sql # Database schema
│   └── populate_cache.php # Sample data population
├── 📄 index.php            # Homepage/Discover page
├── 📄 explore.php          # Movie exploration with filters
├── 📄 genres.php           # Genre browsing
├── 📄 movie.php            # Individual movie details
├── 📄 profile.php          # User profiles
├── 📄 feed.php             # Social activity feed
├── 📄 social.php           # Social interaction API
└── 📄 README.md            # Project documentation
```

## 🛠️ Technology Stack

### Backend

- **PHP 8.0+**: Server-side scripting and business logic
- **MySQL 8.0+**: Relational database for data persistence
- **PDO**: Secure database abstraction layer

### Frontend

- **HTML5**: Semantic markup structure
- **Tailwind CSS**: Utility-first CSS framework
- **JavaScript (ES6+)**: Interactive functionality
- **Font Awesome**: Icon library

### APIs & Services

- **TMDB API**: Movie database and metadata
- **Session Management**: PHP native sessions
- **File Upload**: Profile picture handling

### Development Tools

- **XAMPP**: Local development environment
- **Git**: Version control
- **Composer**: PHP dependency management

## 🎯 Key Components

### Movie Discovery System

- **Trending Movies**: Real-time trending data from TMDB
- **Advanced Search**: Multi-criteria movie search and filtering
- **Genre Browsing**: Organized movie discovery by categories
- **Movie Details**: Comprehensive movie information pages

### Social Features

- **User Profiles**: Personalized user pages with stats and activity
- **Following System**: Connect with other movie enthusiasts
- **Activity Feed**: Chronological activity stream of followed users
- **Review System**: User-generated movie reviews and ratings

### Achievement System

- **Progress Tracking**: Visual achievement progress indicators
- **Badge System**: Unlockable achievement badges
- **Gamification**: Points and rewards for engagement

## 🔒 Security Features

- **Input Validation**: Comprehensive server-side validation
- **SQL Injection Prevention**: Prepared statements and parameterized queries
- **XSS Protection**: Output sanitization and escaping
- **Session Security**: Secure session handling and timeout management
- **Password Hashing**: bcrypt password encryption
- **CSRF Protection**: Token-based request validation

## 📊 Database Schema

The application uses a normalized 3NF database design with the following key tables:

- `users` - User accounts and profiles
- `user_sessions` - Session management
- `user_follows` - Social following relationships
- `movie_cache` - Cached movie data from TMDB
- `movie_reviews` - User movie reviews and ratings
- `movie_status` - Watch status tracking (watchlist, watched, etc.)
- `achievements` - Achievement definitions
- `user_achievements` - User achievement unlocks
