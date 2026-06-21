<?php
/**
 * components/sidebar.php
 * Menu navigasi samping untuk halaman dashboard, ditampilkan
 * secara dinamis berdasarkan role user yang sedang login.
 * Variabel $active_menu dapat di-set sebelum include untuk highlight menu aktif.
 */

$active_menu = $active_menu ?? '';
$role = $_SESSION['role'] ?? '';

function sidebar_link_class($menu_key, $active_menu) {
    $base = 'flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition';
    if ($menu_key === $active_menu) {
        return $base . ' bg-indigo-600 text-white shadow';
    }
    return $base . ' text-gray-600 hover:bg-indigo-50 hover:text-indigo-700';
}
?>
<aside class="w-full md:w-64 bg-white border-r border-gray-200 md:min-h-[calc(100vh-4rem)] flex-shrink-0">
    <div class="p-4">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 mb-2">Menu Utama</p>
        <nav class="space-y-1">
            <a href="dashboard.php" class="<?php echo sidebar_link_class('dashboard', $active_menu); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
            </a>

            <?php if ($role === 'alumni'): ?>
                <a href="alumni_input.php" class="<?php echo sidebar_link_class('alumni_input', $active_menu); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Input Data Karir
                </a>
            <?php endif; ?>

            <?php if ($role === 'admin'): ?>
                <a href="dashboard.php" class="<?php echo sidebar_link_class('kelola_alumni', $active_menu); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-3.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4" />
                    </svg>
                    Data Alumni
                </a>
            <?php endif; ?>

            <a href="index.php" class="<?php echo sidebar_link_class('index', $active_menu); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7m-9-2v10a1 1 0 001 1h3m6-11l2 2m-2-2v10a1 1 0 01-1 1h-3" />
                </svg>
                Halaman Utama
            </a>

            <a href="logout.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium text-red-500 hover:bg-red-50 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Keluar
            </a>
        </nav>
    </div>
</aside>
