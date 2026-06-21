<?php
/**
 * components/header.php
 * Navbar atas serta pemanggilan CDN Tailwind CSS dan Chart.js.
 * Variabel $page_title bisa di-set di halaman pemanggil sebelum include file ini.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = $page_title ?? 'Sistem Tracking Alumni';
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-thumb { background: #142569; border-radius: 8px; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

<nav class="bg-indigo-700 shadow-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                </svg>
                <span class="text-white font-bold text-lg">Tracking Alumni</span>
            </div>

            <div class="hidden md:flex items-center gap-6">
                <a href="index.php" class="text-indigo-100 hover:text-white text-sm font-medium transition">Beranda</a>
                <?php if ($is_logged_in): ?>
                    <a href="dashboard.php" class="text-indigo-100 hover:text-white text-sm font-medium transition">Dashboard</a>
                    <?php if ($_SESSION['role'] === 'alumni'): ?>
                        <a href="alumni_input.php" class="text-indigo-100 hover:text-white text-sm font-medium transition">Input Karir</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-3">
                <?php if ($is_logged_in): ?>
                    <span class="hidden sm:inline text-indigo-100 text-sm">
                        Hai, <span class="font-semibold text-white"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <span class="ml-1 px-2 py-0.5 bg-indigo-500 rounded-full text-xs uppercase"><?php echo htmlspecialchars($_SESSION['role']); ?></span>
                    </span>
                    <a href="logout.php" class="bg-white/10 hover:bg-white/20 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        Keluar
                    </a>
                <?php else: ?>
                    <a href="register.php" class="hidden sm:inline-block text-indigo-100 hover:text-white text-sm font-medium transition">
                        Daftar
                    </a>
                    <a href="login.php" class="bg-white text-indigo-700 hover:bg-indigo-50 text-sm font-semibold px-4 py-2 rounded-lg transition shadow">
                        Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
