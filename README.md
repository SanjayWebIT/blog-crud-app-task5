# Blog Studio — Task 5 Final Project (PHP + MySQL)

A responsive editorial-style blog with a content-management dashboard, role-based access, post drafts, categories, tags and featured-image uploads. The Task 3 public editorial layout and single-article sidebar are retained and extended.

## Requirements
- PHP 8.0+ (PDO MySQL and mbstring extensions)
- MySQL/MariaDB; MAMP, XAMPP or WAMP
- Apache with PHP file uploads enabled

## Fresh installation
1. Extract `blog-crud-app` into `C:\MAMP\htdocslog-crud-app` (or your server web root).
2. Start Apache and MySQL.
3. Import `database.sql` in phpMyAdmin. It creates/uses the `blog` database.
4. Check database username/password in `config.php`.
5. Ensure PHP can write to the project directory so `uploads/` can be created. For production, create `uploads/` manually and restrict script execution there.
6. Open `http://localhost/blog-crud-app/`.
7. Register your first account. New registrations are Authors by default.
8. Promote your account using phpMyAdmin SQL: `UPDATE users SET role='admin' WHERE username='YOUR_USERNAME';` then log out and back in.
9. Open **Dashboard** from the navigation.

## Upgrade existing Task 4 database
1. Back up your Task 4 project folder and export the `blog` database first.
2. Replace the project files with this Task 5 folder, preserving `config.php` credentials.
3. In phpMyAdmin select database `blog` and run `migration_task5.sql` exactly once.
4. Existing Task 4 posts remain published and receive the `General` category; existing users and roles remain unchanged.
5. Do not import `database.sql` over your existing database. The migration adds Task 5 fields only.

## Task 5 features
- Admin dashboard: post totals, published/draft counts, registered member count for Admin, recent activity and quick links.
- Post CRUD with Draft/Published workflow.
- Featured image uploads (JPG/PNG/WebP, 3 MB maximum, MIME validation and random filenames).
- Categories and comma-separated tags.
- Public category filter, title/content search and pagination.
- Public homepage retains the editorial layout; single article retains sidebar and related stories.
- Admin/Editor can manage all posts; Authors can manage only their own posts.
- Drafts are not publicly visible; authorized owners/managers can preview them by opening their single-post URL while logged in.
- PDO prepared statements, server-side validation, CSRF tokens, escaped output, password hashing and session hardening.

## Roles
| Role | Permissions |
|---|---|
| Admin | Create/manage all posts, dashboard, manage user roles |
| Editor | Create/manage all posts and dashboard; no user-role management |
| Author | Create posts and manage only own posts and dashboard |
| Guest | View/search/filter published posts |

## Test checklist
- [ ] Register and log in; verify password is not stored as plain text.
- [ ] Promote one account to Admin and confirm dashboard member count and Users link.
- [ ] Create a Draft and verify it is absent from public homepage and visible in dashboard.
- [ ] Publish a post and verify public listing, search and category filter.
- [ ] Upload valid JPG/PNG/WebP; try a non-image and an image over 3 MB (both should be rejected).
- [ ] Edit and delete a post; verify the old featured image is cleaned up on delete/replace.
- [ ] As Author A, try to edit Author B's post URL (expect HTTP 403).
- [ ] As Editor, verify all posts are manageable but `users.php` is denied.
- [ ] Submit missing/invalid fields and invalid CSRF tokens; verify server-side rejection.
- [ ] Try HTML/script text in title/content; it must render as text, not execute.

## Important notes
This is an educational local project, not a production security certification. Before public deployment add login rate limiting, password reset/email verification, secure environment-based secrets, HTTPS, backups, upload-directory script execution restrictions, security logging and least-privilege database credentials. Do not commit real credentials to GitHub.
