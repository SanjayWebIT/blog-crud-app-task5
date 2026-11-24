<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) { http_response_code(404); exit('Post not found.'); }
$stmt = $pdo->prepare('SELECT posts.*, users.username FROM posts JOIN users ON users.id = posts.user_id WHERE posts.id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$post = $stmt->fetch();
if (!$post || ($post['status'] !== 'published' && (!is_logged_in() || !can_manage_post($pdo, (int)$post['user_id'])))) { http_response_code(404); exit('Post not found.'); }
$relatedStmt = $pdo->prepare("SELECT id, title, created_at FROM posts WHERE id <> :id AND status = 'published' ORDER BY created_at DESC LIMIT 4");
$relatedStmt->execute([':id' => $id]);
$relatedPosts = $relatedStmt->fetchAll();
$wordCount = str_word_count(strip_tags($post['content']));
$readMinutes = max(1, (int)ceil($wordCount / 200));
$paragraphs = preg_split('/\R\s*\R/', trim($post['content']));
$paragraphs = array_values(array_filter($paragraphs, static fn($p) => trim($p) !== ''));
$featuredImage = !empty($post['featured_image']) ? 'uploads/' . $post['featured_image'] : 'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?auto=format&fit=crop&w=1200&q=85';
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= e($post['title']) ?> | My Blog</title><meta name="description" content="<?= e(mb_substr(trim(strip_tags($post['content'])), 0, 155)) ?>"><link rel="stylesheet" href="style.css"></head>
<body class="single-page">
<header class="topbar"><a class="brand" href="index.php"><span class="brand-mark">B</span> My Blog</a><nav><a href="index.php">Home</a><?php if (is_logged_in()): ?><span class="welcome">Hi, <?= e($_SESSION['username']) ?></span><?php $navRole = current_user_role($pdo); ?><span class="role-badge role-<?= e($navRole) ?>"><?= e(ucfirst($navRole)) ?></span><a href="dashboard.php">Dashboard</a><?php if ($navRole === 'admin'): ?><a href="users.php">Manage users</a><?php endif; ?><a class="nav-button" href="create.php">+ New post</a><form class="inline" method="post" action="logout.php"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button class="link-button" type="submit">Log out</button></form><?php else: ?><a href="login.php">Log in</a><a class="nav-button" href="register.php">Create account</a><?php endif; ?></nav></header>
<div class="single-wrap">
<main class="article-card">
  <div class="article-heading"><span class="article-category"><?=e(strtoupper($post['category']))?></span><h1><?= e($post['title']) ?></h1><div class="article-meta"><span class="author-avatar"><?= e(strtoupper(substr($post['username'], 0, 1))) ?></span><span>By <strong><?= e($post['username']) ?></strong></span><i></i><span><?= e(date('M d, Y', strtotime($post['created_at']))) ?></span><i></i><span>0 Comments</span><i></i><span><?= $readMinutes ?> min read</span></div></div>
  <img class="article-featured" src="<?= e($featuredImage) ?>" alt="Featured editorial photograph" loading="eager">
  <div class="article-body">
    <?php if (!$paragraphs): ?><p><?= nl2br(e($post['content'])) ?></p><?php else: ?>
      <?php foreach ($paragraphs as $index => $paragraph): ?>
        <?php if ($index === 2): ?><blockquote class="article-quote"><span class="quote-mark">“</span><?= e(mb_substr(trim($paragraph), 0, 260)) ?></blockquote><?php endif; ?>
        <?php if ($index === 4): ?><h2>A closer look</h2><?php endif; ?>
        <p><?= nl2br(e(trim($paragraph))) ?></p>
      <?php endforeach; ?>
    <?php endif; ?>
    <div class="article-image-grid"><img src="https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=700&q=80" alt="Lifestyle editorial" loading="lazy"><img src="https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=700&q=80" alt="Fashion detail" loading="lazy"></div>
    <div class="article-tags"><strong>Tags:</strong><?php foreach(array_filter(array_map('trim',explode(',',$post['tags']??''))) as $tag):?><span><?=e($tag)?></span><?php endforeach;?></div>
    <div class="article-author-box"><div class="author-avatar large"><?= e(strtoupper(substr($post['username'], 0, 1))) ?></div><div><span class="eyebrow">ABOUT THE AUTHOR</span><h3><?= e($post['username']) ?></h3><p>Sharing ideas, stories and useful perspectives with the community.</p></div></div>
    <?php if (is_logged_in() && can_manage_post($pdo, (int)$post['user_id'])): ?><a class="manage-link" href="edit.php?id=<?= (int)$post['id'] ?>">Edit or delete this post →</a><?php endif; ?>
    <div class="related-heading"><h2>Continue Reading</h2><a href="index.php">View all posts →</a></div>
    <div class="related-grid"><?php foreach ($relatedPosts as $related): ?><a class="related-card" href="single.php?id=<?= (int)$related['id'] ?>"><span class="related-thumb">B.</span><span><strong><?= e($related['title']) ?></strong><small><?= e(date('M d, Y', strtotime($related['created_at']))) ?></small></span></a><?php endforeach; ?></div>
  </div>
</main>
<aside class="single-sidebar">
  <section class="sidebar-widget"><h3>Social Networks</h3><div class="social-grid"><a href="https://www.instagram.com/" target="_blank" rel="noopener"><b class="ig">◎</b> Instagram</a><a href="https://twitter.com/" target="_blank" rel="noopener"><b class="tw">t</b> Twitter</a><a href="https://www.facebook.com/" target="_blank" rel="noopener"><b class="fb">f</b> Facebook</a><a href="https://www.youtube.com/" target="_blank" rel="noopener"><b class="yt">▶</b> YouTube</a><a href="https://www.pinterest.com/" target="_blank" rel="noopener"><b class="pin">p</b> Pinterest</a><a href="https://www.linkedin.com/" target="_blank" rel="noopener"><b class="in">in</b> LinkedIn</a></div></section>
  <section class="sidebar-widget"><h3>Category</h3><div class="category-list"><a href="index.php"><b>✦</b> Fashion</a><a href="index.php"><b>◈</b> Lifestyle</a><a href="index.php"><b>▣</b> Health</a><a href="index.php"><b>⌁</b> Travel</a></div></section>
  <section class="sidebar-widget"><div class="widget-tabs"><span class="active">POPULAR</span><span>RECENT</span><span>TRENDY</span></div><div class="popular-list"><?php if ($relatedPosts): foreach ($relatedPosts as $item): ?><a href="single.php?id=<?= (int)$item['id'] ?>"><span class="popular-thumb">B.</span><span><strong><?= e($item['title']) ?></strong><small><?= e(date('M d, Y', strtotime($item['created_at']))) ?></small></span></a><?php endforeach; else: ?><p class="sidebar-muted">More stories will appear here as they are published.</p><?php endif; ?></div></section>
  <section class="sidebar-widget"><h3>Tag Cloud</h3><div class="tag-cloud"><a href="index.php">Fashion</a><a href="index.php">Health</a><a href="index.php">Technology</a><a href="index.php">Lifestyle</a><a href="index.php">Food</a><a href="index.php">Sport</a><a href="index.php">Media</a><a href="index.php">Photography</a><a href="index.php">Style</a><a href="index.php">Culture</a><a href="index.php">Beauty</a><a href="index.php">Travel</a></div></section>
</aside>
</div><footer class="footer">Built with PHP, MySQL, and curiosity.</footer></body></html>
