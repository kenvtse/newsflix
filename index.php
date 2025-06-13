<?php
// index.php
require_once 'includes/koneksi.php'; // Hubungkan ke database
require_once 'includes/slug.php';    // Sertakan fungsi slug

$berita = [];
$limit = 6; // Jumlah berita per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Query untuk mengambil total berita
$query_total = "SELECT COUNT(*) AS total FROM berita WHERE status = 'published'";
$result_total = mysqli_query($koneksi, $query_total);
$row_total = mysqli_fetch_assoc($result_total);
$total_berita = $row_total['total'];
$total_pages = ceil($total_berita / $limit);

// Query untuk mengambil berita dengan pagination
$query_berita = "SELECT b.id_berita, b.judul, b.slug, LEFT(b.isi_berita, 200) AS ringkasan, b.gambar, b.penulis, k.nama_kategori, k.slug AS kategori_slug, b.tanggal_publikasi
                 FROM berita b
                 LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
                 WHERE b.status = 'published'
                 ORDER BY b.tanggal_publikasi DESC
                 LIMIT $limit OFFSET $offset";
$result_berita = mysqli_query($koneksi, $query_berita);

if ($result_berita && mysqli_num_rows($result_berita) > 0) {
    while ($row = mysqli_fetch_assoc($result_berita)) {
        $berita[] = $row;
    }
} else {
    $error_message = "Tidak ada berita yang ditemukan.";
}

// Query untuk hero section (ambil 5 berita terbaru)
$hero_articles = [];
// Pastikan koneksi masih aktif sebelum query ini
if (isset($koneksi)) {
    $query_hero = "SELECT id_berita, judul, slug, gambar, isi_berita FROM berita WHERE status = 'published' ORDER BY tanggal_publikasi DESC LIMIT 5";
    $result_hero = mysqli_query($koneksi, $query_hero);

    if ($result_hero && mysqli_num_rows($result_hero) > 0) {
        while ($row = mysqli_fetch_assoc($result_hero)) {
            $hero_articles[] = $row;
        }
    }
}


// Query untuk kategori populer
$query_popular_categories = "SELECT k.nama_kategori, k.slug, COUNT(b.id_berita) AS jumlah_berita 
                             FROM kategori k 
                             LEFT JOIN berita b ON k.id_kategori = b.id_kategori 
                             GROUP BY k.id_kategori 
                             ORDER BY jumlah_berita DESC 
                             LIMIT 5";
$result_popular_categories = mysqli_query($koneksi, $query_popular_categories);
$popular_categories = [];
if ($result_popular_categories) {
    while ($row = mysqli_fetch_assoc($result_popular_categories)) {
        $popular_categories[] = $row;
    }
}

mysqli_close($koneksi); // Tutup koneksi (pastikan semua query di atasnya sudah selesai)
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Terbaru - NewsFlix</title>
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

        /* Hero Carousel Styles */
        .hero-carousel {
            position: relative;
            height: 70vh; /* Sesuaikan tinggi sesuai keinginan */
            overflow: hidden;
            margin-bottom: 50px;
            background-color: var(--netflix-black); /* Fallback background */
        }

        .carousel-inner {
            display: flex;
            height: 100%;
            transition: transform 0.5s ease-in-out;
        }

        .carousel-item {
            min-width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 0 20px;
            color: var(--netflix-white);
            position: relative;
            /* Efek overlay untuk teks agar lebih terbaca */
            background-color: rgba(0,0,0,0.5); /* Warna dasar untuk overlay */
            background-blend-mode: overlay; /* Blend mode untuk mencampur warna dengan gambar */
        }

        /* Overlay tambahan untuk gambar latar belakang */
        .carousel-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.0) 50%, rgba(0,0,0,0.8) 100%);
            z-index: 1; /* Pastikan overlay di atas gambar tapi di bawah teks */
        }


        .carousel-item .hero-content {
            position: relative; /* Penting agar z-index hero-content lebih tinggi dari overlay */
            z-index: 2;
            max-width: 800px;
            padding: 20px;
        }

        .carousel-item h1 {
            font-size: 3.5rem;
            color: var(--netflix-white);
            margin-bottom: 20px;
            text-shadow: 2px 2px 5px rgba(0,0,0,0.8);
        }

        .carousel-item h1 a {
            color: inherit; /* agar warna link sama dengan teks h1 */
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .carousel-item h1 a:hover {
            color: var(--netflix-red);
        }

        .carousel-item p {
            font-size: 1.5rem;
            color: var(--netflix-light-gray);
            margin-bottom: 30px;
        }

        .read-more-hero {
            display: inline-block;
            padding: 12px 25px;
            background-color: var(--netflix-red);
            color: var(--netflix-white);
            text-decoration: none;
            border-radius: 5px;
            font-weight: 700;
            transition: background-color 0.3s ease, transform 0.3s ease;
            box-shadow: 0 4px 15px rgba(229, 9, 20, 0.4);
        }

        .read-more-hero:hover {
            background-color: var(--netflix-red-dark);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(229, 9, 20, 0.6);
        }

        .carousel-control {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(0,0,0,0.5);
            color: var(--netflix-white);
            border: none;
            padding: 15px;
            cursor: pointer;
            font-size: 2rem;
            z-index: 10;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .carousel-control:hover {
            background-color: rgba(0,0,0,0.8);
            transform: translateY(-50%) scale(1.1);
        }

        .carousel-control.prev {
            left: 20px;
        }

        .carousel-control.next {
            right: 20px;
        }

        /* Main Content Container */
        .main-container {
            max-width: 1400px;
            margin: 0 auto 50px;
            padding: 0 50px;
        }

        .section-title {
            font-size: 1.8rem;
            color: var(--netflix-white);
            margin-bottom: 30px;
            padding-left: 15px;
            border-left: 5px solid var(--netflix-red);
        }

        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin: 40px 0;
            padding: 0 20px;
        }

        .news-card {
            background-color: var(--netflix-dark-gray);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
            border: 1px solid var(--netflix-gray);
            transform: scale(0.98);
        }

        .news-card:hover {
            transform: scale(1.03) translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.7);
            z-index: 10;
            border-color: var(--netflix-red);
        }

        /* 16:9 Image Container (Already optimized for news websites) */
        .news-image-container {
            position: relative;
            width: 100%;
            padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
            overflow: hidden;
            border-radius: 8px 8px 0 0;
            background-color: #1a1a1a;
            transition: all 0.3s ease;
        }

        .news-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover; /* Ensures the image covers the area without distortion */
            transition: transform 0.5s ease;
        }

        .news-card:hover .news-image {
            transform: scale(1.08);
        }

        .news-content {
            padding: 20px;
            position: relative;
            z-index: 2;
            background: linear-gradient(to top, rgba(20, 20, 20, 0.9) 60%, transparent);
        }

        .news-title {
            font-size: 1.25rem;
            color: var(--netflix-white);
            margin-bottom: 12px;
            line-height: 1.4;
            font-weight: 700;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 3.5em;
        }

        .news-title a {
            color: var(--netflix-white);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .news-title a:hover {
            color: var(--netflix-red);
        }

        .news-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 15px;
            font-size: 0.85rem;
            color: var(--netflix-text-gray);
        }

        .news-meta span {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .news-meta i {
            color: var(--netflix-red);
            font-size: 0.9em;
        }

        .news-excerpt {
            color: var(--netflix-light-gray);
            margin-bottom: 20px;
            font-size: 0.95rem;
            line-height: 1.6;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .read-more {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            background-color: var(--netflix-red);
            color: var(--netflix-white);
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            transition: all 0.3s ease;
            gap: 8px;
            width: 100%;
            text-align: center;
        }

        .read-more:hover {
            background-color: var(--netflix-red-dark);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(229, 9, 20, 0.6);
        }

        /* Popular Categories */
        .categories-container {
            background-color: var(--netflix-dark-gray);
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 50px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            border: 1px solid var(--netflix-gray);
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .category-card {
            background-color: var(--netflix-gray);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .category-card:hover {
            background-color: var(--netflix-red);
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(229, 9, 20, 0.5);
        }

        .category-card a {
            color: var(--netflix-white);
            text-decoration: none;
            font-weight: 600;
            display: block;
        }

        .category-count {
            display: block;
            margin-top: 10px;
            font-size: 0.9rem;
            color: var(--netflix-light-gray);
        }
        .category-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background-color: var(--netflix-red);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 3;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
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

        /* Responsive Adjustments */
        @media (max-width: 1200px) {
            .news-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }

        @media (max-width: 992px) {
            .news-grid {
                grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
                gap: 20px;
            }
            
            /* Carousel responsive */
            .carousel-item h1 {
                font-size: 3rem;
            }
            .carousel-item p {
                font-size: 1.3rem;
            }
            .carousel-control {
                width: 50px;
                height: 50px;
                font-size: 1.8rem;
            }
        }

        @media (max-width: 768px) {
            .news-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                padding: 0 10px;
            }
            
            .news-content {
                padding: 15px;
            }
            
            .news-title {
                font-size: 1.1rem;
                min-height: 3em;
            }

            /* Carousel responsive */
            .hero-carousel {
                height: 60vh;
            }
            .carousel-item h1 {
                font-size: 2.5rem;
            }
            .carousel-item p {
                font-size: 1rem;
            }
            .carousel-control {
                width: 40px;
                height: 40px;
                font-size: 1.5rem;
                padding: 10px;
            }
        }

        @media (max-width: 576px) {
            .news-grid {
                grid-template-columns: 1fr;
                max-width: 400px;
                margin: 30px auto;
            }
            
            .news-title {
                font-size: 1.3rem;
            }

            /* Carousel responsive */
            .hero-carousel {
                height: 50vh;
            }
            .carousel-item h1 {
                font-size: 1.8rem;
                margin-bottom: 10px;
            }
            .carousel-item p {
                font-size: 0.9rem;
                margin-bottom: 20px;
            }
            .read-more-hero {
                padding: 8px 15px;
                font-size: 0.9rem;
            }
            .carousel-control {
                display: none; /* Sembunyikan tombol di layar sangat kecil */
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="index.php" class="logo">NEWSFLIX</a>
        <nav class="navbar">
            <ul>
                <li><a href="index.php" class="active">Beranda</a></li>
                <?php if (!empty($popular_categories)): ?>
                    <?php foreach ($popular_categories as $category): ?>
                        <li><a href="kategori.php?category=<?php echo htmlspecialchars($category['slug']); ?>"><?php echo htmlspecialchars($category['nama_kategori']); ?></a></li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <section class="hero-carousel">
        <div class="carousel-inner">
            <?php if (!empty($hero_articles)): ?>
                <?php foreach ($hero_articles as $index => $article): ?>
                    <div class="carousel-item <?php echo ($index === 0) ? 'active' : ''; ?>" 
                         style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('uploads/<?php echo htmlspecialchars($article['gambar']); ?>');">
                        <div class="hero-content">
                            <h1><a href="detail_berita.php?slug=<?php echo htmlspecialchars($article['slug']); ?>"><?php echo htmlspecialchars($article['judul']); ?></a></h1>
                            <p><?php echo htmlspecialchars(substr($article['isi_berita'], 0, 150)); ?>...</p>
                            <a href="detail_berita.php?slug=<?php echo htmlspecialchars($article['slug']); ?>" class="read-more-hero">Baca Selengkapnya</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="carousel-item active" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://assets.nflxext.com/ffe/siteui/vlv3/9d3533b2-0e2b-40b2-95e0-ecd7979cc88b/2b5a349c-6651-4154-80ed-1a9a3f2bf2a5/ID-id-20240311-popsignuptwoweeks-perspective_alpha_website_large.jpg');">
                    <div class="hero-content">
                        <h1>Berita Terkini & Terupdate</h1>
                        <p>Dapatkan informasi terbaru dari berbagai kategori dengan gaya Newsflix</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php if (count($hero_articles) > 1): // Tampilkan tombol navigasi hanya jika ada lebih dari 1 berita ?>
            <button class="carousel-control prev" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
            <button class="carousel-control next" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
        <?php endif; ?>
    </section>

    <div class="main-container">
        <h2 class="section-title">Berita Terbaru</h2>
        
        <?php if (!empty($berita)): ?>
            <div class="news-grid">
                <?php foreach ($berita as $item): ?>
                    <div class="news-card">
                        <div class="news-image-container"> <?php if ($item['gambar']): ?>
                                <img src="uploads/<?php echo htmlspecialchars($item['gambar']); ?>" alt="<?php echo htmlspecialchars($item['judul']); ?>" class="news-image">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/600x400?text=NEWSFLIX" alt="Placeholder" class="news-image">
                            <?php endif; ?>
                        </div>
                        <div class="news-content">
                            <h3 class="news-title">
                                <a href="detail_berita.php?slug=<?php echo htmlspecialchars($item['slug']); ?>"><?php echo htmlspecialchars($item['judul']); ?></a>
                            </h3>
                            <div class="news-meta">
                                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($item['penulis'] ?? 'Anonim'); ?></span>
                                <span><i class="fas fa-tag"></i> 
                                    <a href="kategori.php?category=<?php echo htmlspecialchars($item['kategori_slug'] ?? ''); ?>">
                                        <?php echo htmlspecialchars($item['nama_kategori'] ?? 'Umum'); ?>
                                    </a>
                                </span>
                                <span><i class="far fa-clock"></i> <?php echo date('d M Y', strtotime($item['tanggal_publikasi'])); ?></span>
                            </div>
                            <p class="news-excerpt"><?php echo htmlspecialchars($item['ringkasan']); ?>...</p>
                            <a href="detail_berita.php?slug=<?php echo htmlspecialchars($item['slug']); ?>" class="read-more">Baca Selengkapnya</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>"><i class="fas fa-chevron-left"></i> Previous</a>
                <?php else: ?>
                    <a href="#" class="disabled"><i class="fas fa-chevron-left"></i> Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>">Next <i class="fas fa-chevron-right"></i></a>
                <? else: ?>
                    <a href="#" class="disabled">Next <i class="fas fa-chevron-right"></i></a>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($popular_categories)): ?>
            <h2 class="section-title">Kategori Populer</h2>
            <div class="categories-container">
                <div class="categories-grid">
                    <?php foreach ($popular_categories as $category): ?>
                        <div class="category-card">
                            <a href="kategori.php?category=<?php echo htmlspecialchars($category['slug']); ?>">
                                <?php echo htmlspecialchars($category['nama_kategori']); ?>
                                <span class="category-count"><?php echo $category['jumlah_berita']; ?> Berita</span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
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
                card.style.transform = 'scale(1.03) translateY(-10px)'; // Combined transformation
                if (card.querySelector('.news-image')) {
                    card.querySelector('.news-image').style.transform = 'scale(1.08)';
                }
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'scale(0.98) translateY(0)'; // Reset to initial state
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

        /* Script JavaScript untuk Hero Carousel */
        const carouselInner = document.querySelector('.carousel-inner');
        const carouselItems = document.querySelectorAll('.carousel-item');
        const prevBtn = document.querySelector('.carousel-control.prev');
        const nextBtn = document.querySelector('.carousel-control.next');

        let currentIndex = 0;
        let autoSlideInterval;
        const slideDuration = 5000; // 5 detik per slide

        function showSlide(index) {
            if (index >= carouselItems.length) {
                currentIndex = 0;
            } else if (index < 0) {
                currentIndex = carouselItems.length - 1;
            } else {
                currentIndex = index;
            }
            const offset = -currentIndex * 100;
            carouselInner.style.transform = `translateX(${offset}%)`;

            // Update active class for visual indication (if you add indicators)
            carouselItems.forEach((item, i) => {
                item.classList.remove('active');
                if (i === currentIndex) {
                    item.classList.add('active');
                }
            });
        }

        function nextSlide() {
            showSlide(currentIndex + 1);
            resetAutoSlide();
        }

        function prevSlide() {
            showSlide(currentIndex - 1);
            resetAutoSlide();
        }

        function startAutoSlide() {
            autoSlideInterval = setInterval(nextSlide, slideDuration);
        }

        function resetAutoSlide() {
            clearInterval(autoSlideInterval);
            startAutoSlide();
        }

        // Event Listeners for controls
        if (prevBtn) { // Pastikan tombol ada sebelum menambahkan event listener
            prevBtn.addEventListener('click', prevSlide);
        }
        if (nextBtn) { // Pastikan tombol ada sebelum menambahkan event listener
            nextBtn.addEventListener('click', nextSlide);
        }

        // Start auto slide when the page loads
        if (carouselItems.length > 1) { // Hanya jalankan auto-slide jika ada lebih dari 1 item
            startAutoSlide();
        }
    </script>
</body>
</html>