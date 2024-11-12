<?php
include '../config/koneksi.php';

$id_pelatihan = $_GET['id_pelatihan'];

$query = "SELECT id_absensi, keterangan_absensi, waktu_absensi FROM tb_absensi WHERE id_pelatihan = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $id_pelatihan);
$stmt->execute();
$result = $stmt->get_result();

$absensi_data = array();
while ($row = $result->fetch_assoc()) {
    $absensi_data[] = $row;
}

echo json_encode($absensi_data);
?>