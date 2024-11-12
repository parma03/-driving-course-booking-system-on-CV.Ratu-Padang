<?php
include '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pelatihan'])) {
    $id_pelatihan = intval($_POST['id_pelatihan']);

    $query_soal = "SELECT tb_soal.tipe_soal, tb_soal.nama_soal, tb_jawaban.jawaban 
                   FROM tb_soal 
                   LEFT JOIN tb_jawaban ON tb_jawaban.id_soal = tb_soal.id_soal 
                   AND tb_jawaban.id_pelatihan = $id_pelatihan 
                   WHERE tb_jawaban.id_pelatihan = $id_pelatihan";
    $result_soal = $conn->query($query_soal);

    $output = '';
    $noo = 1;
    $soal_tersedia = false;

    while ($soal = mysqli_fetch_array($result_soal)) {
        $soal_tersedia = true;
        $jawaban = !empty($soal['jawaban']) ? $soal['jawaban'] : 'belum diisi';
        $output .= "<p>{$noo}. {$soal['nama_soal']}</p><br>";
        $output .= "<p><i>Jawaban: {$jawaban}</i></p><br>";
        $noo++;
    }

    if (!$soal_tersedia) {
        $output = "<p>SOAL BELUM DIKERJAKAN</p>";
    }

    echo $output;
}
?>