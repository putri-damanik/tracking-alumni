<?php
/**
 * dashboard.php
 * Halaman utama setelah login. Menampilkan 4 card statistik,
 * 2 grafik (bar chart sektor pekerjaan & donut chart status alumni),
 * serta tabel aktivitas alumni terbaru menggunakan query JOIN.
 */

require_once __DIR__ . '/auth_check.php';

// ============================
// 1. CARD STATISTIK
// ============================

// Total alumni
$totalAlumni = (int) $koneksi->query('SELECT COUNT(*) AS total FROM tabel_alumni')->fetch()['total'];

// Rerata waktu tunggu (bulan) - hanya untuk alumni yang sudah bekerja
$rataTungguRow = $koneksi->query(
    "SELECT AVG(waktu_tunggu_bulan) AS rata FROM tabel_karir WHERE status = 'Bekerja'"
)->fetch();
$rataTunggu = $rataTungguRow['rata'] !== null ? round((float) $rataTungguRow['rata'], 1) : 0;

// Rerata gaji pertama - hanya untuk alumni yang sudah bekerja
$rataGajiRow = $koneksi->query(
    "SELECT AVG(gaji_pertama) AS rata FROM tabel_karir WHERE status = 'Bekerja' AND gaji_pertama > 0"
)->fetch();
$rataGaji = $rataGajiRow['rata'] !== null ? (float) $rataGajiRow['rata'] : 0;

// Jumlah alumni yang masih mencari kerja
$totalMencariKerja = (int) $koneksi->query(
    "SELECT COUNT(*) AS total FROM tabel_karir WHERE status = 'Mencari Kerja'"
)->fetch()['total'];

// ============================
// 2. DATA UNTUK GRAFIK (JSON)
// ============================

// Bar Chart: Distribusi Sektor Pekerjaan
$stmtSektor = $koneksi->query(
    "SELECT sektor_industri, COUNT(*) AS jumlah
     FROM tabel_karir
     WHERE sektor_industri IS NOT NULL AND sektor_industri != ''
     GROUP BY sektor_industri
     ORDER BY jumlah DESC"
);
$dataSektor = $stmtSektor->fetchAll();

$labelSektor = [];
$jumlahSektor = [];
foreach ($dataSektor as $row) {
    $labelSektor[] = $row['sektor_industri'];
    $jumlahSektor[] = (int) $row['jumlah'];
}

// Donut Chart: Persentase Status Alumni
$stmtStatus = $koneksi->query(
    "SELECT status, COUNT(*) AS jumlah
     FROM tabel_karir
     GROUP BY status"
);
$dataStatus = $stmtStatus->fetchAll();

$labelStatus = [];
$jumlahStatus = [];
foreach ($dataStatus as $row) {
    $labelStatus[] = $row['status'];
    $jumlahStatus[] = (int) $row['jumlah'];
}

// ============================
// 3. TABEL AKTIVITAS TERBARU (JOIN)
// ============================
$stmtAktivitas = $koneksi->query(
    "SELECT a.nama, a.jurusan, a.tahun_lulus, k.status, k.sektor_industri, k.nama_perusahaan, k.updated_at
     FROM tabel_karir k
     INNER JOIN tabel_alumni a ON a.id = k.alumni_id
     ORDER BY k.updated_at DESC
     LIMIT 10"
);
$aktivitasTerbaru = $stmtAktivitas->fetchAll();

$active_menu = 'dashboard';
$page_title = 'Dashboard - Sistem Tracking Alumni';
require_once __DIR__ . '/components/header.php';
?>

<div class="flex flex-col md:flex-row">
    <?php require_once __DIR__ . '/components/sidebar.php'; ?>

    <main class="flex-1 p-4 sm:p-6 lg:p-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
            <p class="text-gray-500 text-sm mt-1">Ringkasan statistik dan visualisasi karir alumni</p>
        </div>

        <!-- 4 CARD STATISTIK -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

            <div class="bg-white rounded-xl shadow p-6 border-l-4 border-indigo-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Total Alumni</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo number_format($totalAlumni, 0, ',', '.'); ?></p>
                    </div>
                    <div class="w-11 h-11 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-3.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Rerata Waktu Tunggu</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo $rataTunggu; ?> <span class="text-sm font-medium">bulan</span></p>
                    </div>
                    <div class="w-11 h-11 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow p-6 border-l-4 border-emerald-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Rerata Gaji Pertama</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">Rp <?php echo number_format($rataGaji, 0, ',', '.'); ?></p>
                    </div>
                    <div class="w-11 h-11 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2m0-9.5v9" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow p-6 border-l-4 border-rose-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Mencari Kerja</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo number_format($totalMencariKerja, 0, ',', '.'); ?></p>
                    </div>
                    <div class="w-11 h-11 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                        </svg>
                    </div>
                </div>
            </div>

        </div>

        <!-- GRAFIK -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Distribusi Sektor Pekerjaan Alumni</h2>
            <div class="chart-container" style="position: relative; height: 300px; width: 200%;">
                <canvas id="chartSektor" height="280"></canvas>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Persentase Status Alumni</h2>
            <div class="chart-container" style="position: relative; height: 300px; width: 200%;">
                <canvas id="chartStatus" height="280"></canvas>
            </div>
        </div>

        <!-- TABEL AKTIVITAS TERBARU -->
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Aktivitas Alumni Terbaru</h2>
        <div class="chart-container" style="position: relative; height: 300px; width: 200%;">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b border-gray-200 text-gray-500 uppercase text-xs">
                            <th class="py-3 px-3">Nama</th>
                            <th class="py-3 px-3">Jurusan</th>
                            <th class="py-3 px-3">Tahun Lulus</th>
                            <th class="py-3 px-3">Status</th>
                            <th class="py-3 px-3">Sektor</th>
                            <th class="py-3 px-3">Perusahaan/Kampus</th>
                            <th class="py-3 px-3">Diperbarui</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (count($aktivitasTerbaru) > 0): ?>
                            <?php foreach ($aktivitasTerbaru as $row): ?>
                                <?php
                                $statusColor = match ($row['status']) {
                                    'Bekerja' => 'bg-emerald-100 text-emerald-700',
                                    'Wirausaha' => 'bg-amber-100 text-amber-700',
                                    'Kuliah' => 'bg-blue-100 text-blue-700',
                                    'Mencari Kerja' => 'bg-rose-100 text-rose-700',
                                    default => 'bg-gray-100 text-gray-700',
                                };
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3 px-3 font-medium text-gray-800"><?php echo htmlspecialchars($row['nama']); ?></td>
                                    <td class="py-3 px-3 text-gray-600"><?php echo htmlspecialchars($row['jurusan']); ?></td>
                                    <td class="py-3 px-3 text-gray-600"><?php echo htmlspecialchars((string) $row['tahun_lulus']); ?></td>
                                    <td class="py-3 px-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo $statusColor; ?>">
                                            <?php echo htmlspecialchars($row['status']); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-gray-600"><?php echo htmlspecialchars($row['sektor_industri'] ?? '-'); ?></td>
                                    <td class="py-3 px-3 text-gray-600"><?php echo htmlspecialchars($row['nama_perusahaan'] ?? '-'); ?></td>
                                    <td class="py-3 px-3 text-gray-500"><?php echo htmlspecialchars(date('d M Y', strtotime($row['updated_at']))); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="py-6 text-center text-gray-400">Belum ada data aktivitas karir alumni.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<?php require_once __DIR__ . '/components/footer.php'; ?>

<script>
    // Data dikirim dari PHP ke JavaScript secara aman menggunakan json_encode
    const sektorLabels = <?php echo json_encode($labelSektor, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const sektorData   = <?php echo json_encode($jumlahSektor); ?>;
    const statusLabels = <?php echo json_encode($labelStatus, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const statusData   = <?php echo json_encode($jumlahStatus); ?>;
</script>
<script src="js/charts.js"></script>
