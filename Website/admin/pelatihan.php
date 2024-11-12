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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $id_pelatihan = $_POST['id_pelatihan'];

    // Hapus data dari tabel tb_pelatihan
    $query_delete_pelatihan = "DELETE FROM tb_pelatihan WHERE id_pelatihan = ?";
    $stmt = $conn->prepare($query_delete_pelatihan);
    $stmt->bind_param("i", $id_pelatihan);
    if ($stmt->execute()) {
        setNotification("Berhasil Hapus Data Pelatihan", "success");
        header("Location: pelatihan.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id_pelatihan = $_POST['id_pelatihan'];
    $id_instruktur = $_POST['id_instruktur'];

    $query_pelatihan = "UPDATE tb_pelatihan SET id_instruktur = ?, status = 'Proses' WHERE id_pelatihan = ?";
    $stmt = $conn->prepare($query_pelatihan);
    $stmt->bind_param("ii", $id_instruktur, $id_pelatihan);
    if ($stmt->execute()) {
        setNotification("Berhasil Tambah Instruktur Pelatihan", "success");
        header("Location: pelatihan.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['v_sim'])) {
    $id_pelatihan = $_POST['id_pelatihan'];
    $no_sim = "#" . $id_pelatihan;
    $type = "A";

    $query_sim = "INSERT INTO tb_esim (id_pelatihan, no_sim, type) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query_sim);
    $stmt->bind_param("iss", $id_pelatihan, $no_sim, $type);
    if ($stmt->execute()) {
        setNotification("Berhasil Tambah NO SIM", "success");
        header("Location: pelatihan.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

// Query untuk mendapatkan jumlah pelatihan
$query = "SELECT COUNT(*) AS jumlah_pelatihan FROM tb_pelatihan WHERE status = 'Proses' OR status = 'Dibayar'";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$jumlah_pelatihan = $row['jumlah_pelatihan'];

// Query untuk mendapatkan jumlah pelatihan selesai
$query = "SELECT COUNT(*) AS jumlah_selesai FROM tb_pelatihan WHERE status = 'Selesai'";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$jumlah_selesai = $row['jumlah_selesai'];

// Query untuk mendapatkan jumlah pelatihan dibayar
$query = "SELECT COUNT(*) AS jumlah_dibayar FROM tb_pelatihan WHERE status = 'Dibayar'";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$jumlah_dibayar = $row['jumlah_dibayar'];

// Query untuk mendapatkan jumlah pelatihan diproses
$query = "SELECT COUNT(*) AS jumlah_proses FROM tb_pelatihan WHERE status = 'Proses'";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$jumlah_proses = $row['jumlah_proses'];

$query = "SELECT 
tb_pelatihan.id_pelatihan, 
tb_pelatihan.id_instruktur,
tb_pelatihan.status, 
tb_konsumen.name_konsumen, 
tb_jenis_pelatihan.nama_jenis, 
tb_jenis_pelatihan.keterangan, 
tb_jenis_pelatihan.kategori, 
tb_jadwal.hari, 
tb_jadwal.jam_mulai, 
tb_jadwal.jam_selesai, 
tb_nilai.id_nilai, 
tb_nilai.nilai_teori, 
tb_nilai.nilai_percaya_diri, 
tb_nilai.nilai_kesopanan_mengemudi, 
tb_nilai.nilai_kepatuhan_lalin, 
tb_nilai.nilai_sikap, 
tb_nilai.nilai_pengetahuan_kendaraan, 
tb_nilai.nilai_keamanan,
tb_instruktur.name_instruktur,
tb_mobil.nama_mobil,
tb_esim.id_esim,
tb_esim.no_sim,
tb_esim.type
FROM 
    tb_pelatihan 
INNER JOIN tb_konsumen ON tb_pelatihan.id_konsumen = tb_konsumen.id_konsumen 
LEFT JOIN tb_instruktur ON tb_pelatihan.id_instruktur = tb_instruktur.id_instruktur 
LEFT JOIN tb_esim ON tb_pelatihan.id_pelatihan = tb_esim.id_pelatihan 
INNER JOIN tb_jadwal ON tb_pelatihan.id_jadwal = tb_jadwal.id_jadwal 
INNER JOIN tb_jenis_pelatihan ON tb_pelatihan.id_jenis_pelatihan = tb_jenis_pelatihan.id_jenis_pelatihan 
INNER JOIN tb_mobil ON tb_pelatihan.id_mobil = tb_mobil.id_mobil 
LEFT JOIN tb_nilai ON tb_pelatihan.id_pelatihan = tb_nilai.id_pelatihan";
$result = $conn->query($query);

// Query untuk mendapatkan data instruktur
$query_instruktur = "SELECT id_instruktur, name_instruktur, tipe_instruktur FROM tb_instruktur";
$result_instruktur = $conn->query($query_instruktur);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | CV. Ratu Mengemudi</title>
    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="styles/app.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
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
                        <div class="numbers"><?php echo $jumlah_selesai; ?></div>
                        <div class="cardName">Selesai</div>
                    </div>

                    <div class="iconBx">
                        <ion-icon name="checkmark-done-outline"></ion-icon>
                    </div>
                </div>

                <div class="card">
                    <div>
                        <div class="numbers"><?php echo $jumlah_dibayar; ?></div>
                        <div class="cardName">Dibayar</div>
                    </div>

                    <div class="iconBx">
                        <ion-icon name="cash-outline"></ion-icon>
                    </div>
                </div>

                <div class="card">
                    <div>
                        <div class="numbers"><?php echo $jumlah_proses; ?></div>
                        <div class="cardName">Proses Pelatihan</div>
                    </div>

                    <div class="iconBx">
                        <ion-icon name="school-outline"></ion-icon>
                    </div>
                </div>
            </div>

            <!-- ================ Order Details List ================= -->
            <div class="details">
                <div class="recentOrders">
                    <div class="cardHeader">
                        <h2>Data Pelatihan</h2>
                    </div>

                    <table id="example">
                        <thead>
                            <tr>
                                <td>Nama Peserta</td>
                                <td>Nama Instruktur</td>
                                <td>Tipe Kursus</td>
                                <td>Jadwal</td>
                                <td>Status</td>
                                <td>Action</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($data = mysqli_fetch_array($result)) { ?>
                                <tr>
                                    <td><?php echo $data['name_konsumen']; ?></td>
                                    <td><?php echo isset($data['name_instruktur']) ? $data['name_instruktur'] : 'Belum Ditentukan'; ?>
                                    </td>
                                    <td>( <?php echo $data['nama_jenis']; ?> ) <?php echo $data['keterangan']; ?></td>
                                    <td><?php echo $data['hari']; ?> (<?php echo $data['jam_mulai']; ?> -
                                        <?php echo $data['jam_selesai']; ?>)
                                    </td>
                                    <td colspan="2">
                                        <a href="hasil.php?id_pelatihan=<?php echo $data['id_pelatihan']; ?>"
                                            class="btn-open-download">Lihat Nilai</a>
                                        <br><br>
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
                                    <td>
                                        <?php if (is_null($data['id_instruktur'])) { ?>
                                            <button class="btn-open-update"
                                                onclick="togglePopupUpdate('<?php echo $data['id_pelatihan']; ?>')">VERIFIKASI</button>
                                        <?php } elseif ($data['status'] === "Selesai") {
                                            if ($data['id_esim'] === NULL) { ?>
                                                <button class="btn-open-update"
                                                    onclick="togglePopupSIM('<?php echo $data['id_pelatihan']; ?>')">SIM</button>
                                                <button class="btn-open-delete"
                                                    onclick="togglePopupDelete('<?php echo $data['id_pelatihan']; ?>')">DELETE</button>
                                                <?php
                                            } elseif ($data['id_esim'] != NULL) { ?>
                                                <button class="btn-open-delete"
                                                    onclick="togglePopupDelete('<?php echo $data['id_pelatihan']; ?>')">DELETE</button>
                                                <?php
                                            }
                                        } else { ?>
                                            <button class="btn-open-delete"
                                                onclick="togglePopupDelete('<?php echo $data['id_pelatihan']; ?>')">DELETE</button>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="popupOverlayUpdate" class="overlay-container">
        <div class="popup-box">
            <h2 style="color: green;">Form Verivikasi</h2>
            <br>
            <form action="pelatihan.php" method="post" class="form-container">
                <input type="hidden" name="id_pelatihan" value="">
                <select class="form-input" name="id_instruktur" id="id_instruktur" required>
                    <option value="" disabled selected>Pilih Instruktur</option>
                    <?php while ($instruktur = mysqli_fetch_array($result_instruktur)) { ?>
                        <option value="<?php echo $instruktur['id_instruktur']; ?>">
                            <?php echo $instruktur['name_instruktur'] . ' - ' . $instruktur['tipe_instruktur']; ?>
                        </option>
                    <?php } ?>
                </select>

                <button class="btn-submit" type="submit" name="update">
                    Submit
                </button>
            </form>

            <button class="btn-close-popup" onclick="togglePopupUpdate()">
                Close
            </button>
        </div>
    </div>

    <div id="popupOverlayDelete" class="overlay-container">
        <div class="popup-box">
            <h2 style="color: green;">Delete Users</h2>
            <form action="pelatihan.php" method="post" class="form-container">
                <input type="hidden" name="id_pelatihan" value="">
                <label class="form-label" for="name">
                    Yakin Menghapus Data Pelatihan?
                </label>
                <button class="btn-submit" type="submit" name="delete">
                    Submit
                </button>
            </form>

            <button class="btn-close-popup" onclick="togglePopupDelete()">
                Close
            </button>
        </div>
    </div>

    <div id="popupOverlaySIM" class="overlay-container">
        <div class="popup-box">
            <h2 style="color: green;">Verifikasi E-SIM</h2>
            <form action="pelatihan.php" method="post" class="form-container">
                <input type="hidden" name="id_pelatihan" value="">

                <label class="form-label">
                    Verifikasi E-SIM pada Peserta ini?
                </label>

                <button class="btn-submit" type="submit" name="v_sim">
                    Submit
                </button>
            </form>

            <button class="btn-close-popup" onclick="togglePopupSIM()">
                Close
            </button>
        </div>
    </div>

    <!-- =========== Scripts =========  -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="styles/app.js"></script>

    <script>
        function togglePopupUpdate(id_pelatihan) {
            const overlay = document.getElementById('popupOverlayUpdate');
            overlay.classList.toggle('show');

            if (id_pelatihan && id_instruktur) {
                document.querySelector('#popupOverlayUpdate input[name="id_pelatihan"]').value = id_pelatihan;
            }
        }

        function togglePopupDelete(id_pelatihan) {
            const overlay = document.getElementById('popupOverlayDelete');
            overlay.classList.toggle('show');

            if (id_pelatihan) {
                document.querySelector('#popupOverlayDelete input[name="id_pelatihan"]').value = id_pelatihan;
                document.querySelector('#popupOverlayDelete .form-label').textContent = 'Yakin Menghapus Data Pelatihan?';
            }
        }

        function togglePopupSIM(id_pelatihan) {
            const overlay = document.getElementById('popupOverlaySIM');
            overlay.classList.toggle('show');

            if (id_pelatihan) {
                document.querySelector('#popupOverlaySIM input[name="id_pelatihan"]').value = id_pelatihan;
            }
        }


        $(document).ready(function () {
            $('#example').DataTable();
        });
    </script>

    <!-- ====== ionicons ======= -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>