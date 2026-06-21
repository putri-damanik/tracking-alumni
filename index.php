<?php
/**
 * index.php
 * Landing page utama yang bisa diakses publik tanpa login.
 * Menampilkan Quick Stats dinamis yang diambil langsung dari database.
 */

require_once __DIR__ . '/koneksi.php';

// Total alumni terdaftar
$totalAlumni = (int) $koneksi->query('SELECT COUNT(*) AS total FROM tabel_alumni')->fetch()['total'];

// Total yang sudah bekerja
$totalBekerja = (int) $koneksi->query(
    "SELECT COUNT(*) AS total FROM tabel_karir WHERE status = 'Bekerja'"
)->fetch()['total'];

// Total wirausaha
$totalWirausaha = (int) $koneksi->query(
    "SELECT COUNT(*) AS total FROM tabel_karir WHERE status = 'Wirausaha'"
)->fetch()['total'];

// Rerata waktu tunggu kerja (bulan), hanya untuk yang sudah bekerja
$rataTungguRow = $koneksi->query(
    "SELECT AVG(waktu_tunggu_bulan) AS rata FROM tabel_karir WHERE status = 'Bekerja'"
)->fetch();
$rataTunggu = $rataTungguRow['rata'] !== null ? round((float) $rataTungguRow['rata'], 1) : 0;

// Daftar alumni yang sudah bekerja, ditampilkan publik agar mahasiswa
// bisa melihat gambaran nyata penyerapan dunia kerja (tanpa perlu login)
$stmtBekerja = $koneksi->query(
    "SELECT a.nama, a.jurusan, a.tahun_lulus, k.sektor_industri, k.nama_perusahaan, k.gaji_pertama
     FROM tabel_karir k
     INNER JOIN tabel_alumni a ON a.id = k.alumni_id
     WHERE k.status = 'Bekerja'
     ORDER BY k.updated_at DESC"
);
$alumniBekerja = $stmtBekerja->fetchAll();

/**
 * Menyamarkan nominal gaji menjadi format "Rp X,X Juta" agar tetap
 * informatif bagi mahasiswa tanpa menampilkan angka pasti yang terlalu personal.
 */
function formatGajiSamar($gaji) {
    $gaji = (float) $gaji;
    if ($gaji <= 0) {
        return '-';
    }
    $jutaan = $gaji / 1000000;
    return 'Rp ' . number_format($jutaan, 1, ',', '.') . ' Juta';
}

$page_title = 'Sistem Tracking Alumni - Beranda';
require_once __DIR__ . '/components/header.php';
?>

<header class="bg-gradient-to-br from-indigo-700 via-indigo-600 to-blue-600">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
        <h1 class="text-3xl sm:text-5xl font-extrabold text-white leading-tight">
            Sistem Tracking Alumni <br class="hidden sm:block"> &amp; Visualisasi Karir
        </h1>
        <p class="mt-5 text-indigo-100 text-lg max-w-2xl mx-auto">
            Pantau perjalanan karir alumni, lihat statistik penyerapan dunia kerja,
            dan dapatkan gambaran nyata tentang capaian lulusan secara transparan dan real-time.
        </p>
        <div class="mt-8 flex justify-center gap-4">
            <a href="login.php" class="bg-white text-indigo-700 font-semibold px-6 py-3 rounded-lg shadow-lg hover:bg-indigo-50 transition">
                Masuk ke Sistem
            </a>
            <a href="#statistik" class="bg-indigo-500/30 text-white border border-white/40 font-semibold px-6 py-3 rounded-lg hover:bg-indigo-500/50 transition">
                Lihat Statistik
            </a>
        </div>
    </div>
</header>

<main>
    <section id="statistik" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 relative z-10">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

            <div class="bg-white rounded-xl shadow-lg p-6 border-t-4 border-indigo-500">
                <p class="text-sm text-gray-500 font-medium">Total Alumni Terdaftar</p>
                <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo number_format($totalAlumni, 0, ',', '.'); ?></p>
                <p class="text-xs text-gray-400 mt-1">Data dari seluruh angkatan</p>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-t-4 border-emerald-500">
                <p class="text-sm text-gray-500 font-medium">Alumni Sudah Bekerja</p>
                <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo number_format($totalBekerja, 0, ',', '.'); ?></p>
                <p class="text-xs text-gray-400 mt-1">Status karir: Bekerja</p>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-t-4 border-amber-500">
                <p class="text-sm text-gray-500 font-medium">Alumni Wirausaha</p>
                <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo number_format($totalWirausaha, 0, ',', '.'); ?></p>
                <p class="text-xs text-gray-400 mt-1">Membangun usaha sendiri</p>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-t-4 border-blue-500">
                <p class="text-sm text-gray-500 font-medium">Rerata Waktu Tunggu</p>
                <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $rataTunggu; ?> <span class="text-base font-medium">bulan</span></p>
                <p class="text-xs text-gray-400 mt-1">Dari lulus hingga bekerja</p>
            </div>

        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-10">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-800">Alumni yang Sudah Bekerja</h2>
            <p class="text-gray-500 mt-2 max-w-2xl mx-auto">
                Lihat gambaran nyata kesempatan kerja yang diraih para alumni. Data ini terbuka untuk umum
                agar mahasiswa bisa melihat peluang karir di berbagai bidang.
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 uppercase text-xs">
                            <th class="py-3 px-4">Nama Alumni</th>
                            <th class="py-3 px-4">Jurusan</th>
                            <th class="py-3 px-4">Tahun Lulus</th>
                            <th class="py-3 px-4">Sektor Industri</th>
                            <th class="py-3 px-4">Perusahaan</th>
                            <th class="py-3 px-4">Gaji Pertama</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (count($alumniBekerja) > 0): ?>
                            <?php foreach ($alumniBekerja as $row): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3 px-4 font-medium text-gray-800"><?php echo htmlspecialchars($row['nama']); ?></td>
                                    <td class="py-3 px-4 text-gray-600"><?php echo htmlspecialchars($row['jurusan']); ?></td>
                                    <td class="py-3 px-4 text-gray-600"><?php echo htmlspecialchars((string) $row['tahun_lulus']); ?></td>
                                    <td class="py-3 px-4 text-gray-600"><?php echo htmlspecialchars($row['sektor_industri'] ?? '-'); ?></td>
                                    <td class="py-3 px-4 text-gray-600"><?php echo htmlspecialchars($row['nama_perusahaan'] ?? '-'); ?></td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-semibold">
                                            <?php echo htmlspecialchars(formatGajiSamar($row['gaji_pertama'])); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="py-8 text-center text-gray-400">Belum ada data alumni yang bekerja.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 text-xs text-gray-400">
                Menampilkan <?php echo count($alumniBekerja); ?> alumni yang sudah bekerja. Gaji ditampilkan dalam estimasi untuk menjaga privasi.
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
            <div class="p-6">
                <div class="w-14 h-14 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-800 text-lg">Visualisasi Data</h3>
                <p class="text-gray-500 text-sm mt-2">Grafik interaktif menampilkan distribusi sektor pekerjaan dan status karir alumni.</p>
            </div>
            <div class="p-6">
                <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-800 text-lg">Data Akurat &amp; Aman</h3>
                <p class="text-gray-500 text-sm mt-2">Seluruh data tersimpan aman menggunakan koneksi database PDO dan prepared statement.</p>
            </div>
            <div class="p-6">
                <div class="w-14 h-14 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-800 text-lg">Input Mandiri Alumni</h3>
                <p class="text-gray-500 text-sm mt-2">Alumni dapat memperbarui status karirnya sendiri secara mandiri dan real-time.</p>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/components/footer.php'; ?>
