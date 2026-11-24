<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
$errors = [];
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) $errors[] = 'Username must be 3–30 characters (letters, numbers, underscore).';
    if (strlen($password) < 8 || strlen($password) > 72) $errors[] = 'Password must be between 8 and 72 characters.';
    if (!$errors) {
        $check = $pdo->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
        $check->execute([':username' => $username]);
        if ($check->fetch()) $errors[] = 'That username is already registered.';
        else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            // Public registration always creates an author; users cannot self-assign privileged roles.
            $insert = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, 'author')");
            $insert->execute([':username' => $username, ':password' => $hash]);
            redirect('login.php?registered=1');
        }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Register</title><link rel="stylesheet" href="style.css"></head>
<body><main class="auth-card"><a href="index.php">← Blog home</a><h1>Create account</h1>
<?php foreach ($errors as $error): ?><p class="error"><?= e($error) ?></p><?php endforeach; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label for="username">Username</label><input id="username" name="username" required minlength="3" maxlength="30" pattern="[A-Za-z0-9_]+" value="<?= e($username) ?>" autocomplete="username">
<label for="password">Password</label><input id="password" type="password" name="password" required minlength="8" maxlength="72" autocomplete="new-password">
<button type="submit">Register</button></form><p>New accounts receive the Author role. An administrator can promote trusted users.</p><p>Already registered? <a href="login.php">Log in</a></p></main></body></html>
