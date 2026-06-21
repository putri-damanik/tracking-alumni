<?php
/**
 * register.php
 * Form pendaftaran akun baru khusus untuk alumni.
 * Alumni mengisi data akun (username, password) sekaligus biodata
 * (NIM, nama, jurusan, tahun lulus, email) dalam satu form.
 *
 * Validasi anti-duplikat: username, NIM, dan email tidak boleh sama
 * dengan data yang sudah ada di database.
 *
 * Proses INSERT ke tabel_users dan tabel_alumni dibungkus dalam
 * database transaction agar data tetap konsisten (all-or-nothing).
 */

require_once __DIR__ . '/koneksi.php';

// Jika sudah login, arahkan langsung ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error_message = '';
$success_message = '';

// Menyimpan kembali input yang sudah diisi jika terjadi error,
// supaya pengguna tidak perlu mengetik ulang dari awal
$old = [
    'username'    => '',
    'nim'         => '',
    'nama'        => '',
    'jurusan'     => '',
    'tahun_lulus' => '',
    'email'       => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username         = trim($_POST['username'] ?? '');
    $password         = trim($_POST['password'] ?? '');
    $password_confirm  = trim($_POST['password_confirm'] ?? '');
    $nim              = trim($_POST['nim'] ?? '');
    $nama             = trim($_POST['nama'] ?? '');
    $jurusan          = trim($_POST['jurusan'] ?? '');
    $tahun_lulus      = trim($_POST['tahun_lulus'] ?? '');
    $email            = trim($_POST['email'] ?? '');

    $old = [
        'username'    => $username,
        'nim'         => $nim,
        'nama'        => $nama,
        'jurusan'     => $jurusan,
        'tahun_lulus' => $tahun_lulus,
        'email'       => $email,
    ];

    $tahunSekarang = (int) date('Y');

    // ============================
    // VALIDASI INPUT
    // ============================
    if ($username === '' || $password === '' || $password_confirm === '' || $nim === '' || $nama === '' || $jurusan === '' || $tahun_lulus === '' || $email === '') {
        $error_message = 'Seluruh field wajib diisi.';
    } elseif (strlen($username) < 4) {
        $error_message = 'Username minimal 4 karakter.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error_message = 'Username hanya boleh berisi huruf, angka, dan underscore.';
    } elseif (strlen($password) < 6) {
        $error_message = 'Password minimal 6 karakter.';
    } elseif ($password !== $password_confirm) {
        $error_message = 'Konfirmasi password tidak sama dengan password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Format email tidak valid.';
    } elseif (!ctype_digit($tahun_lulus) || (int) $tahun_lulus < 1980 || (int) $tahun_lulus > ($tahunSekarang + 1)) {
        $error_message = 'Tahun lulus tidak valid.';
    } else {
        try {
            // ============================
            // CEK DUPLIKAT: username, NIM, email
            // ============================
            $stmtCek = $koneksi->prepare(
                'SELECT
                    (SELECT COUNT(*) FROM tabel_users WHERE username = :username) AS cek_username,
                    (SELECT COUNT(*) FROM tabel_alumni WHERE nim = :nim) AS cek_nim,
                    (SELECT COUNT(*) FROM tabel_alumni WHERE email = :email) AS cek_email'
            );
            $stmtCek->bindParam(':username', $username, PDO::PARAM_STR);
            $stmtCek->bindParam(':nim', $nim, PDO::PARAM_STR);
            $stmtCek->bindParam(':email', $email, PDO::PARAM_STR);
            $stmtCek->execute();
            $cekHasil = $stmtCek->fetch();

            if ((int) $cekHasil['cek_username'] > 0) {
                $error_message = 'Username sudah digunakan. Silakan pilih username lain.';
            } elseif ((int) $cekHasil['cek_nim'] > 0) {
                $error_message = 'NIM ini sudah terdaftar. Jika ini adalah NIM Anda, silakan hubungi administrator.';
            } elseif ((int) $cekHasil['cek_email'] > 0) {
                $error_message = 'Email ini sudah terdaftar. Silakan gunakan email lain atau login.';
            } else {
                // ============================
                // PROSES SIMPAN (TRANSACTION)
                // ============================
                $koneksi->beginTransaction();

                $passwordHash = password_hash($password, PASSWORD_BCRYPT);

                $stmtUser = $koneksi->prepare(
                    'INSERT INTO tabel_users (username, password, role) VALUES (:username, :password, :role)'
                );
                $roleAlumni = 'alumni';
                $stmtUser->bindParam(':username', $username, PDO::PARAM_STR);
                $stmtUser->bindParam(':password', $passwordHash, PDO::PARAM_STR);
                $stmtUser->bindParam(':role', $roleAlumni, PDO::PARAM_STR);
                $stmtUser->execute();

                $newUserId = (int) $koneksi->lastInsertId();

                $stmtAlumni = $koneksi->prepare(
                    'INSERT INTO tabel_alumni (user_id, nim, nama, jurusan, tahun_lulus, email)
                     VALUES (:user_id, :nim, :nama, :jurusan, :tahun_lulus, :email)'
                );
                $tahunLulusInt = (int) $tahun_lulus;
                $stmtAlumni->bindParam(':user_id', $newUserId, PDO::PARAM_INT);
                $stmtAlumni->bindParam(':nim', $nim, PDO::PARAM_STR);
                $stmtAlumni->bindParam(':nama', $nama, PDO::PARAM_STR);
                $stmtAlumni->bindParam(':jurusan', $jurusan, PDO::PARAM_STR);
                $stmtAlumni->bindParam(':tahun_lulus', $tahunLulusInt, PDO::PARAM_INT);
                $stmtAlumni->bindParam(':email', $email, PDO::PARAM_STR);
                $stmtAlumni->execute();

                $koneksi->commit();

                $success_message = 'Pendaftaran berhasil! Silakan login menggunakan username dan password yang baru Anda buat.';
                $old = [
                    'username'    => '',
                    'nim'         => '',
                    'nama'        => '',
                    'jurusan'     => '',
                    'tahun_lulus' => '',
                    'email'       => '',
                ];
            }
        } catch (PDOException $e) {
            if ($koneksi->inTransaction()) {
                $koneksi->rollBack();
            }
            $error_message = 'Terjadi kesalahan saat mendaftarkan akun. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Alumni - Sistem Tracking Alumni</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-indigo-600 via-indigo-500 to-blue-500 min-h-screen flex items-center justify-center px-4 py-10">

    <div class="w-full max-w-2xl">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="bg-indigo-700 px-8 py-6 text-center">
                <div class="mx-auto w-14 h-14 bg-white/20 rounded-full flex items-center justify-center mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                </div>
                <h1 class="text-white text-xl font-bold">Daftar Akun Alumni</h1>
                <p class="text-indigo-200 text-sm mt-1">Lengkapi data berikut untuk membuat akun</p>
            </div>

            <div class="px-8 py-8">

                <?php if ($success_message !== ''): ?>
                    <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-sm">
                        <?php echo htmlspecialchars($success_message); ?>
                        <div class="mt-2">
                            <a href="login.php" class="font-semibold text-emerald-800 underline">Klik di sini untuk login &rarr;</a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($error_message !== ''): ?>
                    <div class="mb-5 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success_message === ''): ?>
                <form method="POST" action="register.php" class="space-y-5">

                    <div class="border-b border-gray-100 pb-1">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Data Akun</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                required
                                minlength="4"
                                value="<?php echo htmlspecialchars($old['username']); ?>"
                                placeholder="Minimal 4 karakter"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                required
                                value="<?php echo htmlspecialchars($old['email']); ?>"
                                placeholder="nama@email.com"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                required
                                minlength="6"
                                placeholder="Minimal 6 karakter"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                        </div>
                        <div>
                            <label for="password_confirm" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                            <input
                                type="password"
                                id="password_confirm"
                                name="password_confirm"
                                required
                                minlength="6"
                                placeholder="Ulangi password"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                        </div>
                    </div>

                    <div class="border-b border-gray-100 pb-1 pt-3">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Biodata Alumni</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="nim" class="block text-sm font-medium text-gray-700 mb-1">NIM</label>
                            <input
                                type="text"
                                id="nim"
                                name="nim"
                                required
                                value="<?php echo htmlspecialchars($old['nim']); ?>"
                                placeholder="Contoh: 2020110045"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                        </div>
                        <div>
                            <label for="tahun_lulus" class="block text-sm font-medium text-gray-700 mb-1">Tahun Lulus</label>
                            <input
                                type="number"
                                id="tahun_lulus"
                                name="tahun_lulus"
                                required
                                min="1980"
                                max="<?php echo (int) date('Y') + 1; ?>"
                                value="<?php echo htmlspecialchars($old['tahun_lulus']); ?>"
                                placeholder="Contoh: 2023"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                            <input
                                type="text"
                                id="nama"
                                name="nama"
                                required
                                value="<?php echo htmlspecialchars($old['nama']); ?>"
                                placeholder="Nama lengkap sesuai ijazah"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="jurusan" class="block text-sm font-medium text-gray-700 mb-1">Jurusan</label>
                            <input
                                type="text"
                                id="jurusan"
                                name="jurusan"
                                required
                                value="<?php echo htmlspecialchars($old['jurusan']); ?>"
                                placeholder="Contoh: Teknik Informatika"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg transition shadow-md">
                        Daftar Sekarang
                    </button>
                </form>
                <?php endif; ?>

                <div class="mt-6 text-center text-sm text-gray-500">
                    Sudah punya akun?
                    <a href="login.php" class="text-indigo-600 hover:text-indigo-800 font-semibold">Login di sini</a>
                </div>

                <div class="mt-2 text-center">
                    <a href="index.php" class="text-sm text-gray-400 hover:text-gray-600">
                        &larr; Kembali ke Halaman Utama
                    </a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
