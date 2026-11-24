-- Run this ONCE in phpMyAdmin for an existing Task 3 database.
USE blog;
ALTER TABLE users
  ADD COLUMN role ENUM('admin', 'editor', 'author') NOT NULL DEFAULT 'author' AFTER password;

-- After creating/logging in to your own account, replace YOUR_USERNAME below
-- with that exact username and run this statement to bootstrap the first admin.
UPDATE users SET role = 'admin' WHERE username = 'YOUR_USERNAME';
