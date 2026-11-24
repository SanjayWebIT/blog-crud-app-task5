-- Run this ONCE on your existing Task 4 `blog` database.
-- Do not import database.sql over an existing database.
USE blog;
ALTER TABLE posts
  ADD COLUMN category VARCHAR(80) NOT NULL DEFAULT 'General' AFTER content,
  ADD COLUMN tags VARCHAR(500) NOT NULL DEFAULT '' AFTER category,
  ADD COLUMN status ENUM('draft','published') NOT NULL DEFAULT 'published' AFTER tags,
  ADD COLUMN featured_image VARCHAR(255) DEFAULT NULL AFTER status,
  ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;
CREATE INDEX idx_posts_status_created ON posts(status, created_at);
CREATE INDEX idx_posts_category ON posts(category);
