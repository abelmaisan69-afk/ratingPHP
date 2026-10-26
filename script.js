
document.addEventListener('DOMContentLoaded', () => {
    // Tampilkan atau sembunyikan password
    document.querySelectorAll('[data-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(
                button.dataset.toggle
            );

            if (!input) return;

            const hidden = input.type === 'password';
            input.type = hidden ? 'text' : 'password';
            button.textContent = hidden ? 'Sembunyikan' : 'Lihat';
        });
    });

    // Validasi konfirmasi password saat registrasi
    const registerForm = document.querySelector(
        'form:has(#confirm_password)'
    );

    if (registerForm) {
        registerForm.addEventListener('submit', (event) => {
            const password = document.getElementById('password');
            const confirm = document.getElementById(
                'confirm_password'
            );

            if (
                password &&
                confirm &&
                password.value !== confirm.value
            ) {
                event.preventDefault();
                alert('Konfirmasi password tidak cocok.');
                confirm.focus();
            }
        });
    }

    // Rating bintang
    const ratingInputs = document.querySelectorAll(
        'input[name="rating"]'
    );
    const ratingCaption = document.getElementById('rating-caption');

    const ratingLabels = {
        1: 'Sangat kurang',
        2: 'Kurang',
        3: 'Cukup',
        4: 'Baik',
        5: 'Sangat baik'
    };

    ratingInputs.forEach((input) => {
        input.addEventListener('change', () => {
            if (ratingCaption) {
                ratingCaption.textContent =
                    ratingLabels[input.value] || 'Pilih rating';
            }
        });
    });

    // Penghitung karakter masukan
    const message = document.getElementById('message');
    const charCount = document.getElementById('char-count');

    function updateCharCount() {
        if (message && charCount) {
            charCount.textContent = message.value.length;
        }
    }

    if (message) {
        message.addEventListener('input', updateCharCount);
        updateCharCount();
    }

    // Mode gelap, disimpan di browser
    const themeButton = document.getElementById('theme-toggle');

    function applyTheme(isDark) {
        document.body.classList.toggle('dark-mode', isDark);

        if (themeButton) {
            themeButton.textContent = isDark
                ? 'Mode terang'
                : 'Mode gelap';
        }
    }

    let darkMode = false;

    try {
        darkMode = localStorage.getItem('sipena-theme') === 'dark';
    } catch (error) {
        darkMode = false;
    }

    applyTheme(darkMode);

    if (themeButton) {
        themeButton.addEventListener('click', () => {
            darkMode = !document.body.classList.contains('dark-mode');
            applyTheme(darkMode);

            try {
                localStorage.setItem(
                    'sipena-theme',
                    darkMode ? 'dark' : 'light'
                );
            } catch (error) {
                // Tema tetap bekerja selama halaman terbuka.
            }
        });
    }

    // Konfirmasi sebelum keluar
    document.querySelectorAll('[data-logout]').forEach((link) => {
        link.addEventListener('click', (event) => {
            if (!confirm('Yakin ingin keluar dari akun SIPENA?')) {
                event.preventDefault();
            }
        });
    });
});
