<?php
// includes/slug.php

function create_slug($string) {
    // Ubah string ke lowercase
    $slug = strtolower($string);
    // Hapus karakter non-alphanumeric kecuali spasi dan tanda hubung
    $slug = preg_replace('/[^a-z0-9 -]/', '', $slug);
    // Ganti spasi dengan tanda hubung
    $slug = str_replace(' ', '-', $slug);
    // Hapus tanda hubung ganda
    $slug = preg_replace('/-+/', '-', $slug);
    // Trim tanda hubung dari awal dan akhir string
    $slug = trim($slug, '-');
    return $slug;
}

// Fungsi opsional untuk memastikan slug unik (jika Anda tidak menyertakan pengecekan di query INSERT)
// Namun, PRIMARY KEY atau UNIQUE constraint di kolom slug sudah cukup untuk mencegah duplikasi.
// Function ini lebih cocok digunakan saat memproses input, sebelum INSERT ke DB.
/*
function generate_unique_slug($string, $table, $koneksi) {
    $slug = create_slug($string);
    $original_slug = $slug;
    $counter = 1;
    while (true) {
        $query = "SELECT COUNT(*) FROM `$table` WHERE `slug` = '$slug'";
        $result = mysqli_query($koneksi, $query);
        $row = mysqli_fetch_array($result);
        if ($row[0] == 0) {
            break; // Slug is unique
        }
        $slug = $original_slug . '-' . $counter++;
    }
    return $slug;
}
*/
?>