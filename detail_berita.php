<?php
// detail_berita.php
include 'includes/koneksi.php'; // Sertakan koneksi database, harus di awal

$slug = $_GET['slug'] ?? '';

$berita = null;
if (!empty($slug)) {
    // Ambil juga slug kategori untuk link di meta
    $query = "SELECT b.*, k.nama_kategori, k.slug AS kategori_slug 
              FROM berita b 
              JOIN kategori k ON b.id_kategori = k.id_kategori 
              WHERE b.slug = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "s", $slug);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $berita = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

// Jika berita tidak ditemukan
if (!$berita) {
    // Pesan error dengan gaya Netflix premium
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Berita Tidak Ditemukan</title>';
    echo '<link href="https://fonts.googleapis.com/css2?family=Netflix+Sans:wght@300;400;700;900&display=swap" rel="stylesheet">';
    echo '<style>
            body { 
                font-family: "Netflix Sans", sans-serif; 
                background: #141414 url("https://assets.nflxext.com/ffe/siteui/vlv3/9d3533b2-0e2b-40b2-95e0-ecd7979cc88b/2b5a349c-6651-4154-80ed-1a9a3f2bf2a5/ID-id-20240311-popsignuptwoweeks-perspective_alpha_website_large.jpg") no-repeat center center fixed; 
                background-size: cover;
                color: #fff; 
                text-align: center; 
                margin: 0;
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .error-overlay {
                background-color: rgba(0,0,0,0.8);
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 1;
            }
            .error-container { 
                position: relative;
                z-index: 2;
                max-width: 800px; 
                margin: 0 auto; 
                padding: 60px; 
            }
            .error-message { 
                color: #e50914; 
                font-size: 4em; 
                margin-bottom: 30px; 
                font-weight: 900; 
                text-shadow: 2px 2px 10px rgba(0,0,0,0.8);
            }
            .error-description { 
                font-size: 1.5em; 
                color: #d2d2d2; 
                margin-bottom: 50px; 
                line-height: 1.6;
            }
            .error-link { 
                display: inline-block; 
                background-color: #e50914; 
                color: white; 
                padding: 18px 40px; 
                border-radius: 4px; 
                text-decoration: none; 
                font-weight: 700; 
                font-size: 1.2em;
                transition: all 0.3s ease; 
                box-shadow: 0 4px 20px rgba(229, 9, 20, 0.6); 
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            .error-link:hover { 
                background-color: #f40612; 
                transform: scale(1.05); 
                box-shadow: 0 6px 25px rgba(229, 9, 20, 0.8); 
            }
            .netflix-logo {
                position: absolute;
                top: 30px;
                left: 50px;
                fill: #e50914;
                width: 120px;
                height: auto;
            }
          </style></head><body>';
    echo '<div class="error-overlay"></div>';
    echo '<svg class="netflix-logo" viewBox="0 0 111 30"><path d="M105.06233,14.2806261 L110.999156,30 C109.249227,29.7497422 107.500234,29.4366857 105.718437,29.1554972 L102.374168,20.4686475 L98.9371075,28.4375293 C97.2499766,28.1563408 95.5928391,28.061674 93.9057081,27.8432843 L99.9372012,14.0931671 L94.4680851,-5.68434189e-14 L99.5313525,-5.68434189e-14 L102.593495,7.87421502 L105.874965,-5.68434189e-14 L110.999156,-5.68434189e-14 L105.06233,14.2806261 Z M90.4686475,-5.68434189e-14 L85.8749649,-5.68434189e-14 L85.8749649,27.2499766 C87.3746368,27.3437061 88.9371075,27.4055675 90.4686475,27.5930265 L90.4686475,-5.68434189e-14 Z M81.9055207,26.93692 C77.7186241,26.6557316 73.5307901,26.4064111 69.250164,26.3117443 L69.250164,-5.68434189e-14 L73.9366389,-5.68434189e-14 L73.9366389,21.8745899 C76.6248008,21.9373887 79.3120255,22.1557784 81.9055207,22.2804387 L81.9055207,26.93692 Z M64.2496954,10.6561065 L64.2496954,15.3435186 L57.8442216,15.3435186 L57.8442216,25.9996251 L53.2186709,25.9996251 L53.2186709,-5.68434189e-14 L66.3436123,-5.68434189e-14 L66.3436123,4.68741213 L57.8442216,4.68741213 L57.8442216,10.6561065 L64.2496954,10.6561065 Z M45.3435186,4.68741213 L45.3435186,26.2498828 C43.7810479,26.2498828 42.1876465,26.2498828 40.6561065,26.3117443 L40.6561065,4.68741213 L35.8121661,4.68741213 L35.8121661,-5.68434189e-14 L50.2183897,-5.68434189e-14 L50.2183897,4.68741213 L45.3435186,4.68741213 Z M30.749836,15.5928391 C28.687787,15.5928391 26.2498828,15.5928391 24.4999531,15.6875059 L24.4999531,22.6562939 C27.2499766,22.4678976 30,22.2495079 32.7809542,22.1557784 L32.7809542,26.6557316 L19.812541,27.6876933 L19.812541,-5.68434189e-14 L32.7809542,-5.68434189e-14 L32.7809542,4.68741213 L24.4999531,4.68741213 L24.4999531,10.9991564 C26.3126816,10.9991564 29.0936358,10.9054269 30.749836,10.9054269 L30.749836,15.5928391 Z M4.78114163,12.9684132 L4.78114163,29.3429562 C3.09401069,29.5313525 1.59340144,29.7497422 0,30 L0,-5.68434189e-14 L4.4690224,-5.68434189e-14 L10.562377,17.0315868 L10.562377,-5.68434189e-14 L15.2497891,-5.68434189e-14 L15.2497891,28.061674 C13.5935889,28.3437998 11.906458,28.4375293 10.1246602,28.6868498 L4.78114163,12.9684132 Z"></path></svg>';
    echo '<div class="error-container"><div class="error-message">404</div>';
    echo '<p class="error-description">Konten yang Anda cari tidak tersedia atau mungkin sudah dihapus.</p>';
    echo '<a href="index.php" class="error-link">Kembali ke Beranda</a></div>';
    echo '</body></html>';
    // Tutup koneksi sebelum keluar
    if (isset($koneksi)) {
        mysqli_close($koneksi);
    }
    exit();
}

// Judul halaman dinamis
$page_title = $berita['judul'];
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

        /* Main Content Container */
        .container {
            max-width: 1400px;
            margin: 120px auto 50px;
            padding: 0 50px;
            display: flex;
            gap: 40px;
        }

        /* Main Content Area */
        .main-content {
            flex: 3;
            min-width: 0;
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

        /* News Detail Container */
        .detail-container {
            background-color: var(--netflix-dark-gray);
            border-radius: 8px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.7);
            padding: 60px;
            margin-bottom: 40px;
            border: 1px solid var(--netflix-gray);
            position: relative;
            overflow: hidden;
        }

        .detail-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--netflix-red), var(--netflix-blue));
        }

        .detail-title {
            font-size: 3.2rem;
            color: var(--netflix-white);
            margin-bottom: 30px;
            line-height: 1.2;
            font-weight: 900;
            text-shadow: 2px 2px 5px rgba(0,0,0,0.5);
            position: relative;
            padding-bottom: 20px;
        }

        .detail-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100px;
            height: 4px;
            background: var(--netflix-red);
            border-radius: 2px;
        }

        .detail-meta {
            font-size: 1.1rem;
            color: var(--netflix-text-gray);
            margin-bottom: 40px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: center;
        }

        .detail-meta span {
            background-color: rgba(255,255,255,0.1);
            padding: 8px 15px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }

        .detail-meta i {
            color: var(--netflix-red);
            font-size: 0.9em;
        }

        .detail-meta a {
            color: var(--netflix-red);
            text-decoration: none;
            font-weight: 700;
            transition: color 0.3s ease;
        }

        .detail-meta a:hover {
            color: var(--netflix-white);
            text-decoration: underline;
        }

       /* Dalam bagian <style> di detail_berita.php */

.detail-image-container {
    position: relative;
    width: 100%; /* Pastikan mengisi lebar parent */
    height: 450px; /* <--- PENTING: Tetapkan tinggi yang konsisten */
    margin-bottom: 50px;
    border-radius: 8px;
    overflow: hidden; /* Penting untuk memotong bagian gambar yang berlebih */
    box-shadow: 0 15px 30px rgba(0,0,0,0.5);
    transition: transform 0.5s ease;
    /* Tambahan untuk placeholder jika gambar tidak ada */
    display: flex; /* Untuk memposisikan placeholder teks di tengah jika ada */
    justify-content: center;
    align-items: center;
    background-color: var(--netflix-gray); /* Warna latar belakang untuk kotak gambar */
}

.detail-image-container:hover {
    transform: scale(1.02);
}

.detail-image {
    width: 100%;
    height: 100%; /* <--- PENTING: Gambar mengisi penuh container */
    object-fit: cover; /* <--- PENTING: Ini akan memotong gambar agar sesuai tanpa distorsi */
    display: block;
    transition: transform 0.5s ease;
}

.detail-image-container:hover .detail-image {
    transform: scale(1.05);
}

/* Tambahan untuk placeholder image agar terlihat lebih baik */
.detail-image[src*="placeholder"] {
    object-fit: contain; /* Agar teks placeholder tidak terpotong */
    height: auto; /* Biarkan tinggi placeholder auto untuk teks */
    width: auto; /* Biarkan lebar placeholder auto untuk teks */
    max-width: 90%;
    max-height: 90%;
    filter: grayscale(100%); /* Contoh: berikan efek grayscale pada placeholder */
}
        .detail-content {
            font-size: 1.2rem;
            line-height: 1.8;
            color: var(--netflix-light-gray);
        }

        .detail-content p {
            margin-bottom: 1.8em;
            text-align: justify;
        }

        .detail-content p:first-child::first-letter {
            float: left;
            font-size: 4em;
            line-height: 0.8;
            margin-right: 15px;
            color: var(--netflix-red);
            font-weight: 700;
            margin-top: 10px;
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

        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: var(--netflix-red);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 1.5rem;
            box-shadow: 0 5px 15px rgba(229, 9, 20, 0.5);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 999;
        }

        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            background-color: var(--netflix-red-dark);
            transform: translateY(-5px);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .container {
                flex-direction: column;
                gap: 30px;
                padding: 0 30px;
            }
            
            .sidebar {
                position: static;
                order: -1;
                width: 100%;
            }
            
            .detail-title {
                font-size: 2.8rem;
            }
            
            .detail-container {
                padding: 40px;
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
            
            .detail-title {
                font-size: 2.5rem;
            }
            
            .detail-content {
                font-size: 1.1rem;
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
            
            .container {
                margin-top: 150px;
                padding: 0 20px;
            }
            
            .detail-container {
                padding: 30px;
            }
            
            .detail-title {
                font-size: 2.2rem;
            }
            
            .detail-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }

        @media (max-width: 576px) {
            .container {
                margin-top: 120px;
                padding: 0 15px;
            }
            
            .detail-container {
                padding: 25px 20px;
            }
            
            .detail-title {
                font-size: 1.8rem;
                padding-bottom: 15px;
            }
            
            .detail-content {
                font-size: 1rem;
            }
            
            .detail-content p:first-child::first-letter {
                font-size: 3em;
            }
            
            .sidebar {
                padding: 20px;
            }
            
            .sidebar h3 {
                font-size: 1.3rem;
            }
            
            .sidebar ul li a {
                padding: 12px 15px;
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
                <?php
                // Untuk navigasi kategori, ambil dari database
                if (isset($koneksi)) {
                    $query_nav_kategori = "SELECT nama_kategori, slug FROM kategori ORDER BY nama_kategori ASC";
                    $result_nav_kategori = mysqli_query($koneksi, $query_nav_kategori);
                    if ($result_nav_kategori) {
                        while ($kategori_nav = mysqli_fetch_assoc($result_nav_kategori)):
                            $is_active_category = (basename($_SERVER['PHP_SELF']) == 'kategori.php' && ($_GET['category'] ?? '') == $kategori_nav['slug']);
                            echo '<li><a href="kategori.php?category=' . htmlspecialchars($kategori_nav['slug']) . '" class="' . ($is_active_category ? 'active' : '') . '">' . htmlspecialchars($kategori_nav['nama_kategori']) . '</a></li>';
                        endwhile;
                    }
                }
                ?>
            </ul>
        </nav>
    </div>

    <div class="container">
        <div class="main-content">
            <div class="detail-container">
                <h1 class="detail-title"><?php echo htmlspecialchars($berita['judul']); ?></h1>
                <div class="detail-meta">
                    <span><i class="fas fa-user"></i> Penulis: <?php echo htmlspecialchars($berita['penulis']); ?></span>
                    <span><i class="fas fa-tag"></i> Kategori: <a href="kategori.php?category=<?php echo htmlspecialchars($berita['kategori_slug']); ?>"><?php echo htmlspecialchars($berita['nama_kategori']); ?></a></span>
                    <span><i class="far fa-clock"></i> Tanggal: <?php echo date("d M Y H:i", strtotime($berita['tanggal_publikasi'])); ?> WIB</span>
                </div>
                <?php if (!empty($berita['gambar'])): ?>
                    <div class="detail-image-container">
                        <img src="uploads/<?php echo htmlspecialchars($berita['gambar']); ?>" alt="<?php echo htmlspecialchars($berita['judul']); ?>" class="main-image">
                    </div>
                <?php endif; ?>
                <div class="detail-content">
                    <?php echo nl2br(htmlspecialchars($berita['isi_berita'])); ?>
                </div>
            </div>
        </div>

        <div class="sidebar">
            <h3><i class="fas fa-cog"></i> Administrasi</h3>
            <ul>
                <li><a href="tambah_berita.php"><i class="fas fa-plus-circle"></i> Tambah Berita Baru</a></li>
                <li><a href="edit_berita.php?slug=<?php echo htmlspecialchars($berita['slug']); ?>"><i class="fas fa-edit"></i> Edit Berita Ini</a></li>
                <li><a href="daftar_berita.php"><i class="fas fa-list"></i> Daftar Berita</a></li>
            </ul>
            <h3><i class="fas fa-fire"></i> Kategori Populer</h3>
            <ul>
                <?php
                if (isset($koneksi)) {
                    $query_sidebar_kategori = "SELECT nama_kategori, slug FROM kategori ORDER BY RAND() LIMIT 5";
                    $result_sidebar_kategori = mysqli_query($koneksi, $query_sidebar_kategori);
                    if ($result_sidebar_kategori) {
                        while ($kategori_sidebar = mysqli_fetch_assoc($result_sidebar_kategori)):
                            echo '<li><a href="kategori.php?category=' . htmlspecialchars($kategori_sidebar['slug']) . '"><i class="fas fa-folder"></i> ' . htmlspecialchars($kategori_sidebar['nama_kategori']) . '</a></li>';
                        endwhile;
                    }
                }
                ?>
            </ul>
            <h3><i class="fas fa-trending-up"></i> Berita Terpopuler</h3>
            <ul>
                <?php
                if (isset($koneksi)) {
                    $query_popular = "SELECT judul, slug FROM berita ORDER BY views DESC LIMIT 3";
                    $result_popular = mysqli_query($koneksi, $query_popular);
                    if ($result_popular) {
                        while ($popular = mysqli_fetch_assoc($result_popular)):
                            echo '<li><a href="detail_berita.php?slug=' . htmlspecialchars($popular['slug']) . '"><i class="fas fa-newspaper"></i> ' . htmlspecialchars($popular['judul']) . '</a></li>';
                        endwhile;
                    }
                }
                ?>
            </ul>
        </div>
    </div>

    <div class="footer">
        <p>&copy; <?php echo date("Y"); ?> NEWSFLIX. All rights reserved. Sumber Berita: Konten Lokal dan Internasional.</p>
        <div class="social-links">
            <a href="#"><i class="fab fa-facebook"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-youtube"></i></a>
        </div>
    </div>

    <a href="#" class="back-to-top" id="backToTop"><i class="fas fa-arrow-up"></i></a>

    <script>
        // Back to top button
        const backToTopButton = document.getElementById('backToTop');
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.add('visible');
            } else {
                backToTopButton.classList.remove('visible');
            }
        });
        
        backToTopButton.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Netflix-like hover effect for images
        const images = document.querySelectorAll('.detail-image-container');
        images.forEach(image => {
            image.addEventListener('mouseenter', () => {
                image.style.transform = 'scale(1.02)';
                image.querySelector('.detail-image').style.transform = 'scale(1.05)';
            });
            
            image.addEventListener('mouseleave', () => {
                image.style.transform = 'scale(1)';
                image.querySelector('.detail-image').style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>

<?php
// Tutup koneksi database jika belum ditutup
if (isset($koneksi)) {
    mysqli_close($koneksi);
}
?>