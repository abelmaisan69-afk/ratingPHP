
<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    if (
        !is_string($pass) ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        $error = 'Email atau password salah.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT id, name, email, password
             FROM users WHERE email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['password'])) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['user_name'] = $user['name'];

            header('Location: dashboard.php');
            exit;
        }

        $error = 'Email atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SIPENA</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body class="auth-page">
    <main class="auth-card">
        <a class="brand" href="index.php">
            <span class="brand-icon">S</span>
            <span>SIPENA</span>
        </a>

        <p class="eyebrow">SELAMAT DATANG KEMBALI</p>
        <h1>Login akun</h1>
        <p class="muted">Masuk untuk menyampaikan saran dan kritik.</p>

        <?php if ($error !== ''): ?>
            <div class="alert error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="form-stack">
            <input type="hidden" name="csrf_token"
                   value="<?= e(csrfToken()) ?>">

            <label for="email">Email</label>
            <input id="email" name="email" type="email"
                   autocomplete="email" placeholder="nama@email.com"
                   value="<?= e($email) ?>" required>

            <label for="password">Password</label>
            <div class="password-wrap">
                <input id="password" name="password" type="password"
                       autocomplete="current-password"
                       placeholder="Masukkan password" required>
                <button type="button" class="password-toggle"
                        data-toggle="password">Lihat</button>
            </div>

            <button class="btn btn-primary" type="submit">
                Masuk
            </button>
        </form>

        <p class="auth-footer">
            Belum punya akun?
            <a href="register.php">Daftar sekarang</a>
        </p>
    </main>
</body>
</html>
