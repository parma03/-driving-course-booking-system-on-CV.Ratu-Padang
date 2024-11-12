<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['laporan'])) {
    header("Location: laporan.php");
    exit();
}

$laporan = $_SESSION['laporan'];
$tgl_awal = $_SESSION['tgl_awal'];
$tgl_akhir = $_SESSION['tgl_akhir'];         
?>

<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Laporan</title>
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
            <h5>Dari <?php echo $tgl_awal; ?> - <?php echo $tgl_akhir; ?></h5>
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
                        echo "<td>{$row['name_konsumen']}</td>";
                        echo "<td>{$row['name_instruktur']}</td>";
                        echo "<td>{$row['keterangan']}</td>";
                        echo "<td>{$row['hari']} ({$row['jam_mulai']} - {$row['jam_selesai']})</td>";
                        echo "<td>{$row['status']}</td>";
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
    <script>
    window.print();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
        integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"
        integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous">
    </script>
</body>

</html>