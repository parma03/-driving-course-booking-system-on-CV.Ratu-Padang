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


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah'])) {
    $hari = $_POST['hari'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];

    $query_user = "INSERT INTO tb_jadwal (hari, jam_mulai, jam_selesai) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query_user);
    $stmt->bind_param("sss", $hari, $jam_mulai, $jam_selesai);

    if ($stmt->execute()) {
        setNotification("Berhasil Menambah Data Jadwal", "success");
        header("Location: jadwal.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $id_jadwal = $_POST['id_jadwal'];

    $query_delete_user = "DELETE FROM tb_jadwal WHERE id_jadwal = ?";
    $stmt = $conn->prepare($query_delete_user);
    $stmt->bind_param("i", $id_jadwal);

    if ($stmt->execute()) {
        setNotification("Berhasil Hapus Data Jadwal", "success");
        header("Location: jadwal.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id_jadwal = $_POST['id_jadwal'];
    $hari = $_POST['hari'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];

    $query_user = "UPDATE tb_jadwal SET hari = ?, jam_mulai = ?, jam_selesai= ?  WHERE id_jadwal = ?";
    $stmt = $conn->prepare($query_user);
    $stmt->bind_param("sssi", $hari, $jam_mulai, $jam_selesai, $id_jadwal);

    if ($stmt->execute()) {
        setNotification("Berhasil Update Data Jadwal", "success");
        header("Location: jadwal.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

$query = "SELECT * FROM tb_jadwal";
$result = $conn->query($query);

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

            <!-- ================ Order Details List ================= -->
            <div class="details">
                <div class="recentOrders">
                    <div class="cardHeader">
                        <h2>Data Jadwal</h2>
                        <button class="btn" onclick="togglePopupTambah()">Tambah</a>
                    </div><br>

                    <table id="example">
                        <thead>
                            <tr>
                                <td>Hari</td>
                                <td>Jam Mulai</td>
                                <td>Jam Selesai</td>
                                <td>Action</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($data = mysqli_fetch_array($result)) { ?>
                                <tr>
                                    <td>
                                        <?php if ($data['hari'] === "Jumat") {
                                            echo "Jum'at";
                                        } else {
                                            echo $data["hari"];
                                        } ?>
                                    </td>
                                    <td><?php echo $data['jam_mulai']; ?></td>
                                    <td><?php echo $data['jam_selesai']; ?></td>
                                    <td>
                                        <button class="btn-open-update"
                                            onclick="togglePopupUpdate('<?php echo $data['id_jadwal']; ?>', '<?php echo $data['hari']; ?>', '<?php echo $data['jam_mulai']; ?>', '<?php echo $data['jam_selesai']; ?>')">UPDATE</button>
                                        <button class="btn-open-delete"
                                            onclick="togglePopupDelete('<?php echo $data['id_jadwal']; ?>')">
                                            DELETE</button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="popupOverlayTambah" class="overlay-container">
        <div class="popup-box">
            <h2 style="color: green;">Form Tambah Jadwal</h2>
            <br>
            <form action="jadwal.php" method="post" class="form-container">
                <label class="form-label" for="hari">
                    Hari:
                </label>
                <select class="form-input" name="hari" id="hari">
                    <option value="" disabled selected>Pilih Hari</option>
                    <option value="Senin">Senin</option>
                    <option value="Selasa">Selasa</option>
                    <option value="Rabu">Rabu</option>
                    <option value="Kamis">Kamis</option>
                    <option value="Jumat">Jum'at</option>
                    <option value="Sabtu">Sabtu</option>
                </select>

                <center>
                    <label class="form-label">
                        Jam Kerja 9am - 6pm
                    </label>
                </center>

                <label class="form-label" for="jam_mulai">
                    Jam Mulai:
                </label>
                <input class="form-input" type="time" id="jam_mulai" name="jam_mulai" min="09:00" max="18:00"
                    required />

                <label class="form-label" for="jam_selesai">
                    Jam Selesai:
                </label>
                <input class="form-input" type="time" id="jam_selesai" name="jam_selesai" min="09:00" max="18:00"
                    required />

                <button class="btn-submit" type="submit" name="tambah">
                    Submit
                </button>
            </form>

            <button class="btn-close-popup" onclick="togglePopupTambah()">
                Close
            </button>
        </div>
    </div>

    <div id="popupOverlayUpdate" class="overlay-container">
        <div class="popup-box">
            <h2 style="color: green;">Form Update Jadwal</h2>
            <br>
            <form action="jadwal.php" method="post" class="form-container">
                <input type="hidden" name="id_jadwal" value="">
                <label class="form-label" for="hari">
                    Hari:
                </label>
                <select class="form-input" name="hari" id="hari">
                    <option value="" disabled selected>Pilih Hari</option>
                    <option value="Senin">Senin</option>
                    <option value="Selasa">Selasa</option>
                    <option value="Rabu">Rabu</option>
                    <option value="Kamis">Kamis</option>
                    <option value="Jumat">Jum'at</option>
                    <option value="Sabtu">Sabtu</option>
                </select>

                <center>
                    <label class="form-label">
                        Jam Kerja 9am - 6pm
                    </label>
                </center>

                <label class="form-label" for="jam_mulai">
                    Jam Mulai:
                </label>
                <input class="form-input" type="time" id="jam_mulai" name="jam_mulai" min="09:00" max="18:00"
                    required />

                <label class="form-label" for="jam_selesai">
                    Jam Selesai:
                </label>
                <input class="form-input" type="time" id="jam_selesai" name="jam_selesai" min="09:00" max="18:00"
                    required />

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
            <h2 style="color: green;">Delete Jadwal</h2>
            <form action="jadwal.php" method="post" class="form-container">
                <input type="hidden" name="id_jadwal" value="">
                <label class="form-label" for="name">
                    Yakin Menghapus Jadwal
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

    <!-- =========== Scripts =========  -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="styles/app.js"></script>

    <script>
        function togglePopupTambah() {
            const overlay = document.getElementById('popupOverlayTambah');
            overlay.classList.toggle('show');
        }

        function togglePopupUpdate(id_jadwal, hari, jam_mulai, jam_selesai) {
            const overlay = document.getElementById('popupOverlayUpdate');
            overlay.classList.toggle('show');

            if (id_jadwal && hari && jam_mulai && jam_selesai) {
                document.querySelector('#popupOverlayUpdate input[name="id_jadwal"]').value = id_jadwal;
                document.querySelector('#popupOverlayUpdate select[name="hari"]').value = hari;
                document.querySelector('#popupOverlayUpdate input[name="jam_mulai"]').value = jam_mulai;
                document.querySelector('#popupOverlayUpdate input[name="jam_selesai"]').value = jam_selesai;
            }
        }

        function togglePopupDelete(id_jadwal) {
            const overlay = document.getElementById('popupOverlayDelete');
            overlay.classList.toggle('show');

            if (id_jadwal) {
                document.querySelector('#popupOverlayDelete input[name="id_jadwal"]').value = id_jadwal;
                document.querySelector('#popupOverlayDelete .form-label').textContent = 'Yakin Menghapus Jadwal ?';
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