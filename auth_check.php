<?php
/**
 * auth_check.php
 * Memastikan hanya user yang sudah login (memiliki session aktif)
 * yang dapat mengakses halaman tertentu.
 * Jika belum login, akan dialihkan ke login.php
 */

require_once __DIR__ . '/koneksi.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: login.php');
    exit;
}
