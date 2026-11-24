<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
$search = trim((string)($_GET['q'] ?? ''));
$category = trim((string)($_GET['category'] ?? ''));
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
$page = ($page && $page > 0) ? $page : 1;
$perPage = 5;
$where = " WHERE posts.status = 'published'";
$params = [];
if ($search !== '') {
    $where .= ' AND (posts.title LIKE :title OR posts.content LIKE :content)';
    $params = [':title' => '%' . $search . '%', ':content' => '%' . $search . '%'];
}
if ($category !== '') { $where .= ' AND posts.category = :category'; $params[':category'] = $category; }
$countStmt = $pdo->prepare('SELECT COUNT(*) FROM posts' . $where);
$countStmt->execute($params);
$totalPosts = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalPosts / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;
$sql = 'SELECT posts.id, posts.user_id, posts.title, posts.content, posts.category, posts.featured_image, posts.created_at, users.username FROM posts JOIN users ON users.id = posts.user_id' . $where . ' ORDER BY posts.created_at DESC LIMIT :limit OFFSET :offset';
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) $stmt->bindValue($key, $value, PDO::PARAM_STR);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();
function page_url(int $number, string $term, string $category = ''): string {
    $query = ['page' => $number];
    if ($term !== '') $query['q'] = $term;
    if ($category !== '') $query['category'] = $category;
    return 'index.php?' . http_build_query($query);
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>My Blog | Posts</title><link rel="stylesheet" href="style.css"></head><body>
<header class="topbar"><a class="brand" href="index.php"><span class="brand-mark">B</span> My Blog</a><nav>
<?php if (is_logged_in()): ?><span class="welcome">Hi, <?= e($_SESSION['username']) ?></span><?php $navRole = current_user_role($pdo); ?><span class="role-badge role-<?= e($navRole) ?>"><?= e(ucfirst($navRole)) ?></span><a href="dashboard.php">Dashboard</a><?php if ($navRole === 'admin'): ?><a href="users.php">Manage users</a><?php endif; ?><a class="nav-button" href="create.php">+ New post</a><form class="inline" method="post" action="logout.php"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button class="link-button" type="submit">Log out</button></form>
<?php else: ?><a href="login.php">Log in</a><a class="nav-button" href="register.php">Create account</a><?php endif; ?></nav></header>
<main class="container"><section class="hero"><p class="eyebrow">THE COMMUNITY BLOG</p><h1>Ideas worth sharing.</h1><p class="hero-copy">Explore the latest posts, thoughts, and updates from our community.</p>
<form class="search-form" method="get" action="index.php"><input type="hidden" name="category" value="<?= e($category) ?>"><input type="search" name="q" value="<?= e($search) ?>" placeholder="Search by post title or content..." aria-label="Search posts"><button type="submit">Search</button><?php if ($search !== ''): ?><a class="clear-search" href="index.php">Clear</a><?php endif; ?></form></section>
<section class="posts-section"><div class="section-heading"><div><h2><?= $search !== '' ? 'Search results' : 'Latest posts' ?></h2><form class="category-filter" method="get"><input type="hidden" name="q" value="<?= e($search) ?>"><select name="category" onchange="this.form.submit()"><option value="">All categories</option><?php foreach(['Technology','Lifestyle','Education','Business','Travel','Health','News','General'] as $cat):?><option value="<?=e($cat)?>" <?=$category===$cat?'selected':''?>><?=e($cat)?></option><?php endforeach;?></select></form><p class="muted"><?= $totalPosts ?> <?= $totalPosts === 1 ? 'post' : 'posts' ?> found<?= $search !== '' ? ' for “' . e($search) . '”' : '' ?></p></div><?php if (is_logged_in()): ?><a class="button-secondary" href="create.php">Write a post</a><?php endif; ?></div>
<?php if (!$posts): ?><div class="empty-state"><div class="empty-icon">⌕</div><h3>No posts found</h3><p>Try another search term or clear your search.</p><?php if ($search !== ''): ?><a href="index.php">View all posts</a><?php endif; ?></div><?php endif; ?>
<?php foreach ($posts as $post): ?><article class="post"><div class="post-topline"><span class="post-tag"><?= e(strtoupper($post['category'])) ?></span><span class="meta"><?= e($post['created_at']) ?></span></div><?php if (!empty($post['featured_image'])): ?><a href="single.php?id=<?= (int)$post['id'] ?>"><img class="post-featured-thumb" src="uploads/<?=e($post['featured_image'])?>" alt="" loading="lazy"></a><?php endif; ?><h3><a class="post-title-link" href="single.php?id=<?= (int)$post['id'] ?>"><?= e($post['title']) ?></a></h3><p class="byline">Written by <strong><?= e($post['username']) ?></strong></p><div class="content"><?= nl2br(e(mb_substr($post['content'], 0, 320))) ?><?= mb_strlen($post['content']) > 320 ? '…' : '' ?></div><a class="read-article" href="single.php?id=<?= (int)$post['id'] ?>">Read full article →</a><?php if (is_logged_in() && can_manage_post($pdo, (int)$post['user_id'])): ?><a class="manage-link" href="edit.php?id=<?= (int)$post['id'] ?>">Edit or delete post →</a><?php endif; ?></article><?php endforeach; ?>
<?php if ($totalPages > 1): ?><nav class="pagination" aria-label="Pagination"><?php if ($page > 1): ?><a href="<?= e(page_url($page - 1, $search, $category)) ?>">← Previous</a><?php else: ?><span class="disabled">← Previous</span><?php endif; ?><span class="page-status">Page <?= $page ?> of <?= $totalPages ?></span><?php if ($page < $totalPages): ?><a href="<?= e(page_url($page + 1, $search, $category)) ?>">Next →</a><?php else: ?><span class="disabled">Next →</span><?php endif; ?></nav><?php endif; ?></section></main><footer class="footer">Built with PHP, MySQL, and curiosity.</footer></body></html>
