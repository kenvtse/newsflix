<?php
// kategori.php
include 'includes/koneksi.php';

$category_slug = $_GET['category'] ?? '';
$page_title = 'Berita Kategori';
$articles = [];
$total_berita = 0;
$berita_per_halaman = 10;
$halaman_saat_ini = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($halaman_saat_ini - 1) * $berita_per_halaman;
$kategori_id = null;
$kategori_nama = '';

if (isset($koneksi)) {
    // Get category info
    $query_kategori = "SELECT id_kategori, nama_kategori FROM kategori WHERE slug = ?";
    $stmt_kategori = mysqli_prepare($koneksi, $query_kategori);
    mysqli_stmt_bind_param($stmt_kategori, "s", $category_slug);
    mysqli_stmt_execute($stmt_kategori);
    $result_kategori = mysqli_stmt_get_result($stmt_kategori);

    if ($result_kategori && mysqli_num_rows($result_kategori) > 0) {
        $kategori_data = mysqli_fetch_assoc($result_kategori);
        $kategori_id = $kategori_data['id_kategori'];
        $kategori_nama = $kategori_data['nama_kategori'];
        $page_title = 'Kategori: ' . htmlspecialchars($kategori_nama);

        // Count total articles
        $query_count = "SELECT COUNT(*) AS total FROM berita WHERE id_kategori = ? AND status = 'published'";
        $stmt_count = mysqli_prepare($koneksi, $query_count);
        mysqli_stmt_bind_param($stmt_count, "i", $kategori_id);
        mysqli_stmt_execute($stmt_count);
        $result_count = mysqli_stmt_get_result($stmt_count);
        if ($result_count) {
            $row_count = mysqli_fetch_assoc($result_count);
            $total_berita = $row_count['total'];
        }
        mysqli_stmt_close($stmt_count);
        
        // Calculate total pages
        $total_halaman = ceil($total_berita / $berita_per_halaman);
        $total_halaman = max(1, $total_halaman);

        // Get articles with pagination
        $query_berita = "SELECT b.*, k.nama_kategori, k.slug AS kategori_slug 
                        FROM berita b
                        JOIN kategori k ON b.id_kategori = k.id_kategori
                        WHERE b.id_kategori = ? AND b.status = 'published' 
                        ORDER BY b.tanggal_publikasi DESC 
                        LIMIT ?, ?";
        $stmt_berita = mysqli_prepare($koneksi, $query_berita);
        mysqli_stmt_bind_param($stmt_berita, "iii", $kategori_id, $offset, $berita_per_halaman);
        mysqli_stmt_execute($stmt_berita);
        $result_berita = mysqli_stmt_get_result($stmt_berita);

        if ($result_berita) {
            while ($row = mysqli_fetch_assoc($result_berita)) {
                $articles[] = $row;
            }
        }
        mysqli_stmt_close($stmt_berita);
    }
    mysqli_stmt_close($stmt_kategori);
}

// Get popular categories for sidebar
$popular_categories = [];
if (isset($koneksi)) {
    $query_popular = "SELECT k.nama_kategori, k.slug, COUNT(b.id_berita) AS jumlah_berita 
                     FROM kategori k 
                     LEFT JOIN berita b ON k.id_kategori = b.id_kategori 
                     GROUP BY k.id_kategori 
                     ORDER BY jumlah_berita DESC 
                     LIMIT 5";
    $result_popular = mysqli_query($koneksi, $query_popular);
    if ($result_popular) {
        while ($row = mysqli_fetch_assoc($result_popular)) {
            $popular_categories[] = $row;
        }
    }
}

// Get recent news for sidebar
$recent_news = [];
if (isset($koneksi)) {
    $query_recent = "SELECT judul, slug FROM berita 
                    WHERE status = 'published' 
                    ORDER BY tanggal_publikasi DESC 
                    LIMIT 5";
    $result_recent = mysqli_query($koneksi, $query_recent);
    if ($result_recent) {
        while ($row = mysqli_fetch_assoc($result_recent)) {
            $recent_news[] = $row;
        }
    }
}

mysqli_close($koneksi);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - NewsFlix</title>
    <link href="https://fonts.googleapis.com/css2?family=Netflix+Sans:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --netflix-red: #e50914;
            --netflix-red-dark: #b20710;
            --netflix-black: #141414;
            --netflix-dark-gray: #222;
            --netflix-gray: #333;
            --netflix-light-gray: #e5e5e5;
            --netflix-white: #fff;
            --netflix-text-gray: #777;
            --netflix-blue: #0071eb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        body {
            font-family: 'Netflix Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: var(--netflix-black);
            color: var(--netflix-light-gray);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Header Styles */
        .header {
            background: linear-gradient(to bottom, rgba(0,0,0,0.7) 10%, rgba(0,0,0,0));
            padding: 20px 50px;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            color: var(--netflix-red);
            font-size: 2.5rem;
            font-weight: 900;
            text-decoration: none;
            letter-spacing: -1px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .logo:hover {
            color: var(--netflix-red-dark);
        }

        /* Navigation */
        .navbar {
            display: flex;
            align-items: center;
        }

        .navbar ul {
            display: flex;
            list-style: none;
            gap: 25px;
        }

        .navbar ul li a {
            color: var(--netflix-white);
            text-decoration: none;
            font-size: 1.1rem;
            font-weight: 500;
            padding: 8px 15px;
            border-radius: 4px;
            transition: all 0.3s ease;
            position: relative;
        }

        .navbar ul li a:hover {
            color: var(--netflix-red);
        }

        .navbar ul li a.active {
            color: var(--netflix-white);
            background-color: var(--netflix-red);
            box-shadow: 0 4px 15px rgba(229, 9, 20, 0.5);
        }

        .navbar ul li a.active:hover {
            background-color: var(--netflix-red-dark);
        }

        /* Hero Section */
        .category-hero {
            height: 50vh;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://assets.nflxext.com/ffe/siteui/vlv3/9d3533b2-0e2b-40b2-95e0-ecd7979cc88b/2b5a349c-6651-4154-80ed-1a9a3f2bf2a5/ID-id-20240311-popsignuptwoweeks-perspective_alpha_website_large.jpg') no-repeat center center/cover;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 0 20px;
            margin-bottom: 50px;
        }

        .hero-content {
            max-width: 800px;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            color: var(--netflix-white);
            margin-bottom: 20px;
            text-shadow: 2px 2px 5px rgba(0,0,0,0.8);
        }

        .hero-content p {
            font-size: 1.5rem;
            color: var(--netflix-light-gray);
            margin-bottom: 30px;
        }

        /* Main Content Container */
        .main-container {
            max-width: 1400px;
            margin: 0 auto 50px;
            padding: 0 50px;
            display: flex;
            gap: 40px;
        }

        .section-title {
            font-size: 1.8rem;
            color: var(--netflix-white);
            margin-bottom: 30px;
            padding-left: 15px;
            border-left: 5px solid var(--netflix-red);
        }

        /* News Grid */
        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .news-card {
            background-color: var(--netflix-dark-gray);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            border: 1px solid var(--netflix-gray);
        }

        .news-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.7);
        }

        .news-image-container {
            position: relative;
            height: 200px;
            overflow: hidden;
        }

        .news-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .news-card:hover .news-image {
            transform: scale(1.05);
        }

        .news-content {
            padding: 20px;
        }

        .news-title {
            font-size: 1.3rem;
            color: var(--netflix-white);
            margin-bottom: 15px;
            line-height: 1.4;
        }

        .news-title a {
            color: var(--netflix-white);
            text-decoration: none;
        }

        .news-title a:hover {
            color: var(--netflix-red);
        }

        .news-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
            font-size: 0.9rem;
            color: var(--netflix-text-gray);
        }

        .news-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .news-meta i {
            color: var(--netflix-red);
        }

        .news-excerpt {
            color: var(--netflix-light-gray);
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .read-more {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--netflix-red);
            color: var(--netflix-white);
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .read-more:hover {
            background-color: var(--netflix-red-dark);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(229, 9, 20, 0.6);
        }

        /* Sidebar */
        .sidebar {
            flex: 1;
            background-color: var(--netflix-dark-gray);
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            padding: 30px;
            align-self: flex-start;
            position: sticky;
            top: 100px;
            border: 1px solid var(--netflix-gray);
        }

        .sidebar h3 {
            font-size: 1.5rem;
            color: var(--netflix-white);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--netflix-gray);
            text-align: center;
            font-weight: 700;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin-bottom: 15px;
        }

        .sidebar ul li a {
            display: block;
            background-color: var(--netflix-gray);
            color: var(--netflix-white);
            text-decoration: none;
            padding: 15px 20px;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .sidebar ul li a:hover {
            background-color: var(--netflix-red);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(229, 9, 20, 0.6);
        }

        .category-count {
            display: block;
            margin-top: 5px;
            font-size: 0.9rem;
            color: var(--netflix-light-gray);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 50px;
        }

        .pagination a {
            display: inline-block;
            padding: 12px 20px;
            background-color: var(--netflix-gray);
            color: var(--netflix-white);
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .pagination a:hover {
            background-color: var(--netflix-red);
            transform: translateY(-3px);
        }

        .pagination a.active {
            background-color: var(--netflix-red);
        }

        .pagination a.disabled {
            background-color: var(--netflix-gray);
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Error Message */
        .error-message {
            background-color: var(--netflix-dark-gray);
            color: var(--netflix-red);
            padding: 30px;
            text-align: center;
            border-radius: 8px;
            margin: 50px auto;
            max-width: 800px;
            border-left: 5px solid var(--netflix-red);
        }

        /* Category Not Found */
        .category-not-found {
            background-color: var(--netflix-dark-gray);
            padding: 50px;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.7);
            border: 1px solid var(--netflix-gray);
            max-width: 800px;
            margin: 50px auto;
        }

        .category-not-found h2 {
            color: var(--netflix-red);
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        .category-not-found p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }

        .back-button {
            display: inline-block;
            padding: 15px 30px;
            background-color: var(--netflix-red);
            color: var(--netflix-white);
            text-decoration: none;
            border-radius: 4px;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background-color: var(--netflix-red-dark);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(229, 9, 20, 0.6);
        }

        /* Add News Button */
        .add-button {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: var(--netflix-red);
            color: var(--netflix-white);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 1.5rem;
            box-shadow: 0 5px 20px rgba(229, 9, 20, 0.6);
            z-index: 999;
            transition: all 0.3s ease;
        }

        .add-button:hover {
            background-color: var(--netflix-red-dark);
            transform: scale(1.1);
        }

        /* Footer */
        .footer {
            background-color: var(--netflix-black);
            color: var(--netflix-text-gray);
            text-align: center;
            padding: 50px 0;
            margin-top: 80px;
            border-top: 1px solid var(--netflix-gray);
            font-size: 1rem;
        }

        .footer p {
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }

        .social-links a {
            color: var(--netflix-text-gray);
            font-size: 1.5rem;
            transition: color 0.3s ease;
        }

        .social-links a:hover {
            color: var(--netflix-red);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .main-container {
                flex-direction: column;
                gap: 30px;
                padding: 0 30px;
            }
            
            .sidebar {
                position: static;
                order: -1;
                width: 100%;
            }
            
            .category-hero h1 {
                font-size: 3rem;
            }
            
            .news-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }

        @media (max-width: 992px) {
            .header {
                padding: 15px 30px;
            }
            
            .logo {
                font-size: 2rem;
            }
            
            .navbar ul {
                gap: 15px;
            }
            
            .category-hero {
                height: 40vh;
            }
            
            .category-hero h1 {
                font-size: 2.5rem;
            }
            
            .category-hero p {
                font-size: 1.2rem;
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                padding: 15px 20px;
                background-color: var(--netflix-black);
            }
            
            .logo {
                margin-bottom: 15px;
            }
            
            .navbar ul {
                flex-wrap: wrap;
                justify-content: center;
                gap: 10px;
            }
            
            .main-container {
                padding: 0 20px;
            }
            
            .section-title {
                font-size: 1.5rem;
            }
            
            .category-not-found h2 {
                font-size: 2rem;
            }
        }

        @media (max-width: 576px) {
            .category-hero {
                height: 35vh;
            }
            
            .category-hero h1 {
                font-size: 2rem;
            }
            
            .category-hero p {
                font-size: 1rem;
            }
            
            .main-container {
                padding: 0 15px;
            }
            
            .news-grid {
                grid-template-columns: 1fr;
            }
            
            .section-title {
                font-size: 1.3rem;
            }
            
            .pagination {
                flex-wrap: wrap;
            }
            
            .pagination a {
                padding: 10px 15px;
            }
            
            .category-not-found {
                padding: 30px 20px;
            }
            
            .category-not-found h2 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="index.php" class="logo">NEWSFLIX</a>
        <nav class="navbar">
            <ul>
                <li><a href="index.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && !isset($_GET['category'])) ? 'active' : ''; ?>">Beranda</a></li>
                <?php if (!empty($popular_categories)): ?>
                    <?php foreach ($popular_categories as $category): ?>
                        <li><a href="kategori.php?category=<?php echo htmlspecialchars($category['slug']); ?>" class="<?php echo ($category['slug'] == $category_slug) ? 'active' : ''; ?>"><?php echo htmlspecialchars($category['nama_kategori']); ?></a></li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <?php if ($kategori_id !== null): ?>
        <section class="category-hero">
            <div class="hero-content">
                <h1><?php echo htmlspecialchars($kategori_nama); ?></h1>
                <p><?php echo $total_berita; ?> berita tersedia dalam kategori ini</p>
            </div>
        </section>
    <?php endif; ?>

    <div class="main-container">
        <div class="main-content">
            <?php if ($kategori_id !== null): ?>
                <?php if (!empty($articles)): ?>
                    <h2 class="section-title">Berita Terbaru</h2>
                    <div class="news-grid">
                        <?php foreach ($articles as $article): ?>
                            <div class="news-card">
                                <div class="news-image-container">
    <?php if (!empty($article['gambar'])): ?>
        <img src="uploads/<?php echo htmlspecialchars($article['gambar']); ?>" alt="<?php echo htmlspecialchars($article['judul']); ?>" class="news-image">
    <?php else: ?>
        <img src="https://via.placeholder.com/600x400?text=NEWSFLIX" alt="Placeholder" class="news-image">
    <?php endif; ?>
</div>
                                <div class="news-content">
                                    <h3 class="news-title">
                                        <a href="detail_berita.php?slug=<?php echo htmlspecialchars($article['slug']); ?>"><?php echo htmlspecialchars($article['judul']); ?></a>
                                    </h3>
                                    <div class="news-meta">
                                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($article['penulis'] ?? 'Anonim'); ?></span>
                                        <span><i class="far fa-clock"></i> <?php echo date('d M Y', strtotime($article['tanggal_publikasi'])); ?></span>
                                    </div>
                                    <p class="news-excerpt"><?php echo htmlspecialchars(substr($article['isi_berita'], 0, 200)); ?>...</p>
                                    <a href="detail_berita.php?slug=<?php echo htmlspecialchars($article['slug']); ?>" class="read-more">Baca Selengkapnya</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="pagination">
                        <?php if ($halaman_saat_ini > 1): ?>
                            <a href="?category=<?php echo htmlspecialchars($category_slug); ?>&page=<?php echo $halaman_saat_ini - 1; ?>"><i class="fas fa-chevron-left"></i> Previous</a>
                        <?php else: ?>
                            <a href="#" class="disabled"><i class="fas fa-chevron-left"></i> Previous</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                            <a href="?category=<?php echo htmlspecialchars($category_slug); ?>&page=<?php echo $i; ?>" class="<?php echo ($i == $halaman_saat_ini) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>

                        <?php if ($halaman_saat_ini < $total_halaman): ?>
                            <a href="?category=<?php echo htmlspecialchars($category_slug); ?>&page=<?php echo $halaman_saat_ini + 1; ?>">Next <i class="fas fa-chevron-right"></i></a>
                        <?php else: ?>
                            <a href="#" class="disabled">Next <i class="fas fa-chevron-right"></i></a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="error-message">
                        <i class="fas fa-newspaper"></i> Tidak ada berita dalam kategori ini saat ini.
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="category-not-found">
                    <h2><i class="fas fa-exclamation-triangle"></i> Kategori Tidak Ditemukan</h2>
                    <p>Kategori yang Anda cari tidak ada atau belum memiliki berita.</p>
                    <a href="index.php" class="back-button">Kembali ke Beranda</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="sidebar">
            <h3><i class="fas fa-cog"></i> Administrasi</h3>
            <ul>
                <li><a href="tambah_berita.php"><i class="fas fa-plus-circle"></i> Tambah Berita Baru</a></li>
                <li><a href="daftar_berita.php"><i class="fas fa-list"></i> Daftar Berita</a></li>
            </ul>
            
            <h3><i class="fas fa-fire"></i> Kategori Populer</h3>
            <ul>
                <?php if (!empty($popular_categories)): ?>
                    <?php foreach ($popular_categories as $category): ?>
                        <li>
                            <a href="kategori.php?category=<?php echo htmlspecialchars($category['slug']); ?>">
                                <i class="fas fa-folder"></i> <?php echo htmlspecialchars($category['nama_kategori']); ?>
                                <span class="category-count"><?php echo $category['jumlah_berita']; ?> berita</span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
            
            <h3><i class="fas fa-history"></i> Berita Terbaru</h3>
            <ul>
                <?php if (!empty($recent_news)): ?>
                    <?php foreach ($recent_news as $news): ?>
                        <li>
                            <a href="detail_berita.php?slug=<?php echo htmlspecialchars($news['slug']); ?>">
                                <i class="fas fa-newspaper"></i> <?php echo htmlspecialchars($news['judul']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <a href="tambah_berita.php" class="add-button" title="Tambah Berita Baru"><i class="fas fa-plus"></i></a>

    <div class="footer">
        <p>&copy; <?php echo date("Y"); ?> NEWSFLIX. All rights reserved. Sumber Berita: Konten Lokal dan Internasional.</p>
        <div class="social-links">
            <a href="#"><i class="fab fa-facebook"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-youtube"></i></a>
        </div>
    </div>

    <script>
        // Netflix-like hover effect for news cards
        const newsCards = document.querySelectorAll('.news-card');
        newsCards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-10px)';
                if (card.querySelector('.news-image')) {
                    card.querySelector('.news-image').style.transform = 'scale(1.05)';
                }
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
                if (card.querySelector('.news-image')) {
                    card.querySelector('.news-image').style.transform = 'scale(1)';
                }
            });
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>