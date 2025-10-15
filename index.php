<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sistem Manajemen Perpustakaan</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>
    <!-- Header Sederhana -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <i class="fas fa-book-open"></i>
                <h1>Perpustakaan Digital</h1>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h2 class="hero-title">Kelola Koleksi Buku Anda dengan Mudah</h2>
                <p class="hero-subtitle">
                    Sistem manajemen perpustakaan modern untuk mengorganisir dan melacak
                    semua koleksi buku Anda
                </p>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <?php
            // Include koneksi database dengan error handling
            $database_file = __DIR__ . '/config/database.php';
            if (file_exists($database_file)) {
                require_once $database_file;
                
                try {
                    // Query untuk statistik
                    $totalBooks = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
                    $totalCategories = $pdo->query("SELECT COUNT(DISTINCT kategori) FROM books")->fetchColumn();
                    $totalBorrowed = $pdo->query("SELECT COUNT(*) FROM books WHERE stok < 1")->fetchColumn();
                    $totalAvailable = $pdo->query("SELECT COUNT(*) FROM books WHERE stok > 0")->fetchColumn();
                } catch(PDOException $e) {
                    // Jika tabel belum ada, set nilai default
                    $totalBooks = 0;
                    $totalCategories = 0;
                    $totalBorrowed = 0;
                    $totalAvailable = 0;
                }
            } else {
                // Jika file database tidak ada, set nilai default
                $totalBooks = 0;
                $totalCategories = 0;
                $totalBorrowed = 0;
                $totalAvailable = 0;
            }
            ?>
            
            <div class="stat-card">
                <i class="fas fa-book"></i>
                <h3 id="totalBooks"><?php echo $totalBooks; ?></h3>
                <p>Total Buku</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-layer-group"></i>
                <h3 id="totalCategories"><?php echo $totalCategories; ?></h3>
                <p>Kategori</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-hand-holding"></i>
                <h3 id="totalBorrowed"><?php echo $totalBorrowed; ?></h3>
                <p>Dipinjam</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-check-circle"></i>
                <h3 id="totalAvailable"><?php echo $totalAvailable; ?></h3>
                <p>Tersedia</p>
            </div>
        </div>
    </section>

    <!-- Books Section -->
    <section class="books-section">
        <div class="container">
            <div class="section-header">
                <h2>Koleksi Buku</h2>
                <div class="hero-actions">
                    <button class="btn btn-primary" onclick="openModal()">
                        <i class="fas fa-plus"></i> Tambah Buku Baru
                    </button>
                </div>
            </div>
            
            <div id="booksContainer" class="books-grid">
                <?php
                if (isset($pdo)) {
                    try {
                        // Query untuk mengambil data buku
                        $stmt = $pdo->query("SELECT * FROM books ORDER BY id DESC");
                        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (empty($books)) {
                            echo '<div class="no-books">Belum ada buku yang ditambahkan.</div>';
                        } else {
                            foreach ($books as $book) {
                                $stockClass = $book['stok'] > 0 ? 'in-stock' : 'out-of-stock';
                                $stockText = $book['stok'] > 0 ? 'Tersedia' : 'Habis';
                ?>
                <div class="book-card" data-category="<?php echo htmlspecialchars($book['kategori']); ?>">
                    <div class="book-cover">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="book-info">
                        <h3 class="book-title"><?php echo htmlspecialchars($book['judul']); ?></h3>
                        <p class="book-author">Oleh: <?php echo htmlspecialchars($book['pengarang']); ?></p>
                        
                        <div class="book-meta">
                            <span class="book-category"><?php echo htmlspecialchars($book['kategori']); ?></span>
                            <span class="book-stock <?php echo $stockClass; ?>"><?php echo $stockText; ?></span>
                        </div>
                        
                        <p class="book-description">
                            <?php 
                            $description = !empty($book['deskripsi']) ? $book['deskripsi'] : 'Tidak ada deskripsi.';
                            echo htmlspecialchars($description);
                            ?>
                        </p>
                        
                        <div class="book-meta">
                            <small>Tahun: <?php echo htmlspecialchars($book['tahun_terbit']); ?></small>
                            <small>Stok: <?php echo htmlspecialchars($book['stok']); ?></small>
                        </div>
                        
                        <div class="book-actions">
                            <button class="btn btn-primary btn-sm" onclick="editBook(<?php echo $book['id']; ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteBook(<?php echo $book['id']; ?>)">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </div>
                    </div>
                </div>
                <?php
                            }
                        }
                    } catch(PDOException $e) {
                        echo '<div class="no-books">Error memuat data: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                } else {
                    echo '<div class="no-books">Database tidak terhubung. Silakan setup database terlebih dahulu.</div>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Modal for Add/Edit Book -->
    <div id="bookModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Tambah Buku Baru</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="bookForm" action="process_book.php" method="POST">
                <input type="hidden" id="bookId" name="bookId" />
                <div class="form-row">
                    <div class="form-group">
                        <label for="judul">Judul Buku *</label>
                        <input type="text" id="judul" name="judul" required />
                    </div>
                    <div class="form-group">
                        <label for="pengarang">Pengarang *</label>
                        <input type="text" id="pengarang" name="pengarang" required />
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="penerbit">Penerbit</label>
                        <input type="text" id="penerbit" name="penerbit" />
                    </div>
                    <div class="form-group">
                        <label for="tahun_terbit">Tahun Terbit</label>
                        <input type="number" id="tahun_terbit" name="tahun_terbit" min="1900" max="2099" />
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="isbn">ISBN</label>
                        <input type="text" id="isbn" name="isbn" />
                    </div>
                    <div class="form-group">
                        <label for="kategori">Kategori *</label>
                        <select id="kategori" name="kategori" required>
                            <option value="">Pilih Kategori</option>
                            <option value="Fiksi">Fiksi</option>
                            <option value="Non-Fiksi">Non-Fiksi</option>
                            <option value="Teknologi">Teknologi</option>
                            <option value="Sejarah">Sejarah</option>
                            <option value="Sains">Sains</option>
                            <option value="Agama">Agama</option>
                            <option value="Anak-anak">Anak-anak</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="jumlah_halaman">Jumlah Halaman</label>
                        <input type="number" id="jumlah_halaman" name="jumlah_halaman" min="1" />
                    </div>
                    <div class="form-group">
                        <label for="stok">Stok *</label>
                        <input type="number" id="stok" name="stok" min="0" required />
                    </div>
                </div>
                <div class="form-group">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" rows="4"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Perpustakaan Digital. Semua hak dilindungi.</p>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>