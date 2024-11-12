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

function setNotification($message, $type)
{
    $_SESSION['notification'] = ['message' => $message, 'type' => $type];
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

// Query untuk mendapatkan jumlah pelatihan
$query = "SELECT COUNT(*) AS jumlah_perbaikan FROM tb_perbaikan_mobil";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$jumlah_pelatihan = $row['jumlah_perbaikan'];

// Query untuk mendapatkan jumlah pendapatan
$query = "SELECT SUM(tb_jenis_pelatihan.harga) AS jumlah_pendapatan FROM tb_jenis_pelatihan JOIN tb_pelatihan ON tb_jenis_pelatihan.id_jenis_pelatihan = tb_pelatihan.id_jenis_pelatihan WHERE tb_pelatihan.status = 'Selesai' OR tb_pelatihan.status = 'Proses'";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$jumlah_pendapatan = $row['jumlah_pendapatan'];

$query_biaya = "SELECT SUM(tb_bensin.nominal) AS total_bensin 
                FROM tb_pelatihan 
                LEFT JOIN tb_bensin ON tb_pelatihan.id_pelatihan = tb_bensin.id_pelatihan 
                WHERE tb_pelatihan.status IN ('Proses', 'Dibayar', 'Selesai')";
$result_biaya = $conn->query($query_biaya);
$row_biaya = $result_biaya->fetch_assoc();
$total_bensin = $row_biaya['total_bensin'];

$query_perbaikan = "SELECT SUM(tb_perbaikan_mobil.nominal) AS total_perbaikan 
                FROM tb_mobil 
                LEFT JOIN tb_perbaikan_mobil ON tb_mobil.id_mobil = tb_perbaikan_mobil.id_mobil";
$result_perbaikan = $conn->query($query_perbaikan);
$row_perbaiki = $result_perbaikan->fetch_assoc();
$total_perbaikan = $row_perbaiki['total_perbaikan'];

$total_gaji = 500000; // Gaji tetap per pelatihan
$query_total_gaji = "SELECT COUNT(*) AS jumlah_pelatihan 
                     FROM tb_pelatihan 
                     WHERE status IN ('Proses', 'Dibayar', 'Selesai')";
$result_total_gaji = $conn->query($query_total_gaji);
$row_total_gaji = $result_total_gaji->fetch_assoc();
$total_gaji *= $row_total_gaji['jumlah_pelatihan'];

$jumlah_laba_bersih = $jumlah_pendapatan - $total_perbaikan;

$total_pengeluaran = $total_perbaikan + $total_gaji + $total_bensin;

$query = "SELECT * FROM tb_mobil 
          INNER JOIN tb_perbaikan_mobil ON tb_mobil.id_mobil = tb_perbaikan_mobil.id_mobil";
$result = $conn->query($query);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajer Dashboard | CV. Ratu Mengemudi</title>
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
    <style>
        .notification-content {
            background-color: hsl(0deg, 0%, 96%);
            border-radius: 4px;
            position: relative;
            padding: 1.25rem 2.5rem 1.25rem 1.5rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .notification a:not(.button):not(.dropdown-item) {
            color: currentColor;
            text-decoration: underline;
        }

        .notification strong {
            color: currentColor;
        }

        .notification code,
        .notification pre {
            background: hsl(0deg, 0%, 100%);
        }

        .notification pre code {
            background: transparent;
        }

        .notification>.delete {
            right: 0.5rem;
            position: absolute;
            top: 0.5rem;
        }

        .notification .title,
        .notification .subtitle,
        .notification .content {
            color: currentColor;
        }

        .notification.is-white {
            background-color: hsl(0deg, 0%, 100%);
            color: hsl(0deg, 0%, 4%);
        }

        .notification.is-black {
            background-color: hsl(0deg, 0%, 4%);
            color: hsl(0deg, 0%, 100%);
        }

        .notification.is-light {
            background-color: hsl(0deg, 0%, 96%);
            color: rgba(0, 0, 0, 0.7);
        }

        .notification.is-dark {
            background-color: hsl(0deg, 0%, 21%);
            color: #fff;
        }

        .notification.is-primary {
            background-color: hsl(171deg, 100%, 41%);
            color: #fff;
        }

        .notification.is-primary.is-light {
            background-color: #ebfffc;
            color: #00947e;
        }

        .notification.is-link {
            background-color: hsl(229deg, 53%, 53%);
            color: #fff;
        }

        .notification.is-link.is-light {
            background-color: #eff1fa;
            color: #3850b7;
        }

        .notification.is-info {
            background-color: hsl(207deg, 61%, 53%);
            color: #fff;
        }

        .notification.is-info.is-light {
            background-color: #eff5fb;
            color: #296fa8;
        }

        .notification.is-success {
            background-color: hsl(153deg, 53%, 53%);
            color: #fff;
        }

        .notification.is-success.is-light {
            background-color: #effaf5;
            color: #257953;
        }

        .notification.is-warning {
            background-color: hsl(44deg, 100%, 77%);
            color: rgba(0, 0, 0, 0.7);
        }

        .notification.is-warning.is-light {
            background-color: #fffaeb;
            color: #946c00;
        }

        .notification.is-danger {
            background-color: hsl(348deg, 86%, 61%);
            color: #fff;
        }

        .notification.is-danger.is-light {
            background-color: #feecf0;
            color: #cc0f35;
        }
    </style>
</head>

<body>
    <!-- Notification -->
    <?php if (isset($_SESSION['notification'])): ?>
        <div class="notification is-<?php echo $_SESSION['notification']['type']; ?>" id="notification">
            <?php echo $_SESSION['notification']['message']; ?>
        </div>
        <?php unset($_SESSION['notification']); ?>
    <?php endif; ?>
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            const notification = document.getElementById('notification');
            if (notification) {
                notification.style.display = 'block';
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 5000);
            }
        });
    </script>

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
            <div class="cardBox">
                <div class="card">
                    <div>
                        <div class="numbers"><?php echo $jumlah_pelatihan; ?></div>
                        <div class="cardName">Pelatihan</div>
                    </div>

                    <div class="iconBx">
                        <ion-icon name="car-sport-outline"></ion-icon>
                    </div>
                </div>

                <div class="card">
                    <div>
                        <div class="numbers"><?php echo "Rp " . number_format($jumlah_pendapatan, 0, ',', '.'); ?></div>
                        <div class="cardName">Total Pendapatan</div>
                    </div>

                    <div class="iconBx">
                        <ion-icon name="cash-outline"></ion-icon>
                    </div>
                </div>

                <div class="card">
                    <div>
                        <div style="color: red;" class="numbers">
                            <?php echo "- Rp " . number_format($total_perbaikan, 0, ',', '.'); ?>
                        </div>
                        <div class="cardName">Total Perbaikan</div>
                    </div>

                    <div class="iconBx">
                        <ion-icon name="cash-outline"></ion-icon>
                    </div>
                </div>

                <div class="card">
                    <div>
                        <div class="numbers"><?php echo "Rp " . number_format($jumlah_laba_bersih, 0, ',', '.'); ?>
                        </div>
                        <div class="cardName">Laba Bersih Perbaikan</div>
                    </div>

                    <div class="iconBx">
                        <ion-icon name="cash-outline"></ion-icon>
                    </div>
                </div>
            </div>

            <!-- ================ Order Details List ================= -->
            <div class="details">
                <div class="recentOrders">
                    <div class="cardHeader">
                        <h2>Perbaikan Mobil</h2>
                    </div>

                    <table id="example">
                        <thead>
                            <tr>
                                <td>Nama Mobil</td>
                                <td>Tipe Mobil</td>
                                <td>Bukti Perbaikan</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($data = mysqli_fetch_array($result)) { ?>
                                <tr>
                                    <td><?php echo $data['nama_mobil']; ?></td>
                                    <td><?php echo $data['tipe_mobil']; ?></td>
                                    <td>
                                        <img src="../instruktur/img/<?php echo $data['gambarbuktimobil']; ?>"
                                            width="200px"><br>
                                        <?php echo "Rp " . number_format($data['nominal'], 0, ',', '.'); ?>
                                    </td>
                                <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- =========== Scripts =========  -->

    <script src="styles/app.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>

    <!-- ====== ionicons ======= -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script>
        $(document).ready(function () {
            $('#example').DataTable();
        });
    </script>
</body>

</html>