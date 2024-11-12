<?php
include '../config/koneksi.php';

if (isset($_POST['hari']) && isset($_POST['jam'])) {
    $hari = $_POST['hari'];
    $jam = explode('-', $_POST['jam']);
    $jam_mulai = $jam[0];
    $jam_selesai = $jam[1];

    $query_jadwal = "SELECT id_jadwal FROM tb_jadwal WHERE hari = ? AND jam_mulai = ? AND jam_selesai = ?";
    $stmt = $conn->prepare($query_jadwal);
    $stmt->bind_param("sss", $hari, $jam_mulai, $jam_selesai);
    $stmt->execute();
    $result_jadwal = $stmt->get_result();

    if ($row = $result_jadwal->fetch_assoc()) {
        echo $row['id_jadwal'];
    }

    $stmt->close();
}
?>