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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah'])) {
    $nama_mobil = $_POST['nama_mobil'];
    $no_mobil = $_POST['no_mobil'];
    $tipe_mobil = $_POST['tipe_mobil'];

    $query_user = "INSERT INTO tb_mobil (nama_mobil, no_mobil, tipe_mobil) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query_user);
    $stmt->bind_param("sss", $nama_mobil, $no_mobil, $tipe_mobil);

    if ($stmt->execute()) {
        setNotification("Berhasil Menambah Data Mobil", "success");
        header("Location: mobil.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['perbaikan'])) {
    $id_mobil = $_POST['id_mobil'];
    $nominal = $_POST['nominal'];
    $file = $_FILES['file']['name'];
    $file_temp = $_FILES['file']['tmp_name'];
    $file_path = "img/" . $file;

    if (move_uploaded_file($file_temp, $file_path)) {
        $query_bensin = "INSERT INTO tb_perbaikan_mobil (id_mobil, gambarbuktimobil, nominal) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query_bensin);
        if ($stmt) {
            $stmt->bind_param("iss", $id_mobil, $file, $nominal);
            if ($stmt->execute()) {
                setNotification("Perbaikan Mobil", "success");
                header("Location: mobil.php");
                exit();
            } else {
                echo "Error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    } else {
        echo "Error uploading file.";
    }
    $conn->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $id_mobil = $_POST['id_mobil'];

    $query_delete_user = "DELETE FROM tb_mobil WHERE id_mobil = ?";
    $stmt = $conn->prepare($query_delete_user);
    $stmt->bind_param("i", $id_mobil);

    if ($stmt->execute()) {
        setNotification("Berhasil Hapus Data Mobil", "success");
        header("Location: mobil.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id_mobil = $_POST['id_mobil'];
    $nama_mobil = $_POST['nama_mobil'];
    $no_mobil = $_POST['no_mobil'];
    $tipe_mobil = $_POST['tipe_mobil'];

    $query_user = "UPDATE tb_mobil SET nama_mobil = ?, no_mobil = ?, tipe_mobil = ?  WHERE id_mobil = ?";
    $stmt = $conn->prepare($query_user);
    $stmt->bind_param("sssi", $nama_mobil, $no_mobil, $tipe_mobil, $id_mobil);

    if ($stmt->execute()) {
        setNotification("Berhasil Update Data Mobil", "success");
        header("Location: mobil.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

// Query untuk mendapatkan jumlah matic
$query = "SELECT COUNT(*) AS jumlah_matic FROM tb_mobil WHERE tipe_mobil = 'Mobil Matic'";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$jumlah_matic = $row['jumlah_matic'];

// Query untuk mendapatkan jumlah manual
$query = "SELECT COUNT(*) AS jumlah_manual FROM tb_mobil WHERE tipe_mobil = 'Mobil Manual'";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$jumlah_manual = $row['jumlah_manual'];

$query = "SELECT * FROM tb_mobil";
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

            <!-- ======================= Cards ================== -->
            <div class="cardBox">
                <div class="card">
                    <div>
                        <div class="numbers"><?php echo $jumlah_manual; ?></div>
                        <div class="cardName">Mobil Manual</div>
                    </div>

                    <div class="iconBx">
                        <ion-icon name="car-sport-outline"></ion-icon>
                    </div>
                </div>

                <div class="card">
                    <div>
                        <div class="numbers"><?php echo $jumlah_matic; ?></div>
                        <div class="cardName">Mobil Matic</div>
                    </div>

                    <div class="iconBx">
                        <ion-icon name="car-outline"></ion-icon>
                    </div>
                </div>
            </div>

            <!-- ================ Order Details List ================= -->
            <div class="details">
                <div class="recentOrders">
                    <div class="cardHeader">
                        <h2>Data Mobil</h2>
                        <button class="btn" onclick="togglePopupTambah()">Tambah</a>
                    </div><br>

                    <table id="example">
                        <thead>
                            <tr>
                                <td>Nama Mobil</td>
                                <td>Tipe Mobil</td>
                                <td>NO Plat Mobil</td>
                                <td>Action</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($data = mysqli_fetch_array($result)) { ?>
                                <tr>
                                    <td><?php echo $data['nama_mobil']; ?></td>
                                    <td><?php echo $data['tipe_mobil']; ?></td>
                                    <td><?php echo $data['no_mobil']; ?></td>
                                    <td>
                                        <button class="btn-open-update"
                                            onclick="togglePopupUpdate('<?php echo $data['id_mobil']; ?>', '<?php echo $data['nama_mobil']; ?>', '<?php echo $data['no_mobil']; ?>', '<?php echo $data['tipe_mobil']; ?>')">UPDATE</button>
                                        <button class="btn-open-delete"
                                            onclick="togglePopupDelete('<?php echo $data['id_mobil']; ?>', '<?php echo $data['nama_mobil']; ?>')">
                                            DELETE</button>
                                        <button class="btn-open-tambah"
                                            onclick="togglePopupPerbaikan('<?php echo $data['id_mobil']; ?>')">
                                            PERBAIKAN</button>
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
            <h2 style="color: green;">Form Tambah Mobil</h2>
            <br>
            <form action="mobil.php" method="post" class="form-container">
                <label class="form-label" for="nama_mobil">
                    Nama Mobil:
                </label>
                <input class="form-input" type="text" placeholder="Masukan Nama" id="nama_mobil" name="nama_mobil"
                    required>

                <label class="form-label" for="nama_mobil">
                    No.Plat Mobil:
                </label>
                <input class="form-input" type="text" placeholder="Masukan No.Plat Mobil" id="no_mobil" name="no_mobil"
                    required>

                <label class="form-label" for="tipe_mobil">
                    Tipe Mobil:
                </label>
                <select class="form-input" name="tipe_mobil" id="tipe_mobil">
                    <option value="" disabled selected>Pilih Tipe</option>
                    <option value="Mobil Matic">Mobil Matic</option>
                    <option value="Mobil Manual">Mobil Manual</option>
                </select>

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
            <h2 style="color: green;">Form Update Mobil</h2>
            <br>
            <form action="mobil.php" method="post" class="form-container">
                <input type="hidden" name="id_mobil" value="">
                <label class="form-label" for="nama_mobil">
                    Nama Mobil:
                </label>
                <input class="form-input" type="text" placeholder="Masukan Nama" id="nama_mobil" name="nama_mobil"
                    required>

                <label class="form-label" for="nama_mobil">
                    No.Plat Mobil:
                </label>
                <input class="form-input" type="text" placeholder="Masukan No.Plat Mobil" id="no_mobil" name="no_mobil"
                    required>

                <label class="form-label" for="tipe_mobil">
                    Tipe Mobil:
                </label>
                <select class="form-input" name="tipe_mobil" id="tipe_mobil">
                    <option value="" disabled selected>Pilih Tipe</option>
                    <option value="Mobil Matic">Mobil Matic</option>
                    <option value="Mobil Manual">Mobil Manual</option>
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
            <h2 style="color: green;">Delete Mobil</h2>
            <form action="mobil.php" method="post" class="form-container">
                <input type="hidden" name="id_mobil" value="">
                <label class="form-label" for="nama_mobil">
                    Yakin Menghapus :
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

    <div id="popupOverlayPerbaikan" class="overlay-container">
        <div class="popup-box">
            <h2 style="color: green;">Perbaikan Mobil</h2>
            <form action="mobil.php" method="post" class="form-container" enctype="multipart/form-data">
                <input type="hidden" name="id_mobil" value="">
                <label class="form-label" for="file">
                    Bukti Struk Perbaikan:
                </label>
                <input class="form-input" type="file" id="file" name="file">
                <input class="form-input" type="text" id="nominal" name="nominal" placeholder="Nominal" required>
                <button class="btn-submit" type="submit" name="perbaikan">
                    Submit
                </button>
            </form>

            <button class="btn-close-popup" onclick="togglePopupPerbaikan()">
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

        function togglePopupUpdate(id_mobil, nama_mobil, no_mobil, tipe_mobil) {
            const overlay = document.getElementById('popupOverlayUpdate');
            overlay.classList.toggle('show');

            if (id_mobil && nama_mobil && no_mobil && tipe_mobil) {
                document.querySelector('#popupOverlayUpdate input[name="id_mobil"]').value = id_mobil;
                document.querySelector('#popupOverlayUpdate input[name="nama_mobil"]').value = nama_mobil;
                document.querySelector('#popupOverlayUpdate input[name="no_mobil"]').value = no_mobil;
                document.querySelector('#popupOverlayUpdate select[name="tipe_mobil"]').value = tipe_mobil;
            }
        }

        function togglePopupDelete(id_mobil, nama_mobil) {
            const overlay = document.getElementById('popupOverlayDelete');
            overlay.classList.toggle('show');

            if (id_mobil && nama_mobil) {
                document.querySelector('#popupOverlayDelete input[name="id_mobil"]').value = id_mobil;
                document.querySelector('#popupOverlayDelete .form-label').textContent = 'Yakin Menghapus : ' + nama_mobil +
                    '?';
            }
        }

        function togglePopupPerbaikan(id_mobil) {
            const overlay = document.getElementById('popupOverlayPerbaikan');
            overlay.classList.toggle('show');

            if (id_mobil) {
                document.querySelector('#popupOverlayPerbaikan input[name="id_mobil"]').value = id_mobil;
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