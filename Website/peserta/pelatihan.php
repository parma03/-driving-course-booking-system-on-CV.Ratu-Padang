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

$query = "SELECT * FROM tb_pelatihan WHERE status = 'Dibayar' OR status = 'Proses' AND id_konsumen = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$id_absensi = @$result['id_absensi'];
$id_pelatihan = @$result['id_pelatihan'];
$id_buku = @$result['id_buku'];
$status = @$result['status'];
$id_instruktur = @$result['id_instruktur'];
$id_mobil = @$result['id_mobil'];

// Ambil detail absensi
$query_absensi = "SELECT *, SUM(CASE WHEN keterangan_absensi = 'Hadir' THEN 1 ELSE 0 END) AS jumlah_hadir,
    SUM(CASE WHEN keterangan_absensi = 'Absen' THEN 1 ELSE 0 END) AS jumlah_absen FROM tb_absensi WHERE id_pelatihan = ?";
$stmt_absensi = $conn->prepare($query_absensi);
$stmt_absensi->bind_param("i", $id_pelatihan);
$stmt_absensi->execute();
$result_absensi = $stmt_absensi->get_result()->fetch_assoc();
$hadir = @$result_absensi['jumlah_hadir'];
$absen = @$result_absensi['jumlah_absen'];

// Ambil detail buku
$query_buku = "SELECT * FROM tb_buku WHERE id_buku = ?";
$stmt_buku = $conn->prepare($query_buku);
$stmt_buku->bind_param("i", $id_buku);
$stmt_buku->execute();
$result_buku = $stmt_buku->get_result()->fetch_assoc();
$file = @$result_buku['file'];

// Ambil detail Instruktur
$query_instruktur = "SELECT * FROM tb_instruktur WHERE id_instruktur = ?";
$stmt_instruktur = $conn->prepare($query_instruktur);
$stmt_instruktur->bind_param("i", $id_instruktur);
$stmt_instruktur->execute();
$result_instruktur = $stmt_instruktur->get_result()->fetch_assoc();
$name_instruktur = @$result_instruktur['name_instruktur'];

// Ambil detail Mobil
$query_mobil = "SELECT * FROM tb_mobil WHERE id_mobil = ?";
$stmt_mobil = $conn->prepare($query_mobil);
$stmt_mobil->bind_param("i", $id_mobil);
$stmt_mobil->execute();
$result_mobil = $stmt_mobil->get_result()->fetch_assoc();
$tipe_mobil = @$result_mobil['tipe_mobil'];

// Ambil detail jawaban
$query_jawaban = "SELECT * FROM tb_jawaban WHERE id_pelatihan = ?";
$stmt_jawaban = $conn->prepare($query_jawaban);
$stmt_jawaban->bind_param("i", $id_pelatihan);
$stmt_jawaban->execute();
$result_jawaban = $stmt_jawaban->get_result()->fetch_assoc();
$id_jawaban = @$result_jawaban['id_jawaban'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah'])) {
    $id_pelatihan = $_POST['id_pelatihan'];

    // Prepare statement
    $query_soal = "INSERT INTO tb_jawaban (id_pelatihan, id_soal, jawaban) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query_soal);

    // Loop through all answers and execute the statement for each
    foreach ($_POST['id_soal'] as $index => $id_soal) {
        $jawaban = $_POST['jawaban'][$index];
        $stmt->bind_param("iis", $id_pelatihan, $id_soal, $jawaban);

        if (!$stmt->execute()) {
            echo "Error: " . $stmt->error;
        }
    }

    setNotification("Berhasil Tambah Jawaban Teori", "success");
    header("Location: pelatihan.php");
    exit();

    $stmt->close();
    $conn->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['absen'])) {
    $id_pelatihan = $_POST['id_pelatihan'];
    date_default_timezone_set('Asia/Jakarta');
    $dateNow = date("Y-m-d h:i:sa");
    $ket = "Hadir";

    // Prepare statement
    $query_absensi = "INSERT INTO tb_absensi (id_pelatihan, keterangan_absensi, waktu_absensi) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query_absensi);
    $stmt->bind_param("iss", $id_pelatihan, $ket, $dateNow);

    if ($stmt->execute()) {
        setNotification("Berhasil Menambah Data Absensi", "success");
        header("Location: pelatihan.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

require('fpdf/fpdf.php');

function generate_certificate($name_peserta, $name_instruktur, $date)
{
    class PDF extends FPDF
    {

        function Footer()
        {

            $this->SetY(-27);
            $this->SetFont('Arial', 'I', 8);

            $this->Cell(0, 10, 'This certificate has been ©  © produced by thetutor', 0, 0, 'R');
        }
    }


    $pdf = new FPDF('L', 'pt', 'A4');

    //Loading data 
    $pdf->SetTopMargin(20);
    $pdf->SetLeftMargin(20);
    $pdf->SetRightMargin(20);

    $pdf->AddPage();
    //  Print the edge of
    $pdf->Image("fpdf/bg.png", 20, 20, 780);
    // Print the certificate logo  
    $pdf->Image("fpdf/logo.png", 140, 180, 240);
    // Print the title of the certificate  
    $pdf->SetFont('times', 'B', 28);
    $pdf->Cell(720 + 10, 200, "SERTIFIKAT KURSUS MENGEMUDI", 0, 0, 'C');

    $pdf->SetFont('Arial', 'I', 34);
    $pdf->SetXY(370, 220);

    $pdf->Cell(350, 25, $name_peserta, "B", 0, 'C', 0);


    $pdf->SetFont('Arial', 'I', 14);
    $pdf->SetXY(370, 280);
    $message = "telah menyelesaikan kursus mengemudi yang diselenggarakan oleh CV.RATU
     
    Padang, $date";
    $pdf->MultiCell(350, 14, $message, 0, 'C', 0);


    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetXY(370, 470);
    $signataire = "$name_instruktur";
    $pdf->Cell(350, 19, $signataire, "T", 0, 'C');

    $pdf->Output('D', 'sertifikat_' . $name_peserta . '.pdf');
}
if (isset($_POST['btn_certifikat'])) {
    $name_peserta = $_POST['name_peserta'];
    $name_instruktur = $_POST['name_instruktur'];
    $date = date('Y-m-d');

    // Generate sertifikat
    generate_certificate($name_peserta, $name_instruktur, $date);
}

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

            <!-- ======================= Cards ================== -->
            <div style="display: flex;justify-content: center;align-items: center;">
                <div class="card" style="width: 300px; padding: 20px;">
                    <div class="cardHeader">
                        <?php
                        if ($id_pelatihan === NULL) {
                            echo "BELUM MENDAFTAR KURSUS";
                        } else { ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center">Nama Instruktur</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th class="text-center">
                                            <?php if ($name_instruktur === NULL) { ?>
                                                <p>Belum Mendapat Instruktur. </p>
                                            <?php } else { ?>
                                                <p> <?php echo $name_instruktur; ?> </p>
                                            <?php } ?>
                                        </th>
                                    </tr>
                                </tbody>
                            </table>


                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col" colspan="2" class="text-center">Absensi Peserta</th>
                                    </tr>
                                    <tr>
                                        <th colspan="2" class="text-center">
                                            <?php if (($hadir + $absen) < 14): ?>
                                                <form method="post" action="pelatihan.php">
                                                    <input type="hidden" name="id_pelatihan"
                                                        value="<?php echo $id_pelatihan; ?>">
                                                    <button class="btn btn-primary" type="submit" name="absen">Ambil
                                                        Absen</button>
                                                </form>
                                            <?php else: ?>
                                                <p class="text-danger">Anda sudah mencapai batas maksimum absensi.</p>
                                            <?php endif; ?>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="text-center">Hadir</th>
                                        <th class="text-center">Absen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th class="text-center"><?php echo $hadir; ?></th>
                                        <th class="text-center"><?php echo $absen; ?></th>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center">Buku Panduan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th class="text-center">
                                            <?php if ($file === NULL) {
                                                echo 'Buku Panduan Belum Di Berikan';
                                            } else { ?>
                                                <a class="btn btn-primary" download="<?php echo $file; ?>"
                                                    href="../instruktur/buku/<?php echo $file; ?>">
                                                    <ion-icon name="download-outline"></ion-icon> Panduan
                                                </a>
                                                <?php
                                            }
                                            ?>
                                        </th>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center">Soal Teori</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th class="text-center">
                                            <?php
                                            if ($id_jawaban === NULL) { ?>
                                                <button class="btn btn-primary" onclick="togglePopupTambahJawaban()">
                                                    Lihat</button>
                                                <?php
                                            } else {
                                                echo "Jawaban terkirim";
                                            } ?>
                                        </th>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <?php
                                        if ($status === 'Selesai') { ?>
                                            <th class="text-center">
                                                <?php echo $status; ?>
                                                <form method="post" action="pelatihan.php">
                                                    <input type="hidden" name="name_peserta" value="<?php echo $nama; ?>">
                                                    <input type="hidden" name="name_instruktur"
                                                        value="<?php echo $name_instruktur; ?>">
                                                    <button class="btn btn-primary" type="submit" name="btn_certifikat">Generate
                                                        Sertifikat</button>
                                                </form>
                                            </th>
                                        <?php } else { ?>
                                            <th class="text-center">
                                                <?php echo $status; ?>
                                            </th>
                                        <?php } ?>
                                    </tr>
                                </tbody>
                            </table>
                            <?php
                            if ($status === 'Selesai') { ?>
                                <center>
                                    <a class="btn btn-primary" href="hasil.php?id_pelatihan=<?php echo $id_pelatihan; ?>">Lihat
                                        Nilai</a>
                                </center>
                            <?php } else {
                                echo '';
                            } ?>
                            <?php
                        }
                        ?>


                    </div>
                </div>
            </div>
        </div>

        <div id="popupOverlayTambahJawaban" class="overlay-container">
            <div class="popup-box">
                <h2 style="color: green;">Soal Teori</h2>
                <br>
                <form action="pelatihan.php" method="post" class="form-container" enctype="multipart/form-data">
                    <input class="form-input" type="hidden" id="text" name="id_pelatihan"
                        value="<?php echo $id_pelatihan; ?>" required>
                    <?php
                    $noo = 1;
                    $query_soal = "SELECT * FROM tb_soal WHERE tipe_soal = '$tipe_mobil'";
                    $result_soal = $conn->query($query_soal);
                    while ($soal = mysqli_fetch_array($result_soal)) { ?>
                        <p><?php echo $noo++, ". ", $soal['nama_soal']; ?></p>
                        <input class="form-input" type="hidden" name="id_soal[]" value="<?php echo $soal['id_soal']; ?>"
                            required>
                        <input class="form-input" type="text" name="jawaban[]" required>
                    <?php } ?>
                    <button class="btn-submit" type="submit" name="tambah">Submit</button>
                </form>
                <button class="btn-close-popup" onclick="togglePopupTambahJawaban()">Close</button>
            </div>
        </div>

        <script>
            function togglePopupTambahJawaban() {
                const overlay = document.getElementById('popupOverlayTambahJawaban');
                overlay.classList.toggle('show');
            }
        </script>

        <!-- =========== Scripts =========  -->
        <script src="styles/app.js"></script>

        <!-- ====== ionicons ======= -->
        <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
        <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>