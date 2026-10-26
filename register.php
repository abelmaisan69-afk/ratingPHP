
<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (
        !is_string($pass) ||
        !is_string($confirm) ||
        $name === '' ||
        strlen($name) > 100 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 150
    ) {
        $error = 'Periksa kembali nama dan alamat email kamu.';
    } elseif (strlen($pass) < 8) {
        $error = 'Password minimal 8 karakter.';
    } elseif ($pass !== $confirm) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO users (name, email, password)
                 VALUES (?, ?, ?)'
            );

            $stmt->execute([
                $name,
                $email,
                password_hash($pass, PASSWORD_DEFAULT)
            ]);

            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $pdo->lastInsertId();
            $_SESSION['user_name'] = $name;

            header('Location: dashboard.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $error = 'Email sudah terdaftar. Silakan login.';
            } else {
                $error = 'Registrasi gagal. Silakan coba lagi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun | SIPENA</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body class="auth-page">
    <main class="auth-card">
        <a class="brand" href="index.php">
            <span class="brand-icon">S</span>
            <span>SIPENA</span>
        </a>

        <p class="eyebrow">ASPIRASI SISWA</p>
        <h1>Buat akun baru</h1>
        <p class="muted">Bergabung untuk menyampaikan aspirasi sekolah.</p>

        <?php if ($error !== ''): ?>
            <div class="alert error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="form-stack">
            <input type="hidden" name="csrf_token"
                   value="<?= e(csrfToken()) ?>">

            <label for="name">Nama lengkap</label>
            <input id="name" name="name" type="text"
                   maxlength="100" autocomplete="name"
                   placeholder="Masukkan nama lengkap"
                   value="<?= e($name) ?>" required>

            <label for="email">Email</label>
            <input id="email" name="email" type="email"
                   maxlength="150" autocomplete="email"
                   placeholder="nama@email.com"
                   value="<?= e($email) ?>" required>

            <label for="password">Password</label>
            <div class="password-wrap">
                <input id="password" name="password" type="password"
                       minlength="8" autocomplete="new-password"
                       placeholder="Minimal 8 karakter" required>
                <button type="button" class="password-toggle"
                        data-toggle="password">Lihat</button>
            </div>

            <label for="confirm_password">Konfirmasi password</label>
            <div class="password-wrap">
                <input id="confirm_password" name="confirm_password"
                       type="password" minlength="8"
                       autocomplete="new-password"
                       placeholder="Ulangi password" required>
                <button type="button" class="password-toggle"
                        data-toggle="confirm_password">Lihat</button>
            </div>

            <button class="btn btn-primary" type="submit">
                Daftar Sekarang
            </button>
        </form>

        <p class="auth-footer">
            Sudah punya akun?
            <a href="login.php">Login di sini</a>
        </p>
    </main>
</body>
</html>
