<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
$errors = [];
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if ($username === '' || strlen($username) > 30 || $password === '') $errors[] = 'Enter a valid username and password.';
    else {
        $stmt = $pdo->prepare('SELECT id, username, password, role FROM users WHERE username = :username LIMIT 1');
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            redirect('dashboard.php');
        }
        $errors[] = 'Invalid username or password.';
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Log in</title><link rel="stylesheet" href="style.css"></head>
<body><main class="auth-card"><a href="index.php">← Blog home</a><h1>Log in</h1>
<?php if (isset($_GET['registered'])): ?><p class="success">Account created. You can now log in.</p><?php endif; ?>
<?php foreach ($errors as $error): ?><p class="error"><?= e($error) ?></p><?php endforeach; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label for="username">Username</label><input id="username" name="username" required maxlength="30" value="<?= e($username) ?>" autocomplete="username">
<label for="password">Password</label><input id="password" type="password" name="password" required maxlength="72" autocomplete="current-password">
<button type="submit">Log in</button></form><p>New here? <a href="register.php">Create an account</a></p></main></body></html>
