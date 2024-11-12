<?php
session_start();
include '../config/koneksi.php';

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/index.php");
        exit();
    } elseif ($_SESSION['role'] === 'peserta') {
        header("Location: ../peserta/index.php");
        exit();
    } elseif ($_SESSION['role'] === 'manajer') {
        header("Location: ../manajer/index.php");
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

$query = "SELECT * FROM tb_instruktur WHERE id_instruktur = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$nama = @$result['name_instruktur'];
$role = @$result['role'];
$username = @$result['username'];
$password = @$result['password'];
$nohp = @$result['nohp'];
$img = @$result['img'];

$query = "SELECT * FROM tb_pelatihan 
          INNER JOIN tb_konsumen ON tb_pelatihan.id_konsumen = tb_konsumen.id_konsumen 
          INNER JOIN tb_instruktur ON tb_pelatihan.id_instruktur = tb_instruktur.id_instruktur 
          JOIN tb_jadwal ON tb_pelatihan.id_jadwal = tb_jadwal.id_jadwal 
          INNER JOIN tb_jenis_pelatihan ON tb_pelatihan.id_jenis_pelatihan = tb_jenis_pelatihan.id_jenis_pelatihan 
          INNER JOIN tb_mobil ON tb_pelatihan.id_mobil = tb_mobil.id_mobil
          LIMIT 10";
$result = $conn->query($query);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instruktur Dashboard | CV. Ratu Mengemudi</title>
    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="styles/app.css">
    <style>
        .notification {
            background-color: hsl(0deg, 0%, 96%);
            border-radius: 4px;
            position: relative;
            padding: 1.25rem 2.5rem 1.25rem 1.5rem;
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
                    <a href="buku.php">
                        <span class="icon">
                            <ion-icon name="book-outline"></ion-icon>
                        </span>
                        <span class="title">Buku Panduan</span>
                    </a>
                </li>

                <li>
                    <a href="soal.php">
                        <span class="icon">
                            <ion-icon name="document-text-outline"></ion-icon>
                        </span>
                        <span class="title">Soal</span>
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
                    <?php if ($img == NULL) { ?>
                        <img src="../img/instruktur.png" alt="Foto Profil" width="40" height="40">
                    <?php } else { ?>
                        <img src="img/<?php echo $img ?>" alt="Foto Profil" width="40" height="40">
                    <?php } ?>
                    <span><?php echo $nama; ?></span>
                </div>
            </div>

            <!-- ================ Order Details List ================= -->
            <div class="details">
                <div class="recentOrders">
                    <div class="cardHeader">
                        <h2>Pelatihan Mengemudi</h2>
                        <a href="pelatihan.php" class="btn">View All</a>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <td>Nama Konsumen</td>
                                <td>Nama Instruktur</td>
                                <td>Tipe Kursus</td>
                                <td>Jadwal</td>
                                <td>Status</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($data = mysqli_fetch_array($result)) { ?>
                                <tr>
                                    <td><?php echo $data['name_konsumen']; ?></td>
                                    <td><?php echo $data['name_instruktur']; ?></td>
                                    <td><?php echo $data['keterangan']; ?></td>
                                    <td><?php echo $data['hari']; ?> (<?php echo $data['jam_mulai']; ?> -
                                        <?php echo $data['jam_selesai']; ?>)
                                    </td>
                                    <td>
                                        <span class="status 
                                        <?php
                                        if ($data['status'] === 'Dibayar') {
                                            echo 'pending';
                                        } elseif ($data['status'] === 'Proses') {
                                            echo 'inProgress';
                                        } elseif ($data['status'] === 'Selesai') {
                                            echo 'delivered';
                                        } else {
                                            echo 'return';
                                        }
                                        ?>">
                                            <?php echo $data['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- =========== Scripts =========  -->
    <script src="styles/app.js"></script>

    <!-- ====== ionicons ======= -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>