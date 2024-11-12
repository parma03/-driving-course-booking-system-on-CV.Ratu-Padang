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
    $id_mobil = $_POST['id_mobil'];
    $nama_buku = $_POST['nama_buku'];
    $file = $_FILES['file']['name'];
    $file_temp = $_FILES['file']['tmp_name'];
    $file_path = "buku/" . $file;

    // Pindahkan file ke direktori 
    if (move_uploaded_file($file_temp, $file_path)) {
        $query_buku = "INSERT INTO tb_buku (id_mobil, nama_buku, file) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query_buku);
        if ($stmt) {
            $stmt->bind_param("iss", $id_mobil, $nama_buku, $file);

            if ($stmt->execute()) {
                setNotification("Berhasil Menambah Data Buku", "success");
                header("Location: buku.php");
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
    $id_buku = $_POST['id_buku'];

    // Dapatkan nama file yang akan dihapus
    $query_get_file = "SELECT file FROM tb_buku WHERE id_buku = ?";
    $stmt = $conn->prepare($query_get_file);
    $stmt->bind_param("i", $id_buku);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $file = $row['file'];

    // Hapus record dari database
    $query_delete_buku = "DELETE FROM tb_buku WHERE id_buku = ?";
    $stmt = $conn->prepare($query_delete_buku);
    $stmt->bind_param("i", $id_buku);

    if ($stmt->execute()) {
        // Hapus file dari server
        $file_path = "buku/" . $file;
        if (file_exists($file_path)) {
            unlink($file_path); // Hapus file
        }
        setNotification("Berhasil Hapus Data Buku", "success");
        header("Location: buku.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id_buku = $_POST['id_buku'];
    $id_mobil = $_POST['id_mobil'];
    $nama_buku = $_POST['nama_buku'];

    // Periksa apakah file diupload
    if (!empty($_FILES['file']['name'])) {
        $file = $_FILES['file']['name'];
        $file_temp = $_FILES['file']['tmp_name'];
        $file_path = "buku/" . $file;

        // Hapus file lama jika ada
        $query_hapus_buku = "SELECT file FROM tb_buku WHERE id_buku = ?";
        $stmt = $conn->prepare($query_hapus_buku);
        $stmt->bind_param("i", $id_buku);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $file_lama = $row['file'];

        if (!empty($file_lama)) {
            $file_lama_path = "buku/" . $file_lama;
            if (file_exists($file_lama_path)) {
                unlink($file_lama_path); // Hapus file lama
            }
        }

        // Pindahkan file baru ke direktori
        move_uploaded_file($file_temp, $file_path);

        // Update query untuk memasukkan data baru ke dalam database
        $query_update = "UPDATE tb_buku SET id_mobil = ?, nama_buku = ?, file = ? WHERE id_buku = ?";
        $stmt = $conn->prepare($query_update);
        $stmt->bind_param("issi", $id_mobil, $nama_buku, $file, $id_buku);
    } else {
        // Jika file tidak diupload, hanya update id_mobil dan nama_buku
        $query_update = "UPDATE tb_buku SET id_mobil = ?, nama_buku = ? WHERE id_buku = ?";
        $stmt = $conn->prepare($query_update);
        $stmt->bind_param("isi", $id_mobil, $nama_buku, $id_buku);
    }

    if ($stmt->execute()) {
        setNotification("Berhasil Update Data Buku", "success");
        header("Location: buku.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

$query = "SELECT * FROM tb_buku LEFT JOIN tb_mobil ON tb_buku.id_mobil = tb_mobil.id_mobil";
$result = $conn->query($query);

// Query untuk mendapatkan data mobil
$query_mobil = "SELECT * FROM tb_mobil";
$result_mobil = $conn->query($query_mobil);

// Query untuk mendapatkan data mobil
$query_mobil1 = "SELECT * FROM tb_mobil";
$result_mobil1 = $conn->query($query_mobil1);
$mobils = [];
while ($mobil = mysqli_fetch_array($result_mobil1)) {
    $mobils[] = $mobil;
}

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

            <!-- ================ Order Details List ================= -->
            <div class="details">
                <div class="recentOrders">
                    <div class="cardHeader">
                        <h2>Data Buku Panduan</h2>
                        <button class="btn" onclick="togglePopupTambah()">Tambah</a>
                    </div><br>

                    <table id="example">
                        <thead>
                            <tr>
                                <td>Nama Buku</td>
                                <td>Nama Mobil</td>
                                <td>File Buku</td>
                                <td>Action</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($data = mysqli_fetch_array($result)) { ?>
                                <tr>
                                    <td><?php echo $data['nama_buku']; ?></td>
                                    <td><?php echo $data['nama_mobil']; ?> - <?php echo $data['tipe_mobil']; ?></td>
                                    <td>
                                        <a class="btn-open-download" download="<?php echo $data['file']; ?>"
                                            href="buku/<?php echo $data['file']; ?>">
                                            <ion-icon name="download-outline"></ion-icon> Download
                                        </a>
                                    </td>
                                    <td>
                                        <button class="btn-open-update"
                                            onclick="togglePopupUpdate('<?php echo $data['id_buku']; ?>', '<?php echo $data['id_mobil']; ?>', '<?php echo $data['nama_buku']; ?>', '<?php echo $data['file']; ?>')">UPDATE</button>
                                        <button class="btn-open-delete"
                                            onclick="togglePopupDelete('<?php echo $data['id_buku']; ?>')">
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
            <h2 style="color: green;">Form Tambah Buku</h2>
            <br>
            <form action="buku.php" method="post" class="form-container" enctype="multipart/form-data">
                <label class="form-label" for="nama_buku">
                    Nama Buku:
                </label>
                <input class="form-input" type="text" placeholder="Masukan Nama Buku" id="nama_buku" name="nama_buku"
                    required>

                <label class="form-label" for="id_mobil">
                    Mobil:
                </label>
                <select class="form-input" name="id_mobil" id="id_mobil" required>
                    <option value="" disabled selected>Pilih Mobil</option>
                    <?php while ($mobil = mysqli_fetch_array($result_mobil)) { ?>
                        <option value="<?php echo $mobil['id_mobil']; ?>">
                            <?php echo $mobil['nama_mobil'] . ' - ' . $mobil['tipe_mobil']; ?>
                        </option>
                    <?php } ?>
                </select>

                <label class="form-label" for="file">
                    File Buku:
                </label>
                <input class="form-input" type="file" id="file" name="file" required>

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
            <h2 style="color: green;">Form Update Buku</h2>
            <br>
            <form action="buku.php" method="post" class="form-container" enctype="multipart/form-data">
                <input type="hidden" name="id_buku" value="">
                <label class="form-label" for="nama_buku">
                    Nama Buku:
                </label>
                <input class="form-input" type="text" placeholder="Masukan Nama Buku" id="nama_buku" name="nama_buku"
                    required>

                <label class="form-label" for="tipe_mobil">
                    Mobil:
                </label>
                <select class="form-input" name="id_mobil" id="id_mobil_update">
                    <option value="" disabled selected>Pilih Mobil</option>
                    <?php foreach ($mobils as $mobil) { ?>
                        <option value="<?php echo $mobil['id_mobil']; ?>">
                            <?php echo $mobil['nama_mobil'] . ' - ' . $mobil['tipe_mobil']; ?>
                        </option>
                    <?php } ?>
                </select>

                <label class="form-label" for="file">
                    File Buku:
                </label>
                <input class="form-input" type="text" id="file_buku" name="file_buku" disabled>
                <input class="form-input" type="file" id="file" name="file">

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
            <h2 style="color: green;">Delete Buku</h2>
            <form action="mobil.php" method="post" class="form-container">
                <input type="hidden" name="id_buku" value="">
                <label class="form-label" for="id_buku">
                    Yakin Menghapus File Ini?
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

        function togglePopupUpdate(id_buku, id_mobil, nama_buku, file) {
            const overlay = document.getElementById('popupOverlayUpdate');
            overlay.classList.toggle('show');

            if (id_buku && id_mobil && nama_buku && file) {
                document.querySelector('#popupOverlayUpdate input[name="id_buku"]').value = id_buku;
                document.querySelector('#popupOverlayUpdate select[name="id_mobil"]').value = id_mobil;
                document.querySelector('#popupOverlayUpdate input[name="nama_buku"]').value = nama_buku;
                document.querySelector('#popupOverlayUpdate input[name="file_buku"]').value = file;
                // Set nilai default pada select
                const selectMobil = document.querySelector('#popupOverlayUpdate select[name="id_mobil"]');
                for (let option of selectMobil.options) {
                    if (option.value == id_mobil) {
                        option.selected = true;
                        break;
                    }
                }
            }
        }

        function togglePopupDelete(id_buku) {
            const overlay = document.getElementById('popupOverlayDelete');
            overlay.classList.toggle('show');

            if (id_mobil && nama_mobil) {
                document.querySelector('#popupOverlayDelete input[name="id_buku"]').value = id_buku;
                document.querySelector('#popupOverlayDelete .form-label').textContent = 'Yakin Menghapus File Ini ? ';
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