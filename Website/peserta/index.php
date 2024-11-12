<?php
session_start();
include '../config/koneksi.php';

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/index.php");
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
$id_peserta = $_SESSION['id_user'];

$query = "SELECT * FROM tb_konsumen WHERE id_konsumen = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$nama = @$result['name_konsumen'];
$role = @$result['role'];
$username = @$result['username'];
$password = @$result['password'];
$nohp = @$result['nohp'];
$img = @$result['img'];

// Ambil data dari tb_pelatihan dengan status 'Dibayar' atau 'Proses'
$query = "SELECT * FROM tb_pelatihan WHERE id_konsumen = ? AND status IN ('Dibayar', 'Proses', 'Selesai')";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_peserta);
$stmt->execute();
$result = $stmt->get_result();
$data_pelatihan = [];

while ($row = $result->fetch_assoc()) {
    $id_pelatihan = $row['id_pelatihan'];
    $id_jenis_pelatihan = $row['id_jenis_pelatihan'];
    $id_mobil = $row['id_mobil'];
    $id_jadwal = $row['id_jadwal'];
    $tanggal_bo = $row['tanggal_bo'];
    // Ambil detail paket dan harga
    $query_paket = "SELECT * FROM tb_jenis_pelatihan WHERE id_jenis_pelatihan = ?";
    $stmt_paket = $conn->prepare($query_paket);
    $stmt_paket->bind_param("i", $id_jenis_pelatihan);
    $stmt_paket->execute();
    $result_paket = $stmt_paket->get_result()->fetch_assoc();
    $harga = $result_paket['harga'];
    $nama_jenis = $result_paket['nama_jenis'];
    // Ambil detail customer
    $query_konsumen = "SELECT name_konsumen, nohp FROM tb_konsumen WHERE id_konsumen = ?";
    $stmt_konsumen = $conn->prepare($query_konsumen);
    $stmt_konsumen->bind_param("i", $id_peserta);
    $stmt_konsumen->execute();
    $result_konsumen = $stmt_konsumen->get_result()->fetch_assoc();
    $name_konsumen = $result_konsumen['name_konsumen'];
    $nohp = $result_konsumen['nohp'];
    // Simpan data dalam array
    $data_pelatihan[] = [
        'id_pelatihan' => $id_pelatihan,
        'nama_jenis' => $nama_jenis,
        'harga' => $harga,
        'tanggal_bo' => $tanggal_bo,
        'name_konsumen' => $name_konsumen,
        'nohp' => $nohp
    ];
}

// Encode data sebagai JSON untuk digunakan di JavaScript
$data_pelatihan_json = json_encode($data_pelatihan);

// Pengecekan apakah sudah ada pelatihan yang sedang berlangsung
$query_cek_pelatihan = "SELECT * FROM tb_pelatihan WHERE id_konsumen = ? AND status = 'Dibayar' OR status = 'Proses'";
$stmt_cek_pelatihan = $conn->prepare($query_cek_pelatihan);
$stmt_cek_pelatihan->bind_param("i", $id_peserta);
$stmt_cek_pelatihan->execute();
$result_cek_pelatihan = $stmt_cek_pelatihan->get_result();
$pelatihan_berlangsung = $result_cek_pelatihan->num_rows > 0;

$stmt_cek_pelatihan->close();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah'])) {
    $id_jenis_pelatihan = $_POST['id_jenis_pelatihan'];
    $id_mobil = $_POST['id_mobil'];
    $id_jadwal = $_POST['id_jadwal'];
    $tanggal_bo = date('Y-m-d');
    $status = "Proses Pembayaran";
    $zero = date('Y-m-d H:i:s'); // Using 24-hour format for consistency

    // Cek apakah mobil sudah digunakan pada jadwal yang sama
    $query_cek_mobil = "SELECT * FROM tb_pelatihan WHERE id_mobil = ? AND id_jadwal = ?";
    $stmt = $conn->prepare($query_cek_mobil);
    $stmt->bind_param("ii", $id_mobil, $id_jadwal);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Mobil sudah terpakai
        setNotification("Mobil sudah terpakai pada jadwal ini.", "error");
        header("Location: index.php"); // Atau ganti dengan lokasi yang sesuai
        exit();
    } else {
        // Lanjutkan dengan pendaftaran
        $query_pendaftaran = "INSERT INTO tb_pelatihan (id_konsumen, id_jadwal, id_jenis_pelatihan, id_mobil, tanggal_bo, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query_pendaftaran);

        // Ensure $id_peserta is set appropriately
        $id_peserta = $_SESSION['id_user'];

        $stmt->bind_param("iiiiss", $id_peserta, $id_jadwal, $id_jenis_pelatihan, $id_mobil, $tanggal_bo, $status);
        if ($stmt->execute()) {
            $order_id = $stmt->insert_id;
            setNotification("Berhasil Mendaftar Pelatihan", "success");
            header("Location: proses.php?order_id=$order_id");
        } else {
            setNotification("Gagal Mendaftar Pelatihan: " . $stmt->error, "error");
        }
        $stmt->close();
    }

    $conn->close();
}


$query_hari = "SELECT DISTINCT hari FROM tb_jadwal";
$result_hari = $conn->query($query_hari);

$query_jam = "SELECT DISTINCT jam_mulai, jam_selesai FROM tb_jadwal";
$result_jam = $conn->query($query_jam);

$query_jadwal = "
    SELECT j.hari, j.jam_mulai, j.jam_selesai, p.id_instruktur, i.tipe_instruktur
    FROM tb_jadwal j
    LEFT JOIN tb_pelatihan p ON j.id_jadwal = p.id_jadwal
    LEFT JOIN tb_instruktur i ON p.id_instruktur = i.id_instruktur
";
$result_jadwal = $conn->query($query_jadwal);

$jadwal = [];
while ($row = mysqli_fetch_assoc($result_jadwal)) {
    $jadwal[$row['hari']][$row['jam_mulai']][$row['jam_selesai']][] = $row;
}

$query_instruktur = "
    SELECT tipe_instruktur, COUNT(*) as total
    FROM tb_instruktur
    GROUP BY tipe_instruktur
";
$result_instruktur = $conn->query($query_instruktur);

$total_instruktur = [];
while ($row = mysqli_fetch_assoc($result_instruktur)) {
    $total_instruktur[$row['tipe_instruktur']] = $row['total'];
}

// Query untuk mendapatkan data paket
$query_paket = "SELECT * FROM tb_jenis_pelatihan";
$result_paket = $conn->query($query_paket);

// Query untuk mendapatkan data mobil
if (isset($_POST['id_jenis_pelatihan'])) {
    $selected_paket = $_POST['id_jenis_pelatihan'];
    $query_mobil = "SELECT * FROM tb_mobil WHERE tipe_mobil = (SELECT kategori FROM tb_jenis_pelatihan WHERE id_jenis_pelatihan = $selected_paket)";
    $result_mobil = $conn->query($query_mobil);
}


$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peserta Dashboard | CV. Ratu Mengemudi</title>
    <!-- ======= Styles ====== -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
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
        /* styles/app.css */

        /* Tambahkan gaya untuk membuat baris tabel responsif */
        .profile-header {
            display: flex;
            align-items: center;
        }

        .profile-header img {
            border-radius: 50%;
            margin-right: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            text-align: center;
        }

        pre {
            white-space: pre;
            /* CSS 2.0 */
            white-space: pre-wrap;
            /* CSS 2.1 */
            white-space: pre-line;
            /* CSS 3.0 */
            white-space: -pre-wrap;
            /* Opera 4-6 */
            white-space: -o-pre-wrap;
            /* Opera 7 */
            white-space: -moz-pre-wrap;
            /* Mozilla */
            white-space: -hp-pre-wrap;
            /* HP Printers */
            word-wrap: break-word;
            /* IE 5+ */
        }

        th {
            background-color: #f2f2f2;
            color: #333;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
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
                    <a href="histori.php">
                        <span class="icon">
                            <ion-icon name="time-outline"></ion-icon>
                        </span>
                        <span class="title">Histori Pelatihan</span>
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
                        <img src="../img/konsumen.png" alt="Foto Profil" width="40" height="40">
                    <?php } else { ?>
                        <img src="img/<?php echo $img ?>" alt="Foto Profil" width="40" height="40">
                    <?php } ?>
                    <span><?php echo $nama; ?></span>
                </div>
            </div>
            <!-- ================ Order Details List ================= -->
            <h2>Jadwal Kursus Mengemudi</h2>
            <p style="color: red;"><i>Jam Kerja : Senin - Jum'at 09:00 - 18:00</i></p>
            <div class="cardHeader" align="right">
                <?php if ($pelatihan_berlangsung) { ?>
                    <button class="btn" onclick="togglePopupSudahMendaftar()">Daftar</button>
                <?php } else { ?>
                    <button class="btn" onclick="togglePopupTambah()">Daftar</button>
                <?php } ?>
            </div><br>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Jam/<sub>Hari</sub></th>
                            <?php
                            mysqli_data_seek($result_hari, 0); // Reset pointer to start
                            while ($hari = mysqli_fetch_array($result_hari)) { ?>
                                <th scope="col">
                                    <pre><?php if ($hari['hari'] === "Jumat") {
                                        echo "Jum'at";
                                    } else {
                                        echo $hari['hari'];
                                    } ?></pre>
                                </th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($jam = mysqli_fetch_array($result_jam)) { ?>
                            <tr>
                                <th>
                                    <pre><?php echo $jam['jam_mulai']; ?> - <?php echo $jam['jam_selesai']; ?></pre>
                                </th>
                                <?php
                                mysqli_data_seek($result_hari, 0); // Reset pointer to start
                                while ($hari = mysqli_fetch_array($result_hari)) {
                                    $jadwal_list = $jadwal[$hari['hari']][$jam['jam_mulai']][$jam['jam_selesai']] ?? [];
                                    $has_instruktur = false;
                                    $matic_count = 0;
                                    $manual_count = 0;

                                    foreach ($jadwal_list as $j) {
                                        if (!is_null($j['id_instruktur'])) {
                                            $has_instruktur = true;
                                            if ($j['tipe_instruktur'] === 'mobil matic') {
                                                $matic_count++;
                                            } elseif ($j['tipe_instruktur'] === 'mobil manual') {
                                                $manual_count++;
                                            }
                                        }
                                    }

                                    if (!$has_instruktur) {
                                        echo "<th><pre>Tidak ada Jadwal</pre></th>";
                                    } else {
                                        echo "<th><pre>Terdapat Jadwal (Instruktur Matic: $matic_count/{$total_instruktur['mobil matic']} | Instruktur Manual: $manual_count/{$total_instruktur['mobil manual']})</pre></th>";
                                    }
                                }
                                ?>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="popupOverlaySudahMendaftar" class="overlay-container">
        <div class="popup-box">
            <h2 style="color: green;">Form Pendaftaran Kursus</h2>
            <br>
            <p style="color: red;">Anda sudah Terdaftar</p>
            <button id="printButton" class="btn btn-success">
                Invoice
            </button>
            <button class="btn-close-popup" onclick="togglePopupSudahMendaftar()">
                Close
            </button>
        </div>
    </div>

    <div id="popupOverlayTambah" class="overlay-container">
        <div class="popup-box">
            <h2 style="color: green;">Form Pendaftaran Kursus</h2>
            <br>
            <form action="index.php" method="post" class="form-container">
                <label class="form-label" for="id_jenis_pelatihan">
                    Paket Kursus:
                </label>
                <select class="form-input" name="id_jenis_pelatihan" id="id_jenis_pelatihan">
                    <option value="" disabled selected>Pilih Paket Kursus</option>
                    <?php while ($paket = mysqli_fetch_array($result_paket)) { ?>
                        <option value="<?php echo $paket['id_jenis_pelatihan']; ?>"
                            data-kategori="<?php echo $paket['kategori']; ?>">
                            <?php echo $paket['nama_jenis'] . ' ( ' . $paket['kategori'] . ' )'; ?>
                        </option>
                    <?php } ?>
                </select>

                <div class="form-group">
                    <label for="hari">Pilih Hari</label>
                    <select id="hari" name="hari" class="form-input" required>
                        <option value="">Pilih Hari</option>
                        <?php
                        mysqli_data_seek($result_hari, 0);
                        while ($hari = mysqli_fetch_array($result_hari)) { ?>
                            <option value="<?php echo $hari['hari']; ?>"><?php echo $hari['hari']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <select id="jam" name="jam" class="form-input" required>
                        <option value="">Pilih Jam</option>
                    </select>
                </div>

                <label class="form-label" for="id_mobil">Pilih Mobil:</label>
                <select class="form-input" name="id_mobil" id="id_mobil">
                    <option value="" disabled selected>Pilih Mobil</option>
                </select>

                <input type="hidden" id="id_jadwal" name="id_jadwal">

                <button type="submit" name="tambah" class="btn-submit">Daftar</button>
            </form>

            <button class="btn-close-popup" onclick="togglePopupTambah()">
                Close
            </button>
        </div>
    </div>

    <script>
        // Script Ajax untuk memuat data mobil berdasarkan kategori paket kursus yang dipilih
        document.getElementById('id_jenis_pelatihan').addEventListener('change', function () {
            var id_jenis_pelatihan = this.value;

            // Mengirim permintaan Ajax
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'get_mobil.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        var data = JSON.parse(xhr.responseText);
                        var id_mobilSelect = document.getElementById('id_mobil');

                        // Menghapus opsi sebelumnya
                        id_mobilSelect.innerHTML = '<option value="" disabled selected>Pilih Mobil</option>';

                        // Menambahkan opsi mobil berdasarkan data yang diterima
                        data.forEach(function (mobil) {
                            var option = document.createElement('option');
                            option.value = mobil.id_mobil;
                            option.textContent = mobil.nama_mobil;
                            id_mobilSelect.appendChild(option);
                        });
                    } else {
                        console.error('Terjadi kesalahan saat memuat data mobil: ' + xhr.status);
                    }
                }
            };

            // Mengirim data form
            var formData = 'id_jenis_pelatihan=' + encodeURIComponent(id_jenis_pelatihan);
            xhr.send(formData);
        });

        function togglePopupTambah() {
            document.getElementById('popupOverlayTambah').classList.toggle('show');
        }

        function togglePopupSudahMendaftar() {
            document.getElementById('popupOverlaySudahMendaftar').classList.toggle('show');
        }

        document.getElementById("hari").addEventListener("change", function () {
            var hari = this.value;
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "get_jam.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if (xhr.status === 200) {
                    document.getElementById("jam").innerHTML = xhr.responseText;
                }
            };
            xhr.send("hari=" + hari);
        });

        document.getElementById("jam").addEventListener("change", function () {
            var jam = this.value;
            var hari = document.getElementById("hari").value;
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "get_jadwal.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if (xhr.status === 200) {
                    document.getElementById("id_jadwal").value = xhr.responseText;
                }
            };
            xhr.send("hari=" + hari + "&jam=" + jam);
        });

    </script>

    <script>
        document.getElementById("printButton").addEventListener("click", function () {
            // Ambil data dari PHP
            const data_pelatihan = <?php echo $data_pelatihan_json; ?>;

            // Fungsi untuk memformat angka menjadi Rupiah
            function formatRupiah(angka) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR'
                }).format(angka);
            }

            // Inisialisasi jsPDF
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Set margin awal
            let y = 20;

            // Tambahkan logo di sebelah kiri
            const logo = new Image();
            logo.src = '../img/logo.png'; // Ganti dengan path logo Anda
            logo.onload = () => {
                doc.addImage(logo, 'PNG', 20, y, 50, 50);

                // Tambahkan id order di sebelah kanan
                doc.setFontSize(12);
                doc.text(`ID Registrasi: #${data_pelatihan[0].id_pelatihan}`, 150, y + 20);
                doc.line(150, y + 22, 200, y + 22); // Garis bawah
                doc.text(`Tanggal Booking: ${data_pelatihan[0].tanggal_bo}`, 150, y + 30);

                // Tambahkan informasi pembooking di bawah logo
                doc.text(`Nama: ${data_pelatihan[0].name_konsumen}`, 20, y + 60);
                doc.text(`No. HP: ${data_pelatihan[0].nohp}`, 20, y + 70);

                // Tambahkan informasi kontak di bawah informasi pembooking
                doc.text(`CV. Ratu`, 20, y + 90);
                doc.text(`Contact Info`, 20, y + 100);
                doc.text(`Address: Jl. Perintis Kemerdekaan, Sawahan, Kec. Padang Tim., Kota Padang`, 20, y + 110);
                doc.text(`Email: admincvratu@gmail.com`, 20, y + 120);
                doc.text(`Phone: 0813-1446-0365`, 20, y + 130);

                // Tambahkan tabel informasi pembayaran
                y = 160;
                doc.text('Jenis Pelatihan', 20, y);
                doc.text('Harga', 150, y);
                doc.line(20, y + 2, 200, y + 2); // Garis bawah tabel

                let total = 0;
                data_pelatihan.forEach((pelatihan, index) => {
                    y += 10;
                    doc.text(`${pelatihan.nama_jenis}`, 20, y);
                    doc.text(formatRupiah(pelatihan.harga), 150, y);
                    total += parseFloat(pelatihan.harga);
                });

                // Tambahkan total di sebelah kanan
                y += 10;
                doc.line(20, y, 200, y); // Garis atas total
                y += 10;
                doc.text('TOTAL:', 20, y);
                doc.text(formatRupiah(total), 150, y);

                // Tambahkan pesan terima kasih
                y += 20;
                doc.text('Terima kasih', 150, y);

                // Simpan atau tampilkan PDF
                doc.save("invoice.pdf");
            };
        });
    </script>

    <!-- =========== Scripts =========  -->
    <script src="styles/app.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>

    <!-- ====== ionicons ======= -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>