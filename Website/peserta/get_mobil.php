<?php
include '../config/koneksi.php';

if (isset($_POST['id_jenis_pelatihan'])) {
    $id_jenis_pelatihan = $_POST['id_jenis_pelatihan'];

    // Query untuk mendapatkan data mobil berdasarkan kategori paket kursus
    $query_mobil = "SELECT * FROM tb_mobil WHERE tipe_mobil = (SELECT kategori FROM tb_jenis_pelatihan WHERE id_jenis_pelatihan = $id_jenis_pelatihan)";
    $result_mobil = $conn->query($query_mobil);

    $data = [];
    while ($row = mysqli_fetch_assoc($result_mobil)) {
        $data[] = $row;
    }

    // Mengembalikan data dalam format JSON
    echo json_encode($data);

} else {
    // Jika tidak ada id_jenis_pelatihan yang diterima
    echo json_encode(['error' => 'ID jenis pelatihan tidak ditemukan']);
}

$conn->close();
?>