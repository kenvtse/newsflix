<?php
// tambah_berita.php
require_once 'includes/koneksi.php'; // Hubungkan ke database
require_once 'includes/slug.php';    // Sertakan fungsi slug

$kategori_list = [];
$query_kategori = "SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori ASC";
$result_kategori = mysqli_query($koneksi, $query_kategori);
if ($result_kategori && mysqli_num_rows($result_kategori) > 0) {
    while ($row = mysqli_fetch_assoc($result_kategori)) {
        $kategori_list[] = $row;
    }
}

$message = '';
$message_type = ''; // 'success' or 'error'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil dan sanitasi data dari form
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $isi_berita = mysqli_real_escape_string($koneksi, $_POST['isi_berita']);
    $penulis = mysqli_real_escape_string($koneksi, $_POST['penulis']);
    $id_kategori = mysqli_real_escape_string($koneksi, $_POST['id_kategori']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);

    // Buat slug
    $slug = create_slug($judul);

    // Cek apakah slug sudah ada, jika ya, tambahkan angka (optional, tapi disarankan)
    $original_slug = $slug;
    $counter = 1;
    while (true) {
        $check_slug_query = "SELECT COUNT(*) FROM berita WHERE slug = '$slug'";
        $check_slug_result = mysqli_query($koneksi, $check_slug_query);
        $row_slug_count = mysqli_fetch_array($check_slug_result);
        if ($row_slug_count[0] == 0) {
            break; // Slug is unique
        }
        $slug = $original_slug . '-' . $counter++;
    }

    $gambar_path = '';
    // Proses upload gambar
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "uploads/"; // Pastikan folder 'uploads' ada di root proyek Anda
        $target_file = $target_dir . basename($_FILES["gambar"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $uploadOk = 1;

        // Cek apakah file gambar asli atau palsu
        $check = getimagesize($_FILES["gambar"]["tmp_name"]);
        if($check !== false) {
            $uploadOk = 1;
        } else {
            $message = "File bukan gambar.";
            $message_type = 'error';
            $uploadOk = 0;
        }

        // Cek ukuran file (misal maksimal 5MB)
        if ($_FILES["gambar"]["size"] > 5000000) {
            $message = "Ukuran gambar terlalu besar (maksimal 5MB).";
            $message_type = 'error';
            $uploadOk = 0;
        }

        // Izinkan format file tertentu
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif" ) {
            $message = "Hanya JPG, JPEG, PNG & GIF yang diizinkan.";
            $message_type = 'error';
            $uploadOk = 0;
        }

        // Jika semua OK, coba upload file
        if ($uploadOk == 1) {
            // Generate unique filename
            $new_filename = uniqid() . '.' . $imageFileType;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                $gambar_path = $target_file;
            } else {
                $message = "Terjadi kesalahan saat mengunggah gambar.";
                $message_type = 'error';
                $uploadOk = 0;
            }
        }
    }

    // Jika tidak ada error upload gambar, lanjutkan insert ke database
    if ($message_type !== 'error') {
        $query_insert = "INSERT INTO berita (judul, slug, isi_berita, gambar, penulis, id_kategori, status)
                         VALUES ('$judul', '$slug', '$isi_berita', '$gambar_path', '$penulis', '$id_kategori', '$status')";

        if (mysqli_query($koneksi, $query_insert)) {
            $message = "Berita berhasil ditambahkan!";
            $message_type = 'success';
            // Bersihkan form setelah sukses
            $_POST = array(); // Clear form data
        } else {
            $message = "Error: " . mysqli_error($koneksi);
            $message_type = 'error';
        }
    }
}

mysqli_close($koneksi); // Tutup koneksi
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Berita Baru</title>
    <link href="https://fonts.googleapis.com/css2?family=Netflix+Sans:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --netflix-red: #e50914;
            --netflix-dark: #141414;
            --netflix-gray: #333;
            --netflix-light-gray: #8c8c8c;
            --netflix-white: #fff;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Netflix Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: var(--netflix-dark);
            color: var(--netflix-white);
            line-height: 1.6;
            padding: 0;
            margin: 0;
        }
        
        .container {
            max-width: 1000px;
            margin: 40px auto;
            background-color: rgba(20, 20, 20, 0.9);
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            position: relative;
            overflow: hidden;
        }
        
        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--netflix-red);
        }
        
        h1 {
            text-align: center;
            color: var(--netflix-white);
            margin-bottom: 30px;
            font-weight: 700;
            font-size: 2.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        }
        
        form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group.full-width {
            grid-column: span 2;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--netflix-light-gray);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        input[type="text"],
        textarea,
        select {
            width: 100%;
            padding: 12px 15px;
            background-color: var(--netflix-gray);
            border: 1px solid #444;
            border-radius: 4px;
            color: var(--netflix-white);
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        input[type="text"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: var(--netflix-red);
            box-shadow: 0 0 0 2px rgba(229, 9, 20, 0.3);
        }
        
        textarea {
            resize: vertical;
            min-height: 200px;
        }
        
        .file-upload {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }
        
        .file-upload-btn {
            border: 2px dashed #444;
            color: var(--netflix-light-gray);
            background-color: var(--netflix-gray);
            padding: 40px 20px;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 500;
            text-align: center;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .file-upload-btn:hover {
            border-color: var(--netflix-red);
            color: var(--netflix-white);
        }
        
        .file-upload-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .preview-container {
            margin-top: 15px;
            display: none;
        }
        
        .image-preview {
            max-width: 100%;
            max-height: 300px;
            border-radius: 4px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            display: block;
            margin: 0 auto;
        }
        
        button[type="submit"] {
            background-color: var(--netflix-red);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            grid-column: span 2;
            margin-top: 10px;
        }
        
        button[type="submit"]:hover {
            background-color: #f40612;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(229, 9, 20, 0.4);
        }
        
        .message {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 4px;
            text-align: center;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        .message.success {
            background-color: rgba(0, 200, 83, 0.2);
            color: #00c853;
            border: 1px solid #00c853;
        }
        
        .message.error {
            background-color: rgba(255, 23, 68, 0.2);
            color: #ff1744;
            border: 1px solid #ff1744;
        }
        
        .back-button {
            display: inline-block;
            margin-top: 30px;
            padding: 10px 20px;
            background-color: var(--netflix-gray);
            color: var(--netflix-white);
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-align: center;
            width: 100%;
        }
        
        .back-button:hover {
            background-color: #444;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 20px;
                padding: 30px 20px;
            }
            
            form {
                grid-template-columns: 1fr;
            }
            
            .form-group.full-width {
                grid-column: span 1;
            }
            
            h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Tambah Berita Baru</h1>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="tambah_berita.php" method="POST" enctype="multipart/form-data">
            <div class="form-group full-width">
                <label for="judul">Judul Berita</label>
                <input type="text" id="judul" name="judul" value="<?php echo htmlspecialchars($_POST['judul'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group full-width">
                <label for="isi_berita">Isi Berita</label>
                <textarea id="isi_berita" name="isi_berita" required><?php echo htmlspecialchars($_POST['isi_berita'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group full-width">
                <label for="gambar">Gambar Utama</label>
                <div class="file-upload">
                    <div class="file-upload-btn">Pilih Gambar (Max 5MB)</div>
                    <input type="file" id="gambar" name="gambar" class="file-upload-input" accept="image/*">
                </div>
                <div class="preview-container">
                    <img src="" alt="Preview Gambar" class="image-preview" id="imagePreview">
                </div>
            </div>
            
            <div class="form-group">
                <label for="penulis">Penulis</label>
                <input type="text" id="penulis" name="penulis" value="<?php echo htmlspecialchars($_POST['penulis'] ?? 'Admin'); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="id_kategori">Kategori</label>
                <select id="id_kategori" name="id_kategori" required>
                    <?php if (!empty($kategori_list)): ?>
                        <?php foreach ($kategori_list as $kategori): ?>
                            <option value="<?php echo htmlspecialchars($kategori['id_kategori']); ?>"
                                <?php echo (isset($_POST['id_kategori']) && $_POST['id_kategori'] == $kategori['id_kategori']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($kategori['nama_kategori']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="">Tidak ada kategori ditemukan</option>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="draft" <?php echo (isset($_POST['status']) && $_POST['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                    <option value="published" <?php echo (isset($_POST['status']) && $_POST['status'] == 'published') ? 'selected' : ''; ?>>Published</option>
                </select>
            </div>
            
            <button type="submit">Tambah Berita</button>
        </form>
        
        <a href="index.php" class="back-button">Kembali ke Beranda</a>
    </div>

    <script>
        // Image preview functionality
        const fileUploadInput = document.querySelector('.file-upload-input');
        const imagePreview = document.getElementById('imagePreview');
        const previewContainer = document.querySelector('.preview-container');
        
        fileUploadInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(event) {
                    imagePreview.src = event.target.result;
                    previewContainer.style.display = 'block';
                }
                
                reader.readAsDataURL(file);
            } else {
                imagePreview.src = '';
                previewContainer.style.display = 'none';
            }
        });
    </script>
</body>
</html>