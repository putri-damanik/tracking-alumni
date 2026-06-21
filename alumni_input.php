<?php
/**
 * alumni_input.php
 * Halaman khusus role 'alumni' untuk mengisi atau memperbarui
 * data karir mereka sendiri.
 * Logika: jika data karir alumni sudah ada -> UPDATE, jika belum -> INSERT.
 */

require_once __DIR__ . '/auth_check.php';

// Hanya role 'alumni' yang boleh mengakses halaman ini
if ($_SESSION['role'] !== 'alumni') {
    header('Location: dashboard.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// Ambil data alumni berdasarkan user_id yang sedang login
$stmtAlumni = $koneksi->prepare(
    'SELECT id, nim, nama, jurusan, tahun_lulus, email FROM tabel_alumni WHERE user_id = :user_id LIMIT 1'
);
$stmtAlumni->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmtAlumni->execute();
$dataAlumni = $stmtAlumni->fetch();

// Jika belum ada data biodata alumni, halaman tidak bisa lanjut
if (!$dataAlumni) {
    require_once __DIR__ . '/components/header.php';
    echo '<div class="max-w-3xl mx-auto px-4 py-16 text-center">
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl p-6">
                <h2 class="text-lg font-semibold mb-2">Data Biodata Alumni Belum Tersedia</h2>
                <p class="text-sm">Akun Anda belum terhubung dengan data biodata alumni. Silakan hubungi administrator.</p>
            </div>
          </div>';
    require_once __DIR__ . '/components/footer.php';
    exit;
}

$alumni_id = (int) $dataAlumni['id'];
$_SESSION['alumni_id'] = $alumni_id;

$success_message = '';
$error_message = '';

// Cek apakah data karir untuk alumni ini sudah ada
$stmtCekKarir = $koneksi->prepare('SELECT id FROM tabel_karir WHERE alumni_id = :alumni_id LIMIT 1');
$stmtCekKarir->bindParam(':alumni_id', $alumni_id, PDO::PARAM_INT);
$stmtCekKarir->execute();
$karirExisting = $stmtCekKarir->fetch();

// ============================
// PROSES SIMPAN DATA (INSERT/UPDATE)
// ============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status              = trim($_POST['status'] ?? '');
    $sektor_industri     = trim($_POST['sektor_industri'] ?? '');
    $nama_perusahaan     = trim($_POST['nama_perusahaan'] ?? '');
    $gaji_pertama        = trim($_POST['gaji_pertama'] ?? '0');
    $waktu_tunggu_bulan  = trim($_POST['waktu_tunggu_bulan'] ?? '0');

    $statusValid = ['Bekerja', 'Wirausaha', 'Kuliah', 'Mencari Kerja'];

    if ($status === '' || !in_array($status, $statusValid, true)) {
        $error_message = 'Status karir wajib dipilih dengan benar.';
    } elseif (!is_numeric($gaji_pertama) || (float) $gaji_pertama < 0) {
        $error_message = 'Gaji pertama harus berupa angka dan tidak boleh negatif.';
    } elseif (!is_numeric($waktu_tunggu_bulan) || (int) $waktu_tunggu_bulan < 0) {
        $error_message = 'Waktu tunggu harus berupa angka dan tidak boleh negatif.';
    } else {
        $gaji_pertama = (float) $gaji_pertama;
        $waktu_tunggu_bulan = (int) $waktu_tunggu_bulan;

        try {
            if ($karirExisting) {
                // Data karir sudah ada -> UPDATE
                $stmt = $koneksi->prepare(
                    'UPDATE tabel_karir
                     SET status = :status,
                         sektor_industri = :sektor_industri,
                         nama_perusahaan = :nama_perusahaan,
                         gaji_pertama = :gaji_pertama,
                         waktu_tunggu_bulan = :waktu_tunggu_bulan
                     WHERE alumni_id = :alumni_id'
                );
            } else {
                // Data karir belum ada -> INSERT
                $stmt = $koneksi->prepare(
                    'INSERT INTO tabel_karir
                        (alumni_id, status, sektor_industri, nama_perusahaan, gaji_pertama, waktu_tunggu_bulan)
                     VALUES
                        (:alumni_id, :status, :sektor_industri, :nama_perusahaan, :gaji_pertama, :waktu_tunggu_bulan)'
                );
            }

            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':sektor_industri', $sektor_industri, PDO::PARAM_STR);
            $stmt->bindParam(':nama_perusahaan', $nama_perusahaan, PDO::PARAM_STR);
            $stmt->bindParam(':gaji_pertama', $gaji_pertama);
            $stmt->bindParam(':waktu_tunggu_bulan', $waktu_tunggu_bulan, PDO::PARAM_INT);
            $stmt->bindParam(':alumni_id', $alumni_id, PDO::PARAM_INT);
            $stmt->execute();

            $success_message = $karirExisting
                ? 'Data karir berhasil diperbarui.'
                : 'Data karir berhasil disimpan.';

            // Refresh data karir setelah disimpan
            $stmtCekKarir->execute();
            $karirExisting = $stmtCekKarir->fetch();

        } catch (PDOException $e) {
            $error_message = 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.';
        }
    }
}

// Ambil data karir terbaru untuk ditampilkan di form (baik setelah submit maupun saat pertama kali load)
$stmtKarir = $koneksi->prepare('SELECT * FROM tabel_karir WHERE alumni_id = :alumni_id LIMIT 1');
$stmtKarir->bindParam(':alumni_id', $alumni_id, PDO::PARAM_INT);
$stmtKarir->execute();
$karirData = $stmtKarir->fetch();

$status_value             = $karirData['status'] ?? '';
$sektor_industri_value    = $karirData['sektor_industri'] ?? '';
$nama_perusahaan_value    = $karirData['nama_perusahaan'] ?? '';
$gaji_pertama_value       = $karirData['gaji_pertama'] ?? '';
$waktu_tunggu_bulan_value = $karirData['waktu_tunggu_bulan'] ?? '';

$active_menu = 'alumni_input';
$page_title = 'Input Data Karir - Sistem Tracking Alumni';
require_once __DIR__ . '/components/header.php';
?>

<div class="flex flex-col md:flex-row">
    <?php require_once __DIR__ . '/components/sidebar.php'; ?>

    <main class="flex-1 p-4 sm:p-6 lg:p-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Input Data Karir</h1>
            <p class="text-gray-500 text-sm mt-1">Lengkapi atau perbarui informasi karir Anda saat ini</p>
        </div>

        <!-- INFO BIODATA ALUMNI -->
        <div class="bg-white rounded-xl shadow p-6 mb-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Biodata Alumni</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <p class="text-gray-400">NIM</p>
                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($dataAlumni['nim']); ?></p>
                </div>
                <div>
                    <p class="text-gray-400">Nama</p>
                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($dataAlumni['nama']); ?></p>
                </div>
                <div>
                    <p class="text-gray-400">Jurusan</p>
                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($dataAlumni['jurusan']); ?></p>
                </div>
                <div>
                    <p class="text-gray-400">Tahun Lulus</p>
                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars((string) $dataAlumni['tahun_lulus']); ?></p>
                </div>
            </div>
        </div>

        <!-- FORM INPUT/UPDATE KARIR -->
        <div class="bg-white rounded-xl shadow p-6 max-w-3xl">
            <h2 class="text-lg font-semibold text-gray-800 mb-1">
                <?php echo $karirExisting ? 'Perbarui Data Karir' : 'Isi Data Karir'; ?>
            </h2>
            <p class="text-sm text-gray-500 mb-5">
                <?php echo $karirExisting
                    ? 'Anda sudah pernah mengisi data karir. Mengirim form ini akan memperbarui data sebelumnya.'
                    : 'Anda belum mengisi data karir. Silakan lengkapi form di bawah ini.'; ?>
            </p>

            <?php if ($success_message !== ''): ?>
                <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-sm">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message !== ''): ?>
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="alumni_input.php" class="space-y-5">

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status Karir</label>
                    <select id="status" name="status" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition bg-white">
                        <option value="">-- Pilih Status --</option>
                        <option value="Bekerja" <?php echo $status_value === 'Bekerja' ? 'selected' : ''; ?>>Bekerja</option>
                        <option value="Wirausaha" <?php echo $status_value === 'Wirausaha' ? 'selected' : ''; ?>>Wirausaha</option>
                        <option value="Kuliah" <?php echo $status_value === 'Kuliah' ? 'selected' : ''; ?>>Kuliah</option>
                        <option value="Mencari Kerja" <?php echo $status_value === 'Mencari Kerja' ? 'selected' : ''; ?>>Mencari Kerja</option>
                    </select>
                </div>

                <div>
                    <label for="sektor_industri" class="block text-sm font-medium text-gray-700 mb-1">Sektor Industri</label>
                    <input
                        type="text"
                        id="sektor_industri"
                        name="sektor_industri"
                        value="<?php echo htmlspecialchars($sektor_industri_value); ?>"
                        placeholder="Contoh: Teknologi Informasi, Perbankan, Pendidikan"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                </div>

                <div>
                    <label for="nama_perusahaan" class="block text-sm font-medium text-gray-700 mb-1">Nama Perusahaan / Kampus</label>
                    <input
                        type="text"
                        id="nama_perusahaan"
                        name="nama_perusahaan"
                        value="<?php echo htmlspecialchars($nama_perusahaan_value); ?>"
                        placeholder="Contoh: PT Sinergi Digital Nusantara"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="gaji_pertama" class="block text-sm font-medium text-gray-700 mb-1">Gaji Pertama (Rp)</label>
                        <input
                            type="number"
                            id="gaji_pertama"
                            name="gaji_pertama"
                            min="0"
                            step="0.01"
                            value="<?php echo htmlspecialchars((string) $gaji_pertama_value); ?>"
                            placeholder="Contoh: 5000000"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    </div>
                    <div>
                        <label for="waktu_tunggu_bulan" class="block text-sm font-medium text-gray-700 mb-1">Waktu Tunggu (Bulan)</label>
                        <input
                            type="number"
                            id="waktu_tunggu_bulan"
                            name="waktu_tunggu_bulan"
                            min="0"
                            step="1"
                            value="<?php echo htmlspecialchars((string) $waktu_tunggu_bulan_value); ?>"
                            placeholder="Contoh: 3"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    </div>
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-lg transition shadow-md">
                        <?php echo $karirExisting ? 'Perbarui Data' : 'Simpan Data'; ?>
                    </button>
                </div>
            </form>
        </div>

    </main>
</div>

<?php require_once __DIR__ . '/components/footer.php'; ?>
