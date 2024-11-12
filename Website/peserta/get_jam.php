<?php
include '../config/koneksi.php';

if (isset($_POST['hari'])) {
    $hari = $_POST['hari'];
    $query_jam = "SELECT DISTINCT jam_mulai, jam_selesai FROM tb_jadwal WHERE hari = ?";
    $stmt = $conn->prepare($query_jam);
    $stmt->bind_param("s", $hari);
    $stmt->execute();
    $result_jam = $stmt->get_result();

    while ($jam = $result_jam->fetch_assoc()) {
        echo '<option value="' . $jam['jam_mulai'] . '-' . $jam['jam_selesai'] . '">' . $jam['jam_mulai'] . ' - ' . $jam['jam_selesai'] . '</option>';
    }

    $stmt->close();
}
?>