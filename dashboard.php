
<?php
require_once 'config.php';
requireLogin();

$success = '';
$error = '';

function chatbotReply(string $category, string $message): string
{
    $text = strtolower($message);

    if (
        str_contains($text, 'terima kasih') ||
        str_contains($text, 'terimakasih')
    ) {
        return 'Sama-sama! Terima kasih sudah ikut berkontribusi untuk sekolah.';
    }

    if (
        str_contains($text, 'toilet') ||
        str_contains($text, 'sampah') ||
        str_contains($text, 'kotor')
    ) {
        return 'Terima kasih atas masukannya tentang kebersihan. Semoga kondisi sekolah semakin nyaman.';
    }

    if (
        str_contains($text, 'guru') ||
        str_contains($text, 'belajar') ||
        str_contains($text, 'pelajaran')
    ) {
        return 'Terima kasih atas saran tentang pembelajaran. Masukanmu penting untuk evaluasi kegiatan belajar.';
    }

    if (
        str_contains($text, 'wifi') ||
        str_contains($text, 'komputer') ||
        str_contains($text, 'kelas') ||
        str_contains($text, 'kursi')
    ) {
        return 'Terima kasih atas laporan fasilitasnya. Semoga masukan ini membantu meningkatkan kenyamanan sekolah.';
    }

    return 'Terima kasih telah menyampaikan aspirasi tentang ' .
        strtolower($category) .
        '. Masukanmu sudah tercatat di SIPENA.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $category = $_POST['category'] ?? '';
    $rating = filter_var(
        $_POST['rating'] ?? null,
        FILTER_VALIDATE_INT
    );
    $message = trim($_POST['message'] ?? '');

    $allowedCategories = [
        'Fasilitas',
        'Pembelajaran',
        'Kebersihan',
        'Pelayanan',
        'Ekstrakurikuler',
        'Lainnya'
    ];

    if (!in_array($category, $allowedCategories, true)) {
        $error = 'Pilih kategori yang tersedia.';
    } elseif ($rating === false || $rating < 1 || $rating > 5) {
        $error = 'Pilih rating antara 1 sampai 5 bintang.';
    } elseif ($message === '' || mb_strlen($message) > 1000) {
        $error = 'Masukan wajib diisi dan maksimal 1000 karakter.';
    } else {
        $reply = chatbotReply($category, $message);

        $stmt = $pdo->prepare(
            'INSERT INTO feedbacks
                (user_id, category, rating, message, bot_reply)
             VALUES (?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $_SESSION['user_id'],
            $category,
            $rating,
            $message,
            $reply
        ]);

        $success = 'Aspirasi berhasil dikirim. Terima kasih!';
    }
}

$stmt = $pdo->prepare(
    'SELECT COUNT(*) AS total,
            COALESCE(AVG(rating), 0) AS average_rating
     FROM feedbacks
     WHERE user_id = ?'
);
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();

$stmt = $pdo->prepare(
    'SELECT category, rating, message, bot_reply, created_at
     FROM feedbacks
     WHERE user_id = ?
     ORDER BY created_at DESC, id DESC
     LIMIT 20'
);
$stmt->execute([$_SESSION['user_id']]);
$feedbacks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | SIPENA</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>
    <header class="topbar">
        <a class="brand" href="dashboard.php">
            <span class="brand-icon">S</span>
            <span>SIPENA</span>
        </a>

        <nav class="nav-actions">
            <button type="button" class="btn btn-outline"
                    id="theme-toggle">Mode gelap</button>

            <span class="user-greeting">
                Halo, <?= e($_SESSION['user_name'] ?? 'Siswa') ?>
            </span>

            <a class="btn btn-danger" href="logout.php"
               data-logout>Keluar</a>
        </nav>
    </header>

    <main class="container">
        <section class="hero">
            <div>
                <p class="eyebrow">SUARA SISWA, PERUBAHAN BERSAMA</p>
                <h1>Sampaikan aspirasimu.</h1>
                <p>
                    Bantu membangun lingkungan SMK Muhammadiyah 4 Yogyakarta
                    yang lebih nyaman, positif, dan inspiratif.
                </p>
            </div>
            <div class="hero-symbol" aria-hidden="true">✦</div>
        </section>

        <section class="stats-grid">
            <article class="stat-card">
                <span class="stat-icon">✎</span>
                <p>Total aspirasimu</p>
                <h2><?= (int) $stats['total'] ?></h2>
            </article>

            <article class="stat-card">
                <span class="stat-icon">★</span>
                <p>Rata-rata ratingmu</p>
                <h2>
                    <?= number_format((float) $stats['average_rating'], 1) ?>
                    <small>/ 5</small>
                </h2>
            </article>
        </section>

        <div class="content-grid">
            <section class="panel">
                <div class="section-heading">
                    <p class="eyebrow">FORMULIR ASPIRASI</p>
                    <h2>Kritik dan saran</h2>
                    <p class="muted">
                        Tuliskan masukan dengan sopan dan jelas.
                    </p>
                </div>

                <?php if ($success !== ''): ?>
                    <div class="alert success">
                        <?= e($success) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error !== ''): ?>
                    <div class="alert error">
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="form-stack" id="feedback-form">
                    <input type="hidden" name="csrf_token"
                           value="<?= e(csrfToken()) ?>">

                    <label for="category">Kategori aspirasi</label>
                    <select name="category" id="category" required>
                        <option value="">Pilih kategori</option>
                        <option>Fasilitas</option>
                        <option>Pembelajaran</option>
                        <option>Kebersihan</option>
                        <option>Pelayanan</option>
                        <option>Ekstrakurikuler</option>
                        <option>Lainnya</option>
                    </select>

                    <fieldset class="rating-field">
                        <legend>Berikan rating sekolah</legend>
                        <div class="stars" role="radiogroup"
                             aria-label="Rating dari 1 sampai 5">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <input type="radio" name="rating"
                                       id="star<?= $i ?>"
                                       value="<?= $i ?>"
                                       required>
                                <label for="star<?= $i ?>"
                                       title="<?= $i ?> bintang">★</label>
                            <?php endfor; ?>
                        </div>
                        <p id="rating-caption" class="muted">
                            Pilih rating 1–5 bintang.
                        </p>
                    </fieldset>

                    <label for="message">Isi kritik atau saran</label>
                    <textarea name="message" id="message"
                              rows="5" maxlength="1000"
                              placeholder="Tuliskan aspirasimu di sini..."
                              required></textarea>
                    <p class="char-counter">
                        <span id="char-count">0</span>/1000 karakter
                    </p>

                    <button type="submit" class="btn btn-primary">
                        Kirim Aspirasi
                    </button>
                </form>

                <div class="chatbot-box">
                    <div class="chatbot-avatar">S</div>
                    <div>
                        <strong>Chatbot SIPENA</strong>
                        <p>
                            Aku akan memberikan balasan otomatis setelah
                            kamu mengirim aspirasi.
                        </p>
                    </div>
                </div>
            </section>

            <section class="panel">
                <div class="section-heading">
                    <p class="eyebrow">RIWAYAT PRIBADI</p>
                    <h2>Aspirasimu</h2>
                    <p class="muted">
                        Menampilkan maksimal 20 masukan terbaru milikmu.
                    </p>
                </div>

                <?php if (!$feedbacks): ?>
                    <div class="empty-state">
                        <span>✎</span>
                        <h3>Belum ada aspirasi</h3>
                        <p>Masukan pertamamu akan muncul di sini.</p>
                    </div>
                <?php else: ?>
                    <div class="feedback-list">
                        <?php foreach ($feedbacks as $item): ?>
                            <article class="feedback-card">
                                <div class="feedback-top">
                                    <span class="category-tag">
                                        <?= e($item['category']) ?>
                                    </span>
                                    <span class="rating-display"
                                          aria-label="<?= (int) $item['rating'] ?> dari 5 bintang">
                                        <?= str_repeat('★', (int) $item['rating']) ?>
                                        <span class="muted">
                                            <?= (int) $item['rating'] ?>/5
                                        </span>
                                    </span>
                                </div>

                                <p class="feedback-message">
                                    <?= nl2br(e($item['message'])) ?>
                                </p>

                                <p class="feedback-date">
                                    <?= e(date(
                                        'd-m-Y H:i',
                                        strtotime($item['created_at'])
                                    )) ?>
                                </p>

                                <div class="bot-reply">
                                    <strong>Balasan SIPENA</strong>
                                    <p><?= e($item['bot_reply']) ?></p>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <footer class="footer">
            <p>SIPENA · Sistem Pengaduan dan Aspirasi Sekolah</p>
            <p>SMK Muhammadiyah 4 Yogyakarta</p>
        </footer>
    </main>
</body>
</html>
