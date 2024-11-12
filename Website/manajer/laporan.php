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
    INNER JOIN tb_mobil ON tb_pelatihan.id_mobil = tb_mobil.id_mobil WHERE tb_pelatihan.tanggal_bo BETWEEN '$tgl_awal' AND '$tgl_akhir'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['laporan'] = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $_SESSION['tgl_awal'] = $tgl_awal;
        $_SESSION['tgl_akhir'] = $tgl_akhir;
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
                    <a href="customers.php">
                        <span class="icon">
                            <ion-icon name="people-outline"></ion-icon>
                        </span>
                        <span class="title">Users</span>
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
                    <a href="kursus.php">
                        <span class="icon">
                            <ion-icon name="bag-add-outline"></ion-icon>
                        </span>
                        <span class="title">Paket Kursus</span>
                    </a>
                </li>

                <li>
                    <a href="jadwal.php">
                        <span class="icon">
                            <ion-icon name="timer-outline"></ion-icon>
                        </span>
                        <span class="title">Jadwal</span>
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
                        <form id="form-laporan" action="laporan.php" method="post" class="form-container">
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

                            <button class="btn-submit" type="button" id="preview-btn">
                                Preview
                            </button>
                            <br>

                            <button class="btn-submit" type="submit" name="print">
                                Cetak
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Preview Section -->
            <div id="preview-laporan" style="margin: 20px;">
                <h3>Preview Laporan:</h3>
                <div id="preview-content"></div>
            </div>

            <!-- =========== Scripts =========  -->
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
            <script src="styles/app.js"></script>

            <!-- ====== ionicons ======= -->
            <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
            <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
            <script>
                $(document).ready(function () {
                    $('#preview-btn').click(function () {
                        var tgl_awal = $('#tgl_awal').val();
                        var tgl_akhir = $('#tgl_akhir').val();

                        if (tgl_awal && tgl_akhir) {
                            $.ajax({
                                url: 'preview_laporan.php',
                                type: 'POST',
                                data: { tgl_awal: tgl_awal, tgl_akhir: tgl_akhir },
                                success: function (response) {
                                    $('#preview-content').html(response);
                                },
                                error: function () {
                                    $('#preview-content').html('Terjadi kesalahan saat memuat data.');
                                }
                            });
                        } else {
                            alert('Silakan pilih tanggal terlebih dahulu.');
                        }
                    });
                });
            </script>
</body>

</html>