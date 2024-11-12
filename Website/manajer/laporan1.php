<?php
session_start();
include '../config/koneksi.php';

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'peserta') {
        header("Location: ../peserta/index.php");
        exit();
    } elseif ($_SESSION['role'] === 'instruktur') {
        header("Location: ../instruktur/index.php");
        exit();
    } elseif ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/index.php");
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}

$id_user = $_SESSION['id_user'];

$query = "SELECT * FROM tb_user WHERE id_user = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$nama = @$result['nama'];
$role = @$result['role'];
$username = @$result['username'];
$password = @$result['password'];
$nohp = @$result['nohp'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['print'])) {
    $tgl_awal = $_POST['tgl_awal'];
    $tgl_akhir = $_POST['tgl_akhir'];

    $query = "SELECT * FROM tb_pelatihan 
    INNER JOIN tb_konsumen ON tb_pelatihan.id_konsumen = tb_konsumen.id_konsumen 
    INNER JOIN tb_instruktur ON tb_pelatihan.id_instruktur = tb_instruktur.id_instruktur 
    JOIN tb_jadwal ON tb_pelatihan.id_jadwal = tb_jadwal.id_jadwal 
    INNER JOIN tb_jenis_pelatihan ON tb_pelatihan.id_jenis_pelatihan = tb_jenis_pelatihan.id_jenis_pelatihan 
    INNER JOIN tb_mobil ON tb_pelatihan.id_mobil = tb_mobil.id_mobil
    LEFT JOIN tb_bensin ON tb_pelatihan.id_pelatihan = tb_bensin.id_pelatihan 
    WHERE tb_pelatihan.tanggal_bo BETWEEN ? AND ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $tgl_awal, $tgl_akhir);
    $stmt->execute();
    $result = $stmt->get_result();

    // Query untuk mendapatkan jumlah pelatihan
    $queryJumlahPelatihan = "SELECT COUNT(*) AS jumlah_pelatihans FROM tb_pelatihan WHERE status IN ('Proses', 'Dibayar', 'Selesai') AND tanggal_bo BETWEEN ? AND ?";
    $stmtJumlahPelatihan = $conn->prepare($queryJumlahPelatihan);
    $stmtJumlahPelatihan->bind_param("ss", $tgl_awal, $tgl_akhir);
    $stmtJumlahPelatihan->execute();
    $resultJumlahPelatihan = $stmtJumlahPelatihan->get_result();
    $rowJumlahPelatihan = $resultJumlahPelatihan->fetch_assoc();
    $jumlah_pelatihan = $rowJumlahPelatihan['jumlah_pelatihans'];

    // Query untuk mendapatkan jumlah pendapatan
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
    $row_perbaiki = $result_perbaikan->fetch_assoc();
    $total_perbaikan = $row_perbaiki['total_perbaikan'];

    // Menghitung total gaji instruktur
    $total_gaji = 500000; // Gaji tetap per pelatihan
    $total_gaji *= $jumlah_pelatihan;

    // Menghitung laba bersih
    $jumlah_laba_bersih = $jumlah_pendapatan - $total_gaji - $total_bensin - $total_perbaikan;
    $total_pengeluaran = $total_perbaikan + $total_gaji + $total_bensin;

    if ($result->num_rows > 0) {
        $_SESSION['laporan'] = $result->fetch_all(MYSQLI_ASSOC);
        $_SESSION['tgl_awal'] = $tgl_awal;
        $_SESSION['tgl_akhir'] = $tgl_akhir;
        $_SESSION['jumlah_laba_bersih'] = $jumlah_laba_bersih;
        $_SESSION['total_pengeluaran'] = $total_pengeluaran;
        $_SESSION['total_gaji'] = $total_gaji;
        $_SESSION['jumlah_pendapatan'] = $jumlah_pendapatan;
        $_SESSION['total_perbaikan'] = $total_perbaikan;
        $_SESSION['total_bensin'] = $total_bensin;



        header("Location: print.php");
        exit();
    } else {
        echo "Tidak ada data yang ditemukan.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | KOMINFO</title>
    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="styles/app.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
    <style>
        .profile-header {
            display: flex;
            align-items: center;
        }

        .profile-header img {
            border-radius: 50%;
            margin-right: 10px;
        }
    </style>
</head>

<body>
    <!-- =============== Navigation ================ -->
    <div class="container">
        <div class="navigation">
            <ul>
                <li>
                    <a href="#">
                        <span class="icon">
                            <ion-icon name="logo-apple"></ion-icon>
                        </span>
                        <span class="title">CV. Ratu</span>
                    </a>
                </li>

                <li>
                    <a href="index.php">
                        <span class="icon">
                            <ion-icon name="home-outline"></ion-icon>
                        </span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>

                <li>
                    <a href="pelatihan.php">
                        <span class="icon">
                            <ion-icon name="car-outline"></ion-icon>
                        </span>
                        <span class="title">Pelatihan</span>
                    </a>
                </li>

                <li>
                    <a href="mobil.php">
                        <span class="icon">
                            <ion-icon name="car-sport-outline"></ion-icon>
                        </span>
                        <span class="title">Mobil</span>
                    </a>
                </li>

                <li>
                    <a href="laporan.php">
                        <span class="icon">
                            <ion-icon name="documents-outline"></ion-icon>
                        </span>
                        <span class="title">Laporan</span>
                    </a>
                </li>

                <li>
                    <a href="settings.php">
                        <span class="icon">
                            <ion-icon name="settings-outline"></ion-icon>
                        </span>
                        <span class="title">Edit Profil</span>
                    </a>
                </li>

                <li>
                    <a href="logout.php">
                        <span class="icon">
                            <ion-icon name="log-out-outline"></ion-icon>
                        </span>
                        <span class="title">Sign Out</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- ========================= Main ==================== -->
        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon name="menu-outline"></ion-icon>
                </div>
                <!-- Foto Profil dan Nama -->
                <div class="profile-header">
                    <img src="../img/admin.png" alt="Foto Profil" width="40" height="40">
                    <span><?php echo $username; ?></span>
                </div>
            </div>

            <!-- ======================= Cards ================== -->
            <div class="cardBox" style="display: flex;justify-content: center;align-items: center;">
                <div class="card" style="width: 300px; padding: 20px;">
                    <div>
                        <form action="laporan.php" method="post" class="form-container">
                            <center>
                                <h3>Cetak Laporan Pelatihan</h3>
                            </center>
                            <br>
                            <label class="form-label" for="tgl_awal">
                                Dari Tanggal:
                            </label>
                            <input class="form-input" type="date" id="tgl_awal" name="tgl_awal" required>

                            <label class="form-label" for="tgl_akhir">
                                Ke Tanggal:
                            </label>
                            <input class="form-input" type="date" id="tgl_akhir" name="tgl_akhir" required>

                            <button class="btn-submit" type="submit" name="print">
                                Cetak
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- =========== Scripts =========  -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="styles/app.js"></script>

    <!-- ====== ionicons ======= -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>