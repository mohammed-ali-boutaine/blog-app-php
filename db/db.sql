

create database blog_app;
use blog_app;

CREATE DATABASE IF NOT EXISTS blog_app ;
USE blog_app;

-- 1. User Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    profile_picture VARCHAR(255), 
    birthday DATE ,
    role_id INT NOT NULL DEFAULT 1, -- References role table
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

-- 2. Role Table
CREATE TABLE roles ( -- 0 == admin , 1 == user
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE 
);

-- 3. Article Table
CREATE TABLE articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- Author of the article, references user
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image_path VARCHAR(255), 
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 4. Commentaire Table
CREATE TABLE commentaires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL, -- References article
    user_id INT NOT NULL, -- Author of the comment
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 5. Like Table
CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL, -- References article
    user_id INT NOT NULL, -- User who liked the article
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 6. UserLogin Table
CREATE TABLE user_logins  (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- References user table
    token VARCHAR(255) NOT NULL, -- Unique session token
    browser VARCHAR(255), 
    ip_address VARCHAR(45), 
    login_time DATETIME DEFAULT CURRENT_TIMESTAMP, 
    logout_time DATETIME, 
    is_active BOOLEAN DEFAULT TRUE, 
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 7. Signal Table
CREATE TABLE signalPosts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT, -- The reported article
    user_id INT NOT NULL, -- User who reported
    reason TEXT NOT NULL, -- Reason for reporting
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
-- 7. Signal Table
CREATE TABLE signalComments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commentaire_id INT, -- The reported comment
    user_id INT NOT NULL, -- User who reported
    reason TEXT NOT NULL, -- Reason for reporting
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (commentaire_id) REFERENCES commentaires(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
-- 8. FormMessages Table
CREATE TABLE form_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
-- Tags Table
CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tag_name VARCHAR(100) NOT NULL UNIQUE
);

-- Article Tags Table (Join Table)
CREATE TABLE article_tags (
    article_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (article_id, tag_id),
    FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);


-- indexes:

CREATE INDEX idx_email ON users(email);
