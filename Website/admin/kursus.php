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
    $nama_jenis = $_POST['nama_jenis'];
    $kategori = $_POST['kategori'];
    $keterangan = $_POST['keterangan'];
    $harga1 = $_POST['harga'];
    $harga = intval(preg_replace("/[^0-9]/", "", $harga1));

    $query_user = "INSERT INTO tb_jenis_pelatihan (nama_jenis, kategori, keterangan, harga) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query_user);
    $stmt->bind_param("sssi", $nama_jenis, $kategori, $keterangan, $harga);

    if ($stmt->execute()) {
        setNotification("Berhasil Tambah Data Kursus", "success");
        header("Location: kursus.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $id_jenis_pelatihan = $_POST['id_jenis_pelatihan'];

    $query_delete_user = "DELETE FROM tb_jenis_pelatihan WHERE id_jenis_pelatihan = ?";
    $stmt = $conn->prepare($query_delete_user);
    $stmt->bind_param("i", $id_jenis_pelatihan);

    if ($stmt->execute()) {
        setNotification("Berhasil Hapus Data Kursus", "success");
        header("Location: kursus.php");
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
        setNotification("Berhasil Update Data Kursus", "success");
        header("Location: kursus.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

$query = "SELECT * FROM tb_jenis_pelatihan";
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
                        <h2>Data Paket Kursus</h2>
                        <button class="btn" onclick="togglePopupTambah()">Tambah</button>
                    </div><br>

                    <table id="example">
                        <thead>
                            <tr>
                                <td>Jenis Pelatihan</td>
                                <td>Kategori Mobil</td>
                                <td>Keterangan</td>
                                <td>Harga</td>
                                <td>Action</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($data = mysqli_fetch_array($result)) { ?>
                                <tr>
                                    <td><?php echo $data['nama_jenis']; ?></td>
                                    <td><?php echo $data['kategori']; ?></td>
                                    <td><?php echo $data['keterangan']; ?></td>
                                    <td><?php echo "Rp " . number_format($data['harga'], 0, ',', '.'); ?></td>
                                    <td>
                                        <button class="btn-open-update"
                                            onclick="togglePopupUpdate('<?php echo $data['id_jenis_pelatihan']; ?>', '<?php echo $data['nama_jenis']; ?>', '<?php echo $data['kategori']; ?>', '<?php echo $data['keterangan']; ?>', '<?php echo $data['harga']; ?>')">UPDATE</button>
                                        <button class="btn-open-delete"
                                            onclick="togglePopupDelete('<?php echo $data['id_jenis_pelatihan']; ?>')">
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
            <h2 style="color: green;">Form Tambah Paket Kursus</h2>
            <br>
            <form action="kursus.php" method="post" class="form-container">
                <label class="form-label" for="nama_jenis">
                    Jenis Pelatihan:
                </label>
                <select class="form-input" name="nama_jenis" id="nama_jenis">
                    <option value="" disabled selected>Pilih Jenis</option>
                    <option value="Intensif + SIM">Instensif + SIM</option>
                    <option value="Intensif">Intensif</option>
                </select>

                <label class="form-label" for="kategori">
                    Kategori Mobil:
                </label>
                <select class="form-input" name="kategori" id="kategori">
                    <option value="" disabled selected>Pilih Jenis</option>
                    <option value="Mobil Matic">Mobil Matic</option>
                    <option value="Mobil Manual">Mobil Manual</option>
                </select>

                <label class="form-label" for="keterangan">
                    Keterangan:
                </label>
                <textarea class="form-input" id="keterangan" name="keterangan" rows="4" cols="50" required> </textarea>

                <label class="form-label" for="harga">
                    Harga:
                </label>
                <input class="form-input" type="text" id="harga" name="harga" required
                    oninput="formatInputRupiah(this)" />

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
            <h2 style="color: green;">Form Update Paket Kursus</h2>
            <br>
            <form action="jadwal.php" method="post" class="form-container">
                <input type="hidden" name="id_jenis_pelatihan" value="" />

                <label class="form-label" for="nama_jenis">
                    Jenis Pelatihan:
                </label>
                <select class="form-input" name="nama_jenis" id="update_nama_jenis">
                    <option value="" disabled selected>Pilih Jenis</option>
                    <option value="Intensif + SIM">Intensif + SIM</option>
                    <option value="Intensif">Intensif</option>
                </select>

                <label class="form-label" for="kategori">
                    Kategori Mobil:
                </label>
                <select class="form-input" name="kategori" id="update_kategori">
                    <option value="" disabled selected>Pilih Jenis</option>
                    <option value="Mobil Matic">Mobil Matic</option>
                    <option value="Mobil Manual">Mobil Manual</option>
                </select>

                <label class="form-label" for="keterangan">
                    Keterangan:
                </label>
                <textarea class="form-input" id="update_keterangan" name="keterangan" rows="4" cols="50"
                    required> </textarea>

                <label class="form-label" for="harga">
                    Harga:
                </label>
                <input class="form-input" type="text" id="update_harga" name="harga" required
                    oninput="formatInputRupiah(this)" />

                <button class="btn-submit" type="submit" name="update">
                    Submit
                </button>
            </form>

            <button class="btn-close-popup" onclick="closePopupUpdate()">
                Close
            </button>
        </div>
    </div>

    <div id="popupOverlayDelete" class="overlay-container">
        <div class="popup-box">
            <h2 style="color: red;">Konfirmasi Delete</h2>
            <br>
            <form action="kursus.php" method="post" class="form-container">
                <input type="hidden" name="id_jenis_pelatihan" value="" />
                <p>Apakah Anda yakin ingin menghapus data ini?</p>
                <button class="btn-submit" type="submit" name="delete">Ya</button>
                <button class="btn-close-popup" onclick="togglePopupDelete()">Tidak</button>
            </form>
        </div>
    </div>

    <script>
        function togglePopupTambah() {
            document.getElementById("popupOverlayTambah").classList.toggle("show");
        }

        function togglePopupDelete(id_jenis_pelatihan) {
            document.querySelector('#popupOverlayDelete [name="id_jenis_pelatihan"]').value = id_jenis_pelatihan;
            document.getElementById("popupOverlayDelete").classList.toggle("show");
        }

        function togglePopupUpdate(id_jenis_pelatihan, nama_jenis, kategori, keterangan, harga) {
            document.querySelector('#popupOverlayUpdate [name="id_jenis_pelatihan"]').value = id_jenis_pelatihan;

            const namaJenisSelect = document.querySelector('#popupOverlayUpdate [name="nama_jenis"]');
            const kategoriSelect = document.querySelector('#popupOverlayUpdate [name="kategori"]');

            namaJenisSelect.value = nama_jenis;
            Array.from(namaJenisSelect.options).forEach(option => {
                if (option.value === nama_jenis) {
                    option.selected = true;
                }
            });

            kategoriSelect.value = kategori;
            Array.from(kategoriSelect.options).forEach(option => {
                if (option.value === kategori) {
                    option.selected = true;
                }
            });

            document.querySelector('#popupOverlayUpdate [name="keterangan"]').value = keterangan;
            const hargaFormatted = formatRupiah(harga.toString());
            document.querySelector('#popupOverlayUpdate [name="harga"]').value = hargaFormatted;

            document.getElementById("popupOverlayUpdate").classList.toggle("show");
        }


        function closePopupUpdate() {
            document.getElementById("popupOverlayUpdate").classList.remove("show");
        }

        function formatRupiah(value) {
            let valueStr = value.replace(/[^,\d]/g, '');
            let formattedValue = valueStr.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            return formattedValue ? `Rp ${formattedValue}` : '';
        }

        function formatInputRupiah(input) {
            let value = input.value.replace(/[^,\d]/g, '');
            let formattedValue = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            input.value = formattedValue ? `Rp ${formattedValue}` : '';
        }

        $(document).ready(function () {
            $('#example').DataTable();
        });
    </script>
    <!-- ====== ionicons ======= -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <!-- =========== Scripts =========  -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="styles/app.js"></script>
</body>

</html>