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


$id_instruktur = $_SESSION['id_user'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah_nilaiteori'])) {
    $id_pelatihan = $_POST['id_pelatihan'];
    $nilai_teori = $_POST['nilai_teori'];

    $query_tambah_nilai_teori = "INSERT INTO tb_nilai (id_pelatihan, nilai_teori) VALUES (?, ?)";
    $stmt = $conn->prepare($query_tambah_nilai_teori);
    $stmt->bind_param("ii", $id_pelatihan, $nilai_teori);
    if ($stmt->execute()) {
        setNotification("Berhasil Tambah Nilai Teori", "success");
        header("Location: pelatihan.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_nilaiteori'])) {
    $id_nilai = $_POST['id_nilai'];
    $nilai_teori = $_POST['nilai_teori'];

    $query_update_nilai_teori = "UPDATE tb_nilai SET nilai_teori = ? WHERE id_nilai = ?";
    $stmt = $conn->prepare($query_update_nilai_teori);
    $stmt->bind_param("ii", $nilai_teori, $id_nilai);
    if ($stmt->execute()) {
        setNotification("Berhasil Update Nilai Teori", "success");
        header("Location: pelatihan.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah_nilaipraktek'])) {
    $id_nilai = $_POST['id_nilai'];
    $nilai_percaya_diri = $_POST['nilai_percaya_diri'];
    $nilai_kesopanan_mengemudi = $_POST['nilai_kesopanan_mengemudi'];
    $nilai_kepatuhan_lalin = $_POST['nilai_kepatuhan_lalin'];
    $nilai_sikap = $_POST['nilai_sikap'];
    $nilai_pengetahuan_kendaraan = $_POST['nilai_pengetahuan_kendaraan'];
    $nilai_keamanan = $_POST['nilai_keamanan'];

    $query_tambah_nilai_praktek = "UPDATE tb_nilai SET nilai_percaya_diri = ?, nilai_kesopanan_mengemudi = ?, nilai_kepatuhan_lalin = ?, nilai_sikap = ?, nilai_pengetahuan_kendaraan = ?, nilai_keamanan = ? WHERE id_nilai = ?";
    $stmt = $conn->prepare($query_tambah_nilai_praktek);
    $stmt->bind_param("iiiiiii", $nilai_percaya_diri, $nilai_kesopanan_mengemudi, $nilai_kepatuhan_lalin, $nilai_sikap, $nilai_pengetahuan_kendaraan, $nilai_keamanan, $id_nilai);
    if ($stmt->execute()) {
        setNotification("Berhasil Tambah Nilai Praktek", "success");
        header("Location: pelatihan.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['selesai'])) {
    $id_pelatihan = $_POST['id_pelatihan'];
    $nominal = $_POST['nominal'];
    $file = $_FILES['file']['name'];
    $file_temp = $_FILES['file']['tmp_name'];
    $file_path = "img/" . $file;

    if (move_uploaded_file($file_temp, $file_path)) {
        $query_bensin = "INSERT INTO tb_bensin (id_pelatihan, gambarbukti, nominal) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query_bensin);
        if ($stmt) {
            $stmt->bind_param("iss", $id_pelatihan, $file, $nominal);
            if ($stmt->execute()) {
                $query_selesai = "UPDATE tb_pelatihan SET status = 'Selesai' WHERE id_pelatihan = ?";
                $stmt = $conn->prepare($query_selesai);
                $stmt->bind_param("i", $id_pelatihan);
                $stmt->execute();
                setNotification("Pelatihan Diselesaikan", "success");
                header("Location: pelatihan.php");
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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah_buku'])) {
    $id_pelatihan = $_POST['id_pelatihan'];
    $id_buku = $_POST['id_buku'];

    $query_tambah_buku = "UPDATE tb_pelatihan SET id_buku = ? WHERE id_pelatihan = ?";
    $stmt = $conn->prepare($query_tambah_buku);
    $stmt->bind_param("ii", $id_buku, $id_pelatihan);
    if ($stmt->execute()) {
        setNotification("Berhasil Tambah Panduan", "success");
        header("Location: pelatihan.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah_absensi'])) {
    $id_pelatihan = $_POST['id_pelatihan'];
    $status_absensi = $_POST['status_absensi'];
    $stat = 1;
    $stats = 0;

    if ($status_absensi === "hadir") {
        $query_tambah_hadir = "INSERT INTO tb_absensi (id_pelatihan, hadir, absen) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query_tambah_hadir);
        $stmt->bind_param("iii", $id_pelatihan, $stat, $stats);
        if ($stmt->execute()) {
            $last_id = $stmt->insert_id;
            $query_tambah_hadir = "UPDATE tb_pelatihan SET id_absensi = ? WHERE id_pelatihan = ?";
            $stmt = $conn->prepare($query_tambah_hadir);
            $stmt->bind_param("ii", $last_id, $id_pelatihan);
            $stmt->execute();
            setNotification("Berhasil Absensi", "success");
            header("Location: pelatihan.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    } elseif ($status_absensi === "absen") {
        $query_tambah_hadir = "INSERT INTO tb_absensi (id_pelatihan, hadir, absen) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query_tambah_hadir);
        $stmt->bind_param("iii", $id_pelatihan, $stats, $stat);
        if ($stmt->execute()) {
            $last_id = $stmt->insert_id;
            $query_tambah_hadir = "UPDATE tb_pelatihan SET id_absensi = ? WHERE id_pelatihan = ?";
            $stmt = $conn->prepare($query_tambah_hadir);
            $stmt->bind_param("ii", $last_id, $id_pelatihan);
            $stmt->execute();
            setNotification("Berhasil Absensi", "success");
            header("Location: pelatihan.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_absensi'])) {
    $id_absensi = $_POST['id_absensi'];
    $status_absensi = $_POST['status_absensi'];
    $hadir = $_POST['hadir'];
    $absen = $_POST['absen'];
    $hadirtambah = $hadir + 1;
    $absentambah = $absen + 1;

    if ($status_absensi === "hadir") {
        $query_tambah_buku = "UPDATE tb_absensi SET hadir = ? WHERE id_absensi = ?";
        $stmt = $conn->prepare($query_tambah_buku);
        $stmt->bind_param("ii", $hadirtambah, $id_absensi);
        if ($stmt->execute()) {
            setNotification("Berhasil Absensi", "success");
            header("Location: pelatihan.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    } elseif ($status_absensi === "absen") {
        $query_tambah_buku = "UPDATE tb_absensi SET absen = ? WHERE id_absensi = ?";
        $stmt = $conn->prepare($query_tambah_buku);
        $stmt->bind_param("ii", $absentambah, $id_absensi);
        if ($stmt->execute()) {
            setNotification("Berhasil Absensi", "success");
            header("Location: pelatihan.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    }
}

$query = "SELECT 
    tb_pelatihan.id_pelatihan, 
    tb_pelatihan.id_instruktur,
    tb_pelatihan.status, 
    tb_konsumen.name_konsumen, 
    tb_jenis_pelatihan.nama_jenis, 
    tb_jenis_pelatihan.keterangan, 
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
    tb_mobil.id_mobil,
    tb_mobil.nama_mobil,
    tb_mobil.tipe_mobil,
    tb_buku.id_buku,
    tb_buku.id_mobil,
    tb_buku.nama_buku,
    tb_buku.file,
    SUM(CASE WHEN tb_absensi.keterangan_absensi = 'Hadir' THEN 1 ELSE 0 END) AS jumlah_hadir,
    SUM(CASE WHEN tb_absensi.keterangan_absensi = 'Absen' THEN 1 ELSE 0 END) AS jumlah_absen,
    COUNT(tb_absensi.id_absensi) AS total_absensi
FROM 
    tb_pelatihan 
INNER JOIN tb_konsumen ON tb_pelatihan.id_konsumen = tb_konsumen.id_konsumen 
LEFT JOIN tb_instruktur ON tb_pelatihan.id_instruktur = tb_instruktur.id_instruktur 
INNER JOIN tb_jadwal ON tb_pelatihan.id_jadwal = tb_jadwal.id_jadwal 
INNER JOIN tb_jenis_pelatihan ON tb_pelatihan.id_jenis_pelatihan = tb_jenis_pelatihan.id_jenis_pelatihan 
INNER JOIN tb_mobil ON tb_pelatihan.id_mobil = tb_mobil.id_mobil 
LEFT JOIN tb_absensi ON tb_pelatihan.id_pelatihan = tb_absensi.id_pelatihan
LEFT JOIN tb_nilai ON tb_pelatihan.id_pelatihan = tb_nilai.id_pelatihan 
LEFT JOIN tb_buku ON tb_pelatihan.id_buku = tb_buku.id_buku
WHERE 
    tb_pelatihan.id_instruktur = '$id_instruktur'
GROUP BY
    tb_pelatihan.id_pelatihan, 
    tb_pelatihan.id_instruktur,
    tb_pelatihan.status, 
    tb_konsumen.name_konsumen, 
    tb_jenis_pelatihan.nama_jenis, 
    tb_jenis_pelatihan.keterangan, 
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
    tb_mobil.id_mobil,
    tb_mobil.nama_mobil,
    tb_mobil.tipe_mobil,
    tb_buku.id_buku,
    tb_buku.id_mobil,
    tb_buku.nama_buku,
    tb_buku.file";
$result = $conn->query($query);

// Query untuk mendapatkan data soal
$query_modul = "SELECT * FROM tb_buku";
$result_modul = $conn->query($query_modul);

$query_modul1 = "SELECT * FROM tb_buku";
$result_modul1 = $conn->query($query_modul1);
$moduls = [];
while ($modul = mysqli_fetch_array($result_modul1)) {
    $moduls[] = $modul;
}

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

        .action-buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .action-buttons a,
        .action-buttons button {
            margin: 0 5px;
        }

        .info-text {
            display: block;
            margin-bottom: 10px;
            padding: 5px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
            border-radius: 4px;
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
                        <h2>Data Pelatihan</h2>
                    </div>

                    <table id="example">
                        <thead>
                            <tr>
                                <td>Nama Peserta</td>
                                <td>Tipe Kursus</td>
                                <td>Jadwal</td>
                                <td>Absensi</td>
                                <td>Modul</td>
                                <td>Soal</td>
                                <td>Nilai Teori</td>
                                <td>Nilai Praktek</td>
                                <td>Status</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($data = mysqli_fetch_array($result)) { ?>
                                <tr>
                                    <td><?php echo $data['name_konsumen']; ?></td>
                                    <td>( <?php echo $data['nama_jenis']; ?> ) <?php echo $data['keterangan']; ?></td>
                                    <td><?php echo $data['hari']; ?> (<?php echo $data['jam_mulai']; ?> -
                                        <?php echo $data['jam_selesai']; ?>)
                                    </td>
                                    <td>
                                        <button class="btn-open-tambah"
                                            onclick="togglePopupTambahAbsensi('<?php echo $data['id_pelatihan']; ?>', '<?php echo $data['total_absensi']; ?>', '<?php echo $data['jumlah_absen']; ?>', '<?php echo $data['jumlah_hadir']; ?>')">Lihat</button>
                                    </td>
                                    <td>
                                        <?php
                                        switch (true) {
                                            case ($data['id_buku'] === NULL): ?>
                                                <button class="btn-open-tambah"
                                                    onclick="togglePopupTambahBuku('<?php echo $data['id_pelatihan']; ?>')">Tambah</button>
                                                <?php
                                                break;
                                            case ($data['id_buku'] != NULL): ?>
                                                <div class="action-buttons">
                                                    <a class="btn-open-download" download="<?php echo $data['file']; ?>"
                                                        href="soal/<?php echo $data['file']; ?>"> Download
                                                    </a>
                                                    <button class="btn-open-update"
                                                        onclick="togglePopupUpdateBuku('<?php echo $data['id_pelatihan']; ?>', '<?php echo $data['id_buku']; ?>')">Update
                                                    </button>
                                                </div>
                                                <?php
                                                break;
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <button class="btn-open-tambah"
                                            onclick="togglePopupLihatSoal('<?php echo $data['id_pelatihan']; ?>')">Lihat</button>
                                    </td>
                                    <td>
                                        <?php
                                        switch (true) {
                                            case ($data['nilai_teori'] === NULL): ?>
                                                <button class="btn-open-tambah"
                                                    onclick="togglePopupTambahTeori('<?php echo $data['id_pelatihan']; ?>')">Tambah</button>
                                                <?php
                                                break;
                                            case ($data['nilai_teori'] != NULL):
                                                if ($data['status'] === "Selesai") {
                                                    echo $data['nilai_teori'];
                                                } else { ?>
                                                    <button class="btn-open-update"
                                                        onclick="togglePopupUpdateTeori('<?php echo $data['id_nilai']; ?>', '<?php echo $data['nilai_teori']; ?>')">Update</button>
                                                    <?php
                                                }
                                                break;
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        switch (true) {
                                            case ($data['total_absensi'] === NULL):
                                                ?>
                                                <span class="status pending">Proses</span>
                                                <?php
                                                break;
                                            case ($data['total_absensi'] == 14 && empty($data['nilai_percaya_diri'])):
                                                ?>
                                                <button class="btn-open-tambah"
                                                    onclick="togglePopupTambahNilai('<?php echo $data['id_nilai']; ?>')">Tambah</button>
                                                <?php
                                                break;
                                            case (!empty($data['nilai_percaya_diri'])):
                                                if ($data['status'] === "Selesai") {
                                                    $nilai_praktik = $data['nilai_percaya_diri'] + $data['nilai_kesopanan_mengemudi'] + $data['nilai_kepatuhan_lalin'] + $data['nilai_sikap'] + $data['nilai_pengetahuan_kendaraan'] + $data['nilai_keamanan'];
                                                    echo $nilai_praktik . '/30';
                                                } else { ?>
                                                    <div class="action-buttons">
                                                        <button class="btn-open-update"
                                                            onclick="togglePopupUpdateNilai('<?php echo $data['id_nilai']; ?>', '<?php echo $data['nilai_percaya_diri']; ?>', '<?php echo $data['nilai_kesopanan_mengemudi']; ?>', '<?php echo $data['nilai_kepatuhan_lalin']; ?>', '<?php echo $data['nilai_sikap']; ?>', '<?php echo $data['nilai_pengetahuan_kendaraan']; ?>', '<?php echo $data['nilai_keamanan']; ?>')">Update</button>
                                                        /
                                                        <button class="btn-open-download"
                                                            onclick="togglePopupSelesaiPelatihan('<?php echo $data['id_pelatihan']; ?>')">Selesai</button>
                                                    </div>
                                                    <?php
                                                }
                                                break;
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($data['status'] === "Selesai") { ?>
                                            <a href="hasil.php?id_nilai=<?php echo $data['id_nilai']; ?>"
                                                class="btn-open-download">Nilai</a>
                                        <?php } else { ?>
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

    <div id="popupOverlayTambahTeori" class="overlay-container">
        <div class="popup-box">
            <h2 style="color: green;">Tambah Nilai Teori</h2>
            <br>
            <form action="pelatihan.php" method="post" class="form-container">
                <input type="hidden" name="id_pelatihan" value="">

                <label class="form-label">
                    Nilai Teori:
                </label>
                <input type="number" name="nilai_teori" class="form-input" placeholder="Nilai 1-50" step="1" min="1"
                    oninput="this.value = this.value > 50 ? 50 : Math.abs(this.value)" required>

                <button class="btn-submit" type="submit" name="tambah_nilaiteori">
                    Submit
                </button>
            </form>

            <button class="btn-close-popup" onclick="togglePopupTambahTeori()">
                Close
            </button>
        </div>
    </div>

    <div id="popupOverlayUpdateTeori" class="overlay-container">
        <div class="popup-box">
            <h2 style="color: green;">Update Nilai Teori</h2>
            <br>
            <form action="pelatihan.php" method="post" class="form-container">
                <input type="hidden" name="id_nilai" value="">

                <label class="form-label">
                    Nilai Teori:
                </label>
                <input type="number" name="nilai_teori" class="form-input" placeholder="Nilai 1-50" step="1" min="1"
                    oninput="this.value = this.value > 50 ? 50 : Math.abs(this.value)" required>

                <button class="btn-submit" type="submit" name="update_nilaiteori">
                    Submit
                </button>
            </form>

            <button class="btn-close-popup" onclick="togglePopupUpdateTeori()">
                Close
            </button>
        </div>
    </div>

    <div id="popupOverlayTambahNilai" class="overlay-container">
        <div class="popup-box">
            <h2 style="color: green;">Tambah Nilai Praktek</h2>
            <br>
            <form action="pelatihan.php" method="post" class="form-container">
                <input type="hidden" name="id_nilai" value="">

                <label class="form-label">
                    Percaya Diri Saat Mengemudi:
                </label>
                <input type="number" name="nilai_percaya_diri" class="form-input" placeholder="Nilai 1-5" step="1"
                    min="1" oninput="this.value = this.value > 5 ? 5 : Math.abs(this.value)" required>

                <label class="form-label">
                    Kesopanan Saat Mengemudi:
                </label>
                <input type="number" name="nilai_kesopanan_mengemudi" class="form-input" placeholder="Nilai 1-5"
                    step="1" min="1" oninput="this.value = this.value > 5 ? 5 : Math.abs(this.value)" required>

                <label class="form-label">
                    Kepatuhan Terhadap Lalu lintas:
                </label>
                <input type="number" name="nilai_kepatuhan_lalin" class="form-input" placeholder="Nilai 1-5" step="1"
                    min="1" oninput="this.value = this.value > 5 ? 5 : Math.abs(this.value)" required>

                <label class="form-label">
                    Sikap Terhadap Pengguna Jalan Lainya:
                </label>
                <input type="number" name="nilai_sikap" class="form-input" placeholder="Nilai 1-5" step="1" min="1"
                    oninput="this.value = this.value > 5 ? 5 : Math.abs(this.value)" required>

                <label class="form-label">
                    Pengetahuan Tentang Kendaraan:
                </label>
                <input type="number" name="nilai_pengetahuan_kendaraan" class="form-input" placeholder="Nilai 1-5"
                    step="1" min="1" oninput="this.value = this.value > 5 ? 5 : Math.abs(this.value)" required>

                <label class="form-label">
                    Keamanan Dalam Mengemudi:
                </label>
                <input type="number" name="nilai_keamanan" class="form-input" placeholder="Nilai 1-5" step="1" min="1"
                    oninput="this.value = this.value > 5 ? 5 : Math.abs(this.value)" required>

                <button class="btn-submit" type="submit" name="tambah_nilaipraktek">
                    Submit
                </button>
            </form>

            <button class="btn-close-popup" onclick="togglePopupTambahNilai()">
                Close
            </button>
        </div>
    </div>

    <div id="popupOverlayUpdateNilai" class="overlay-container">
        <div class="popup-box">
            <h2 style="color: green;">Update Nilai Praktek</h2>
            <br>
            <form action="pelatihan.php" method="post" class="form-container">
                <input type="hidden" name="id_nilai" value="">

                <label class="form-label">
                    Percaya Diri Saat Mengemudi:
                </label>
                <input type="number" name="nilai_percaya_diri" class="form-input" placeholder="Nilai 1-5" step="1"
                    min="1" oninput="this.value = this.value > 5 ? 5 : Math.abs(this.value)" required>

                <label class="form-label">
                    Kesopanan Saat Mengemudi:
                </label>
                <input type="number" name="nilai_kesopanan_mengemudi" class="form-input" placeholder="Nilai 1-5"
                    step="1" min="1" oninput="this.value = this.value > 5 ? 5 : Math.abs(this.value)" required>

                <label class="form-label">
                    Kepatuhan Terhadap Lalu lintas:
                </label>
                <input type="number" name="nilai_kepatuhan_lalin" class="form-input" placeholder="Nilai 1-5" step="1"
                    min="1" oninput="this.value = this.value > 5 ? 5 : Math.abs(this.value)" required>

                <label class="form-label">
                    Sikap Terhadap Pengguna Jalan Lainya:
                </label>
                <input type="number" name="nilai_sikap" class="form-input" placeholder="Nilai 1-5" step="1" min="1"
                    oninput="this.value = this.value > 5 ? 5 : Math.abs(this.value)" required>

                <label class="form-label">
                    Pengetahuan Tentang Kendaraan:
                </label>
                <input type="number" name="nilai_pengetahuan_kendaraan" class="form-input" placeholder="Nilai 1-5"
                    step="1" min="1" oninput="this.value = this.value > 5 ? 5 : Math.abs(this.value)" required>

                <label class="form-label">
                    Keamanan Dalam Mengemudi:
                </label>
                <input type="number" name="nilai_keamanan" class="form-input" placeholder="Nilai 1-5" step="1" min="1"
                    oninput="this.value = this.value > 5 ? 5 : Math.abs(this.value)" required>

                <button class="btn-submit" type="submit" name="tambah_nilaipraktek">
                    Submit
                </button>
            </form>

            <button class="btn-close-popup" onclick="togglePopupUpdateNilai()">
                Close
            </button>
        </div>
    </div>

    <div id="popupOverlaySelesai" class="overlay-container">
        <div class="popup-box">
            <h2 style="color: green;">Selesai Proses Pelatihan ?</h2>
            <form action="pelatihan.php" method="post" class="form-container" enctype="multipart/form-data">
                <input type="hidden" name="id_pelatihan" value="">
                <label class="form-label" for="file">
                    Bukti Struk Bensin:
                </label>
                <input class="form-input" type="file" id="file" name="file">
                <input class="form-input" type="text" id="nominal" name="nominal" required>
                <button class="btn-submit" type="submit" name="selesai">
                    Submit
                </button>
            </form>

            <button class="btn-close-popup" onclick="togglePopupSelesaiPelatihan()">
                Close
            </button>
        </div>
    </div>

    <div id="popupOverlayTambahBuku" class="overlay-container">
        <div class="popup-box">
            <h2 style="color: green;">Berikan Modul</h2>
            <br>
            <form action="pelatihan.php" method="post" class="form-container">
                <input type="hidden" name="id_pelatihan" value="">
                <label class="form-label">
                    Pilih Modul:
                </label>
                <select class="form-input" name="id_buku" id="id_buku" required>
                    <option value="" disabled selected>Pilih Modul</option>
                    <?php while ($modul = mysqli_fetch_array($result_modul)) { ?>
                        <option value="<?php echo $modul['id_buku']; ?>">
                            <?php echo $modul['nama_buku'] . ' - ' . $modul['file']; ?>
                        </option>
                    <?php } ?>
                </select>

                <button class="btn-submit" type="submit" name="tambah_buku">
                    Submit
                </button>
            </form>

            <button class="btn-close-popup" onclick="togglePopupTambahBuku()">
                Close
            </button>
        </div>
    </div>

    <div id="popupOverlayUpdateBuku" class="overlay-container">
        <div class="popup-box">
            <h2 style="color: green;">Berikan Modul</h2>
            <br>
            <form action="pelatihan.php" method="post" class="form-container">
                <input type="hidden" name="id_pelatihan" value="">
                <label class="form-label">
                    Pilih Modul:
                </label>
                <select class="form-input" name="id_buku" id="id_buku">
                    <option value="" disabled selected>Pilih Modul</option>
                    <?php foreach ($moduls as $modul) { ?>
                        <option value="<?php echo $modul['id_buku']; ?>">
                            <?php echo $modul['nama_buku'] . ' - ' . $modul['file']; ?>
                        </option>
                    <?php } ?>
                </select>

                <button class="btn-submit" type="submit" name="update_buku">
                    Submit
                </button>
            </form>

            <button class="btn-close-popup" onclick="togglePopupUpdateBuku()">
                Close
            </button>
        </div>
    </div>

    <div id="popupOverlayTambahAbsensi" class="overlay-container">
        <div class="popup-box">
            <h2 style="color: green;">Absensi Peserta</h2>
            <br>
            <input type="hidden" id="id_pelatihan" name="id_pelatihan" value="">
            <div id="jumlah_hadir" class="info-text"></div>
            <div id="jumlah_absen" class="info-text"></div>
            <div id="total_absensi" class="info-text"></div>

            <!-- =========== TABEL =========  -->
            <table id="example1" class="display">
                <thead>
                    <tr>
                        <th>ID Absensi</th>
                        <th>Status Kehadiran</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody id="table-content-absensi">
                    <!-- Konten tabel akan dimuat di sini dengan AJAX -->
                </tbody>
            </table>

            <button class="btn-close-popup" onclick="togglePopupTambahAbsensi()">
                Close
            </button>
        </div>
    </div>

    <div id="popupOverlayLihatSoal" class="overlay-container">
        <div class="popup-box">
            <h2 style="color: green;">Soal Pelatihan</h2>
            <br>
            <div id="soalContent"></div>
            <button class="btn-close-popup" onclick="togglePopupLihatSoal()">Close</button>
        </div>
    </div>


    <!-- =========== Scripts =========  -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="styles/app.js"></script>

    <script>
        function togglePopupTambahTeori(id_pelatihan) {
            const overlay = document.getElementById('popupOverlayTambahTeori');
            overlay.classList.toggle('show');

            if (id_pelatihan) {
                document.querySelector('#popupOverlayTambahTeori input[name="id_pelatihan"]').value = id_pelatihan;
            }
        }

        function togglePopupUpdateTeori(id_nilai, nilai_teori) {
            const overlay = document.getElementById('popupOverlayUpdateTeori');
            overlay.classList.toggle('show');

            if (id_nilai && nilai_teori) {
                document.querySelector('#popupOverlayUpdateTeori input[name="id_nilai"]').value = id_nilai;
                document.querySelector('#popupOverlayUpdateTeori input[name="nilai_teori"]').value = nilai_teori;
            }
        }

        function togglePopupTambahNilai(id_nilai) {
            const overlay = document.getElementById('popupOverlayTambahNilai');
            overlay.classList.toggle('show');

            if (id_nilai) {
                document.querySelector('#popupOverlayTambahNilai input[name="id_nilai"]').value = id_nilai;
            }
        }

        function togglePopupLihatSoal(id_pelatihan) {
            const overlay = document.getElementById('popupOverlayLihatSoal');
            overlay.classList.toggle('show');

            if (id_pelatihan) {
                fetchSoalJawaban(id_pelatihan);
            }
        }

        function fetchSoalJawaban(id_pelatihan) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'fetch_soal_jawaban.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    document.getElementById('soalContent').innerHTML = xhr.responseText;
                }
            };
            xhr.send('id_pelatihan=' + id_pelatihan);
        }


        function togglePopupTambahBuku(id_pelatihan) {
            const overlay = document.getElementById('popupOverlayTambahBuku');
            overlay.classList.toggle('show');

            if (id_pelatihan) {
                document.querySelector('#popupOverlayTambahBuku input[name="id_pelatihan"]').value = id_pelatihan;
            }
        }

        function togglePopupTambahAbsensi(id_pelatihan, total_absensi, jumlah_absen, jumlah_hadir) {
            const overlay = document.getElementById('popupOverlayTambahAbsensi');
            overlay.classList.toggle('show');

            if (id_pelatihan && total_absensi && jumlah_absen && jumlah_hadir) {
                document.querySelector('#popupOverlayTambahAbsensi input[name="id_pelatihan"]').value = id_pelatihan;
                document.querySelector('#popupOverlayTambahAbsensi #total_absensi').textContent = 'Total Absensi: ' + total_absensi;
                document.querySelector('#popupOverlayTambahAbsensi #jumlah_absen').textContent = 'Total Absen: ' + jumlah_absen;
                document.querySelector('#popupOverlayTambahAbsensi #jumlah_hadir').textContent = 'Total Kehadiran: ' + jumlah_hadir;
                // Memuat data absensi menggunakan AJAX
                loadAbsensiData(id_pelatihan);
            }
        }

        function loadAbsensiData(id_pelatihan) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `get_absensi_data.php?id_pelatihan=${id_pelatihan}`, true);
            xhr.onload = function () {
                if (this.status === 200) {
                    const response = JSON.parse(this.responseText);
                    let tableContent = '';
                    response.forEach(function (absensi) {
                        tableContent += `
                    <tr>
                        <td>${absensi.id_absensi}</td>
                        <td>${absensi.keterangan_absensi}</td>
                        <td>${absensi.waktu_absensi}</td>
                    </tr>
                `;
                    });
                    document.getElementById('table-content-absensi').innerHTML = tableContent;
                    $('#example1').DataTable(); // Inisialisasi ulang DataTable
                }
            };
            xhr.send();
        }

        function togglePopupUpdateBuku(id_pelatihan, id_buku) {
            const overlay = document.getElementById('popupOverlayUpdateBuku');
            overlay.classList.toggle('show');

            if (id_pelatihan, id_buku) {
                document.querySelector('#popupOverlayUpdateBuku input[name="id_pelatihan"]').value = id_pelatihan;
                document.querySelector('#popupOverlayUpdateBuku select[name="id_buku"]').value = id_buku;

                // Set nilai default pada select
                const selectModul = document.querySelector('#popupOverlayUpdateBuku select[name="id_buku"]');
                for (let option of selectModul.options) {
                    if (option.value == id_buku) {
                        option.selected = true;
                        break;
                    }
                }
            }
        }

        function togglePopupUpdateNilai(id_nilai, nilai_percaya_diri, nilai_kesopanan_mengemudi, nilai_kepatuhan_lalin,
            nilai_sikap, nilai_pengetahuan_kendaraan, nilai_keamanan) {
            const overlay = document.getElementById('popupOverlayUpdateNilai');
            overlay.classList.toggle('show');

            if (id_nilai && nilai_percaya_diri && nilai_kesopanan_mengemudi && nilai_kepatuhan_lalin && nilai_sikap &&
                nilai_pengetahuan_kendaraan && nilai_keamanan) {
                document.querySelector('#popupOverlayUpdateNilai input[name="id_nilai"]').value = id_nilai;
                document.querySelector('#popupOverlayUpdateNilai input[name="nilai_percaya_diri"]').value =
                    nilai_percaya_diri;
                document.querySelector('#popupOverlayUpdateNilai input[name="nilai_kesopanan_mengemudi"]').value =
                    nilai_kesopanan_mengemudi;
                document.querySelector('#popupOverlayUpdateNilai input[name="nilai_kepatuhan_lalin"]').value =
                    nilai_kepatuhan_lalin;
                document.querySelector('#popupOverlayUpdateNilai input[name="nilai_sikap"]').value = nilai_sikap;
                document.querySelector('#popupOverlayUpdateNilai input[name="nilai_pengetahuan_kendaraan"]').value =
                    nilai_pengetahuan_kendaraan;
                document.querySelector('#popupOverlayUpdateNilai input[name="nilai_keamanan"]').value = nilai_keamanan;
            }
        }

        function togglePopupSelesaiPelatihan(id_pelatihan) {
            const overlay = document.getElementById('popupOverlaySelesai');
            overlay.classList.toggle('show');

            if (id_pelatihan) {
                document.querySelector('#popupOverlaySelesai input[name="id_pelatihan"]').value = id_pelatihan;
            }
        }


        $(document).ready(function () {
            $('#example').DataTable();
        });
        $(document).ready(function () {
            $('#example1').DataTable();
        });
    </script>

    <!-- ====== ionicons ======= -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>