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
$jumlah_laba_bersih = $_SESSION['jumlah_laba_bersih'];
$total_pengeluaran = $_SESSION['total_pengeluaran'];
$jumlah_pelatihan = $_SESSION['jumlah_pelatihan'];
$jumlah_pendapatan = $_SESSION['jumlah_pendapatan'];
$total_perbaikan = $_SESSION['total_perbaikan'];
$total_bensin = $_SESSION['total_bensin'];

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
            <h2>Laporan Pendapatan Pelatihan Kursus Mobil</h2>
            <h3>CV. RATU PADANG</h3>
            <h5>Dari <?php echo $tgl_awal; ?> - <?php echo $tgl_akhir; ?></h5>
        </center>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <td>NO</td>
                        <td>Nama Peserta</td>
                        <td>Nama Instruktur</td>
                        <td>Tipe Kursus</td>
                        <td>Harga</td>
                        <td>Gaji</td>
                        <td>Bensin</td>
                        <td>Laba Bersih</td>
                        <td>Status</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $total_gaji = 0;
                    foreach ($laporan as $row) {
                        echo "<tr>";
                        echo "<td>{$no}</td>";
                        echo "<td>{$row['name_konsumen']}</td>";
                        echo "<td>{$row['name_instruktur']}</td>";
                        echo "<td>{$row['keterangan']}</td>";
                        echo "<td>Rp " . number_format($row['harga'], 0, ',', '.') . "</td>";
                        echo "<td>Rp 500.000</td>";
                        echo "<td>";
                        if (!empty($row['gambarbukti'])) {
                            echo "<img src='../instruktur/img/{$row['gambarbukti']}' width='50px'><br>Rp " . number_format($row['nominal'], 0, ',', '.');
                        } else {
                            echo "Rp " . number_format($row['nominal'], 0, ',', '.');
                        }
                        echo "</td>";
                        echo "<td>Rp " . number_format($row['harga'] - $row['nominal'] - 500000, 0, ',', '.') . "</td>";
                        echo "<td>{$row['status']}</td>";
                        echo "</tr>";
                        $no++;
                        $total_gaji += 500000;

                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td>
                            Pendapatan: Rp <?php echo number_format($jumlah_pendapatan, 0, ',', '.'); ?><br>
                            Perbaikan Mobil: Rp <?php echo number_format($total_perbaikan, 0, ',', '.'); ?><br>
                            Total Pendapatan: Rp
                            <?php echo number_format($jumlah_pendapatan - $total_bensin - $total_perbaikan - $total_gaji, 0, ',', '.'); ?><br>
                        </td>
                    </tr>
                </tfoot>
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