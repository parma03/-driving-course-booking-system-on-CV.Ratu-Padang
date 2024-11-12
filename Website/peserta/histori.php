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

require('fpdf/fpdf.php');

function generate_certificate($name_peserta, $name_instruktur, $img_ttd, $date)
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

    // Set margins
    $pdf->SetTopMargin(20);
    $pdf->SetLeftMargin(20);
    $pdf->SetRightMargin(20);

    $pdf->AddPage();

    // Print the background image
    $pdf->Image("fpdf/bg.png", 20, 20, 780);

    // Print the certificate logo
    $pdf->Image("fpdf/logo.png", 140, 180, 240);

    // Print the title of the certificate
    $pdf->SetFont('times', 'B', 28);
    $pdf->Cell(720 + 10, 200, "SERTIFIKAT KURSUS MENGEMUDI", 0, 0, 'C');

    $pdf->SetFont('Arial', 'I', 34);
    $pdf->SetXY(370, 220);
    $pdf->Cell(350, 25, $name_peserta, "B", 0, 'C', 0);

    // Print the message with the date
    $pdf->SetFont('Arial', 'I', 14);
    $pdf->SetXY(370, 280);
    $message = "telah menyelesaikan kursus mengemudi yang diselenggarakan oleh CV.RATU
     
    Padang, $date";
    $pdf->MultiCell(350, 14, $message, 0, 'C', 0);

    // Print the signature image
    $pdf->Image("../instruktur/img/" . $img_ttd, 470, 350, 100); // Adjust the position and size as needed

    // Print the instructor's name
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetXY(370, 470);
    $signataire = "$name_instruktur";
    $pdf->Cell(350, 19, $signataire, "T", 0, 'C');

    // Output the PDF
    $pdf->Output('D', 'sertifikat_' . $name_peserta . '.pdf');
}
if (isset($_POST['btn_certifikat'])) {
    $name_peserta = $_POST['name_peserta'];
    $name_instruktur = $_POST['name_instruktur'];
    $img_ttd = $_POST['img_ttd'];
    $date = date('Y-m-d');

    // Generate sertifikat
    generate_certificate($name_peserta, $name_instruktur, $img_ttd, $date);
}

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
tb_instruktur.img_ttd,
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
            <h2>Histori Kursus Mengemudi</h2><br>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <td>Tipe Kursus</td>
                            <td>Status</td>
                            <td>Invoice</td>
                            <td>Sertifikat</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($data = mysqli_fetch_array($result)) { ?>
                            <tr>
                                <td>( <?php echo $data['nama_jenis']; ?> ) <?php echo $data['keterangan']; ?></td>
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
                                <td>
                                    <button id="printButton" class="btn btn-success">
                                        Cetak
                                    </button>
                                </td>
                                <td>
                                    <?php
                                    if ($data['status'] === 'Selesai') { ?>
                                        <form method="post" action="histori.php">
                                            <input type="hidden" name="name_peserta" value="<?php echo $nama; ?>">
                                            <input type="hidden" name="name_instruktur"
                                                value="<?php echo $data['name_instruktur']; ?>">
                                            <input type="hidden" name="img_ttd" value="<?php echo $data['img_ttd']; ?>">
                                            <button class="btn btn-primary" type="submit" name="btn_certifikat">Generate
                                                Sertifikat</button>
                                        </form>
                                    <?php } else { ?>
                                        <?php echo $data['status']; ?>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

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