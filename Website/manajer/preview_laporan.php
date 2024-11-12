<?php
include '../config/koneksi.php';

// Debugging untuk koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['tgl_awal']) && isset($_POST['tgl_akhir'])) {
    $tgl_awal = $_POST['tgl_awal'];
    $tgl_akhir = $_POST['tgl_akhir'];

    // echo "Tanggal Awal: $tgl_awal<br>";
    // echo "Tanggal Akhir: $tgl_akhir<br>";

    // Query untuk laporan
    $query = "SELECT * FROM tb_pelatihan 
        INNER JOIN tb_konsumen ON tb_pelatihan.id_konsumen = tb_konsumen.id_konsumen 
        INNER JOIN tb_instruktur ON tb_pelatihan.id_instruktur = tb_instruktur.id_instruktur 
        JOIN tb_jadwal ON tb_pelatihan.id_jadwal = tb_jadwal.id_jadwal 
        INNER JOIN tb_jenis_pelatihan ON tb_pelatihan.id_jenis_pelatihan = tb_jenis_pelatihan.id_jenis_pelatihan 
        INNER JOIN tb_mobil ON tb_pelatihan.id_mobil = tb_mobil.id_mobil
        LEFT JOIN tb_bensin ON tb_pelatihan.id_pelatihan = tb_bensin.id_pelatihan 
        WHERE tb_pelatihan.tanggal_bo BETWEEN ? AND ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("ss", $tgl_awal, $tgl_akhir);
    $stmt->execute();
    $result = $stmt->get_result();

    // Debugging untuk hasil query
    if ($result === false) {
        die('Query failed: ' . htmlspecialchars($conn->error));
    }
    if ($result->num_rows > 0) {
        $laporan = [];
        while ($row = $result->fetch_assoc()) {
            $laporan[] = $row;
        }

        // Query untuk jumlah pelatihan
        $queryJumlahPelatihan = "SELECT COUNT(*) AS jumlah_pelatihans FROM tb_pelatihan WHERE status IN ('Proses', 'Dibayar', 'Selesai') AND tanggal_bo BETWEEN ? AND ?";
        $stmtJumlahPelatihan = $conn->prepare($queryJumlahPelatihan);
        $stmtJumlahPelatihan->bind_param("ss", $tgl_awal, $tgl_akhir);
        $stmtJumlahPelatihan->execute();
        $resultJumlahPelatihan = $stmtJumlahPelatihan->get_result();
        $rowJumlahPelatihan = $resultJumlahPelatihan->fetch_assoc();
        $jumlah_pelatihan = $rowJumlahPelatihan['jumlah_pelatihans'];

        // Query untuk jumlah pendapatan
        $queryJumlahPendapatan = "SELECT SUM(tb_jenis_pelatihan.harga) AS jumlah_pendapatan FROM tb_jenis_pelatihan 
        JOIN tb_pelatihan ON tb_jenis_pelatihan.id_jenis_pelatihan = tb_pelatihan.id_jenis_pelatihan 
        WHERE (tb_pelatihan.status = 'Selesai' OR tb_pelatihan.status = 'Proses') AND tb_pelatihan.tanggal_bo BETWEEN ? AND ?";
        $stmtJumlahPendapatan = $conn->prepare($queryJumlahPendapatan);
        $stmtJumlahPendapatan->bind_param("ss", $tgl_awal, $tgl_akhir);
        $stmtJumlahPendapatan->execute();
        $resultJumlahPendapatan = $stmtJumlahPendapatan->get_result();
        $rowJumlahPendapatan = $resultJumlahPendapatan->fetch_assoc();
        $jumlah_pendapatan = $rowJumlahPendapatan['jumlah_pendapatan'];

        // Query untuk biaya bensin
        $queryBiayaBensin = "SELECT SUM(tb_bensin.nominal) AS total_bensin 
        FROM tb_pelatihan 
        LEFT JOIN tb_bensin ON tb_pelatihan.id_pelatihan = tb_bensin.id_pelatihan 
        WHERE tb_pelatihan.status IN ('Proses', 'Dibayar', 'Selesai') AND tb_pelatihan.tanggal_bo BETWEEN ? AND ?";
        $stmtBiayaBensin = $conn->prepare($queryBiayaBensin);
        $stmtBiayaBensin->bind_param("ss", $tgl_awal, $tgl_akhir);
        $stmtBiayaBensin->execute();
        $resultBiayaBensin = $stmtBiayaBensin->get_result();
        $rowBiayaBensin = $resultBiayaBensin->fetch_assoc();
        $total_bensin = $rowBiayaBensin['total_bensin'];

        // Query untuk biaya perbaikan mobil
        $query_perbaikan = "SELECT SUM(tb_perbaikan_mobil.nominal) AS total_perbaikan 
        FROM tb_mobil 
        LEFT JOIN tb_perbaikan_mobil ON tb_mobil.id_mobil = tb_perbaikan_mobil.id_mobil";
        $result_perbaikan = $conn->query($query_perbaikan);
        if ($result_perbaikan === false) {
            die('Query perbaikan failed: ' . htmlspecialchars($conn->error));
        }
        $row_perbaiki = $result_perbaikan->fetch_assoc();
        $total_perbaikan = $row_perbaiki['total_perbaikan'];

        // Menghitung total gaji instruktur
        $total_gaji = 500000; // Gaji tetap per pelatihan
        $total_gaji *= $jumlah_pelatihan;

        // Menghitung laba bersih
        $jumlah_laba_bersih = $jumlah_pendapatan - $total_gaji - $total_bensin - $total_perbaikan;
        $total_pengeluaran = $total_perbaikan + $total_gaji + $total_bensin;
    } else {
        $laporan = [];
        $jumlah_pelatihan = 0;
        $jumlah_pendapatan = 0;
        $total_bensin = 0;
        $total_perbaikan = 0;
        $total_gaji = 0;
        $jumlah_laba_bersih = 0;
        $total_pengeluaran = 0;
    }
} else {
    echo "Tanggal tidak valid.";
    exit;
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
            <h2>Laporan Pendapatan Pelatihan Kursus Mobil</h2>
            <h3>CV. RATU PADANG</h3>
            <h5>Dari <?php echo htmlspecialchars($tgl_awal); ?> - <?php echo htmlspecialchars($tgl_akhir); ?></h5>
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
        <div class="signature-left"></div>
        <div class="signature-right">
            <p>
                Padang, <?php echo date('d-m-Y'); ?><br>
                Pimpinan
            </p>
            <br><br>
            <p>__________________</p>
        </div>
    </div>
</body>
</html>
