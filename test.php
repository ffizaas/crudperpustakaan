<?php
require_once 'config/database.php';

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM books");
    $count = $stmt->fetchColumn();
    echo "Koneksi database BERHASIL! Total buku: " . $count;
} catch(PDOException $e) {
    echo "Koneksi database GAGAL: " . $e->getMessage();
}
?>