<?php
$host = 'localhost';
$dbname = 'perpustakaan';
$username = 'root';
$password = ''; // Default XAMPP password kosong

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?> 