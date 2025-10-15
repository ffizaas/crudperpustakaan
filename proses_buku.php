<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookId = $_POST['bookId'] ?? '';
    $judul = $_POST['judul'];
    $pengarang = $_POST['pengarang'];
    $penerbit = $_POST['penerbit'] ?? '';
    $tahun_terbit = $_POST['tahun_terbit'] ?? '';
    $isbn = $_POST['isbn'] ?? '';
    $kategori = $_POST['kategori'];
    $jumlah_halaman = $_POST['jumlah_halaman'] ?? '';
    $stok = $_POST['stok'];
    $deskripsi = $_POST['deskripsi'] ?? '';

    try {
        if (empty($bookId)) {
            // Tambah buku baru
            $stmt = $pdo->prepare("INSERT INTO books (judul, pengarang, penerbit, tahun_terbit, isbn, kategori, jumlah_halaman, stok, deskripsi) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$judul, $pengarang, $penerbit, $tahun_terbit, $isbn, $kategori, $jumlah_halaman, $stok, $deskripsi]);
            $message = "Buku berhasil ditambahkan!";
        } else {
            // Update buku
            $stmt = $pdo->prepare("UPDATE books SET judul=?, pengarang=?, penerbit=?, tahun_terbit=?, isbn=?, kategori=?, jumlah_halaman=?, stok=?, deskripsi=? 
                                   WHERE id=?");
            $stmt->execute([$judul, $pengarang, $penerbit, $tahun_terbit, $isbn, $kategori, $jumlah_halaman, $stok, $deskripsi, $bookId]);
            $message = "Buku berhasil diperbarui!";
        }
        
        header("Location: index.php?success=" . urlencode($message));
        exit;
        
    } catch(PDOException $e) {
        header("Location: index.php?error=" . urlencode("Terjadi kesalahan: " . $e->getMessage()));
        exit;
    }
}
?>