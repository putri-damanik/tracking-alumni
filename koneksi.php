<?php
/**
 * koneksi.php
 * File koneksi database menggunakan PDO (PHP Data Objects)
 * PDO digunakan agar mendukung prepared statement,
 * sehingga aman dari serangan SQL Injection.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi database
$db_host = 'localhost';
$db_name = 'db_alumni';
$db_user = 'root';
$db_pass = '';
$db_charset = 'utf8mb4';

$dsn = "mysql:host={$db_host};dbname={$db_name};charset={$db_charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $koneksi = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    die('Koneksi database gagal: ' . htmlspecialchars($e->getMessage()));
}
