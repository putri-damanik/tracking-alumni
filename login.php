<?php
/**
 * login.php
 * Form login untuk admin dan alumni.
 * Verifikasi password menggunakan password_verify() terhadap hash bcrypt
 * yang tersimpan di database (dibuat dengan password_hash()).
 */

require_once __DIR__ . '/koneksi.php';

// Jika sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error_message = 'Username dan password wajib diisi.';
    } else {
        try {
            $stmt = $koneksi->prepare(
                'SELECT id, username, password, role FROM tabel_users WHERE username = :username LIMIT 1'
            );
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];

                // Jika role alumni, ambil alumni_id untuk kebutuhan halaman input
                if ($user['role'] === 'alumni') {
                    $stmtAlumni = $koneksi->prepare(
                        'SELECT id FROM tabel_alumni WHERE user_id = :user_id LIMIT 1'
                    );
                    $stmtAlumni->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
                    $stmtAlumni->execute();
                    $alumniRow = $stmtAlumni->fetch();
                    if ($alumniRow) {
                        $_SESSION['alumni_id'] = $alumniRow['id'];
                    }
                }

                header('Location: dashboard.php');
                exit;
            } else {
                $error_message = 'Username atau password salah.';
            }
        } catch (PDOException $e) {
            $error_message = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Tracking Alumni</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-indigo-600 via-indigo-500 to-blue-500 min-h-screen flex items-center justify-center px-4">

    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="bg-indigo-700 px-8 py-6 text-center">
                <div class="mx-auto w-14 h-14 bg-white/20 rounded-full flex items-center justify-center mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                    </svg>
                </div>
                <h1 class="text-white text-xl font-bold">Sistem Tracking Alumni</h1>
                <p class="text-indigo-200 text-sm mt-1">Masuk untuk melanjutkan</p>
            </div>

            <div class="px-8 py-8">
                <?php if ($error_message !== ''): ?>
                    <div class="mb-4 bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg text-sm">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php" class="space-y-5">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            required
                            autofocus
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                            placeholder="Masukkan username">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                            placeholder="Masukkan password">
                    </div>
                    <button
                        type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg transition shadow-md">
                        Masuk
                    </button>
                </form>

                <div class="mt-6 text-center text-sm text-gray-500">
                    Belum punya akun alumni?
                    <a href="register.php" class="text-indigo-600 hover:text-indigo-800 font-semibold">Daftar di sini</a>
                </div>

                <div class="mt-3 text-center">
                    <a href="index.php" class="text-sm text-gray-400 hover:text-gray-600">
                        &larr; Kembali ke Halaman Utama
                    </a>
                </div>

                <div class="mt-6 border-t pt-4 text-xs text-gray-400 text-center">
                    Demo akun &mdash; Admin: <span class="font-semibold">admin</span> / Alumni: <span class="font-semibold">budianto</span><br>
                    Password keduanya: <span class="font-semibold">123</span>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
