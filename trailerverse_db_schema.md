## 1. users

**Primary Key:** id

| No: | Fieldname  | Datatype (Size) | Key / Constraints | Description of the field  |
| --: | ---------- | --------------: | ----------------- | ------------------------- |
|  1. | id         |             INT | PRIMARY KEY       | User id                   |
|  2. | username   |     VARCHAR(20) | UNIQUE, NOT NULL  | Username / login handle   |
|  3. | password   |    VARCHAR(255) | NOT NULL          | Hashed password           |
|  4. | first_name |     VARCHAR(15) | NULL              | First name                |
|  5. | last_name  |     VARCHAR(20) | NULL              | Last name                 |
|  6. | is_public  |         BOOLEAN | DEFAULT TRUE      | Account visibility        |
|  7. | bio        |            TEXT | NULL              | Short user bio            |
|  8. | created_at |       TIMESTAMP | NOT NULL          | Record creation timestamp |
|  9. | updated_at |       TIMESTAMP | NOT NULL          | Last updated timestamp    |

## 2. user_follows

**Primary Key:** id

| No: | Fieldname    | Datatype (Size) | Key / Constraints | Description of the field |
| --: | ------------ | --------------: | ----------------- | ------------------------ |
|  1. | id           |             INT | PRIMARY KEY       | Follow record id         |
|  2. | follower_id  |             INT | FOREIGN KEY       | User who follows         |
|  3. | following_id |             INT | FOREIGN KEY       | User being followed      |
|  4. | created_at   |       TIMESTAMP | NOT NULL          | When follow happened     |

## 3. movie_cache

**Primary Key:** movie_id

| No: | Fieldname      | Datatype (Size) | Key / Constraints | Description of the field      |
| --: | -------------- | --------------: | ----------------- | ----------------------------- |
|  1. | movie_id       |             INT | PRIMARY KEY       | TMDB / external movie id      |
|  2. | title          |    VARCHAR(255) | NOT NULL          | Movie title                   |
|  3. | overview       |            TEXT | NULL              | Movie synopsis                |
|  4. | poster_path    |    VARCHAR(255) | NULL              | Poster image path/URL         |
|  5. | backdrop_path  |    VARCHAR(255) | NULL              | Backdrop image path/URL       |
|  6. | release_date   |            DATE | NULL              | Release date                  |
|  7. | runtime        |             INT | NULL              | Duration in minutes           |
|  8. | vote_average   |    DECIMAL(3,1) | NULL              | Average rating from source    |
|  9. | vote_count     |             INT | NULL              | Number of votes               |
| 10. | genre_ids      |            JSON | NULL              | Array of genre ids            |
| 11. | director       |    VARCHAR(100) | NULL              | Director name(s)              |
| 12. | cast_info      |            JSON | NULL              | Cast metadata (JSON)          |
| 13. | trailer_key    |     VARCHAR(50) | NULL              | External trailer id/key       |
| 14. | similar_movies |            JSON | NULL              | List of similar movie ids     |
| 15. | trending_order |             INT | DEFAULT 0         | Used to order trending movies |
| 16. | cached_at      |       TIMESTAMP | NOT NULL          | When cached locally           |

## 4. movie_status

**Primary Key:** id

| No: | Fieldname    | Datatype (Size) | Key / Constraints | Description of the field                         |
| --: | ------------ | --------------: | ----------------- | ------------------------------------------------ |
|  1. | id           |             INT | PRIMARY KEY       | Status record id                                 |
|  2. | user_id      |             INT | FOREIGN KEY       | Owner of status                                  |
|  3. | movie_id     |             INT | NOT NULL          | Movie id (FK to movie_cache.movie_id if desired) |
|  4. | status       |     VARCHAR(20) | NOT NULL          | User status for movie                            |
|  5. | date_added   |       TIMESTAMP | NOT NULL          | When added to list                               |
|  6. | date_watched |       TIMESTAMP | NULL              | When user marked watched                         |

## 5. movie_ratings

**Primary Key:** id

| No: | Fieldname  | Datatype (Size) | Key / Constraints | Description of the field                         |
| --: | ---------- | --------------: | ----------------- | ------------------------------------------------ |
|  1. | id         |             INT | PRIMARY KEY       | Rating id                                        |
|  2. | user_id    |             INT | FOREIGN KEY       | Who gave the rating                              |
|  3. | movie_id   |             INT | NOT NULL          | Movie id (FK to movie_cache.movie_id if desired) |
|  4. | rating     |    DECIMAL(2,1) | NOT NULL          | Rating value (1.0 - 10.0)                        |
|  5. | created_at |       TIMESTAMP | NOT NULL          | When rating was created                          |
|  6. | updated_at |       TIMESTAMP | NOT NULL          | When rating was updated                          |

## 6. movie_reviews

**Primary Key:** id

| No: | Fieldname   | Datatype (Size) | Key / Constraints | Description of the field                         |
| --: | ----------- | --------------: | ----------------- | ------------------------------------------------ |
|  1. | id          |             INT | PRIMARY KEY       | Review id                                        |
|  2. | user_id     |             INT | FOREIGN KEY       | Author of review                                 |
|  3. | movie_id    |             INT | NOT NULL          | Movie id (FK to movie_cache.movie_id if desired) |
|  4. | review_text |            TEXT | NOT NULL          | Review body                                      |
|  5. | created_at  |       TIMESTAMP | NOT NULL          | When review was created                          |
|  6. | updated_at  |       TIMESTAMP | NOT NULL          | Last edit timestamp                              |

## 7. achievements

**Primary Key:** id

| No: | Fieldname        | Datatype (Size) | Key / Constraints | Description of the field       |
| --: | ---------------- | --------------: | ----------------- | ------------------------------ |
|  1. | id               |             INT | PRIMARY KEY       | Achievement id                 |
|  2. | name             |     VARCHAR(50) | NOT NULL, UNIQUE  | Achievement name               |
|  3. | description      |            TEXT | NOT NULL          | What the achievement is for    |
|  4. | icon             |    VARCHAR(255) | NULL              | Icon path/URL                  |
|  5. | achievement_type |     VARCHAR(20) | NOT NULL          | Category/type of achievement   |
|  6. | criteria_value   |             INT | NOT NULL          | Numeric threshold for earning  |
|  7. | points           |             INT | DEFAULT 10        | Points awarded                 |
|  8. | is_active        |         BOOLEAN | DEFAULT TRUE      | Whether achievement is enabled |
|  9. | created_at       |       TIMESTAMP | NOT NULL          | When achievement created       |

## 8. user_achievements

**Primary Key:** id

| No: | Fieldname      | Datatype (Size) | Key / Constraints | Description of the field |
| --: | -------------- | --------------: | ----------------- | ------------------------ |
|  1. | id             |             INT | PRIMARY KEY       | User-achievement id      |
|  2. | user_id        |             INT | FOREIGN KEY       | User who earned it       |
|  3. | achievement_id |             INT | FOREIGN KEY       | Which achievement        |
|  4. | earned_at      |       TIMESTAMP | NOT NULL          | When earned              |

## 9. user_activities

**Primary Key:** id

| No: | Fieldname      | Datatype (Size) | Key / Constraints | Description of the field         |
| --: | -------------- | --------------: | ----------------- | -------------------------------- |
|  1. | id             |             INT | PRIMARY KEY       | Activity id                      |
|  2. | user_id        |             INT | FOREIGN KEY       | Actor of the activity            |
|  3. | activity_type  |     VARCHAR(20) | NOT NULL          | Type of activity                 |
|  4. | movie_id       |             INT | NULL              | Related movie id (if applicable) |
|  5. | achievement_id |             INT | FOREIGN KEY       | Related achievement (if any)     |
|  6. | metadata       |            JSON | NULL              | Extra data (e.g. rating value)   |
|  7. | created_at     |       TIMESTAMP | NOT NULL          | When activity occurred           |

## 10. genres

**Primary Key:** id

| No: | Fieldname  | Datatype (Size) | Key / Constraints | Description of the field                   |
| --: | ---------- | --------------: | ----------------- | ------------------------------------------ |
|  1. | id         |             INT | PRIMARY KEY       | Genre id (matches external ids if desired) |
|  2. | name       |     VARCHAR(50) | NOT NULL, UNIQUE  | Genre name                                 |
|  3. | created_at |       TIMESTAMP | NOT NULL          | When genre was added                       |
