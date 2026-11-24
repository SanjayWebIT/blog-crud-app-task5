<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_role($pdo, ['admin']);
$errors = []; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $userId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
    $newRole = (string) ($_POST['role'] ?? '');
    if (!$userId || !in_array($newRole, ['admin', 'editor', 'author'], true)) $errors[] = 'Select a valid user and role.';
    elseif ($userId === (int) $_SESSION['user_id'] && $newRole !== 'admin') $errors[] = 'You cannot remove your own administrator role.';
    else {
        $exists = $pdo->prepare('SELECT id FROM users WHERE id = :id LIMIT 1');
        $exists->execute([':id' => $userId]);
        if (!$exists->fetch()) $errors[] = 'User not found.';
        else {
            $update = $pdo->prepare('UPDATE users SET role = :role WHERE id = :id');
            $update->execute([':role' => $newRole, ':id' => $userId]);
            $success = 'User role updated successfully.';
        }
    }
}
$users = $pdo->query('SELECT id, username, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>User Roles | My Blog</title><link rel="stylesheet" href="style.css"></head>
<body><header class="topbar"><a class="brand" href="index.php"><span class="brand-mark">B</span> My Blog</a><nav><a href="index.php">Home</a><span class="welcome">Admin: <?= e($_SESSION['username']) ?></span></nav></header>
<main class="container narrow"><a href="index.php">← Blog home</a><h1>User roles & permissions</h1><p class="muted">Only administrators can change account roles. New registrations are always Authors.</p>
<?php foreach ($errors as $error): ?><p class="error"><?= e($error) ?></p><?php endforeach; ?><?php if ($success): ?><p class="success"><?= e($success) ?></p><?php endif; ?>
<div class="role-table-wrap"><table class="role-table"><thead><tr><th>User</th><th>Current role</th><th>Change role</th></tr></thead><tbody>
<?php foreach ($users as $account): ?><tr><td><strong><?= e($account['username']) ?></strong><small>Joined <?= e(date('M d, Y', strtotime($account['created_at']))) ?></small></td><td><span class="role-badge role-<?= e($account['role']) ?>"><?= e(ucfirst($account['role'])) ?></span></td><td><form class="role-form" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="user_id" value="<?= (int) $account['id'] ?>"><select name="role" aria-label="Role for <?= e($account['username']) ?>"><option value="author" <?= $account['role'] === 'author' ? 'selected' : '' ?>>Author</option><option value="editor" <?= $account['role'] === 'editor' ? 'selected' : '' ?>>Editor</option><option value="admin" <?= $account['role'] === 'admin' ? 'selected' : '' ?>>Admin</option></select><button type="submit">Save</button></form></td></tr><?php endforeach; ?>
</tbody></table></div></main></body></html>
