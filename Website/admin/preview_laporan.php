<?php
session_start();
include '../config/koneksi.php';

if (!isset($_POST['tgl_awal']) || !isset($_POST['tgl_akhir'])) {
    echo "Tanggal awal dan akhir tidak ditemukan.";
    exit();
}

$tgl_awal = $_POST['tgl_awal'];
$tgl_akhir = $_POST['tgl_akhir'];

$query = "SELECT tb_pelatihan.*, tb_konsumen.name_konsumen, tb_instruktur.name_instruktur, tb_jenis_pelatihan.nama_jenis, tb_jadwal.hari, tb_jadwal.jam_mulai, tb_jadwal.jam_selesai
          FROM tb_pelatihan
          INNER JOIN tb_konsumen ON tb_pelatihan.id_konsumen = tb_konsumen.id_konsumen
          INNER JOIN tb_instruktur ON tb_pelatihan.id_instruktur = tb_instruktur.id_instruktur
          INNER JOIN tb_jenis_pelatihan ON tb_pelatihan.id_jenis_pelatihan = tb_jenis_pelatihan.id_jenis_pelatihan
          INNER JOIN tb_jadwal ON tb_pelatihan.id_jadwal = tb_jadwal.id_jadwal
          WHERE tb_pelatihan.tanggal_bo BETWEEN ? AND ?";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$stmt->bind_param("ss", $tgl_awal, $tgl_akhir);
$stmt->execute();
$result = $stmt->get_result();

$laporan = [];
while ($row = $result->fetch_assoc()) {
    $laporan[] = $row;
}
?>

<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Preview Laporan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
    .signature-left {
        float: left;
        width: 50%;
        text-align: center;
    }

    .signature-right {
        float: right;
        width: 50%;
        text-align: center;
    }
    </style>
</head>

<body>
    <div class="container-xxl">
        <center>
            <h2>Laporan Pelatihan Kursus Mobil</h2>
            <h3>CV. RATU PADANG</h3>
            <h5>Dari <?php echo htmlspecialchars($tgl_awal); ?> - <?php echo htmlspecialchars($tgl_akhir); ?></h5>
        </center>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <td>Nama Peserta</td>
                        <td>Nama Instruktur</td>
                        <td>Tipe Kursus</td>
                        <td>Jadwal</td>
                        <td>Status</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    foreach ($laporan as $row) {
                        echo "<tr>";
                        echo "<td>{$no}</td>";
                        echo "<td>" . htmlspecialchars($row['name_konsumen']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['name_instruktur']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['nama_jenis']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['hari']) . " (" . htmlspecialchars($row['jam_mulai']) . " - " . htmlspecialchars($row['jam_selesai']) . ")" . "</td>";
                        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                        echo "</tr>";
                        $no++;
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <div class="signature-left">
        </div>
        <div class="signature-right">
            <p>
                Padang, <?php echo date('d-m-Y'); ?><br>
                Pimpinan
            </p>
            <br><br>
            <p>__________________</p>
        </div>
    </div>
    <!-- <script>
    window.print();
    </script> -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
        integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"
        integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous">
    </script>
</body>

</html>
