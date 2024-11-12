<?php
session_start();
include '../config/koneksi.php';

if (isset($_GET['id_pelatihan'])) {
    $id_pelatihan = $_GET['id_pelatihan'];

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
    LEFT JOIN tb_nilai ON tb_pelatihan.id_pelatihan = tb_nilai.id_pelatihan WHERE tb_pelatihan.id_pelatihan = $id_pelatihan";

    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $conn->close();
} else {
    echo "Parameter id_nilai tidak ditemukan";
}
  
$nilai_total = $row['nilai_percaya_diri'] + $row['nilai_kesopanan_mengemudi'] + $row['nilai_kepatuhan_lalin'] + $row['nilai_sikap'] + $row['nilai_pengetahuan_kendaraan'] + $row['nilai_keamanan'] + $row['nilai_teori'];
$nilai_akhir = $nilai_total/80*100;

$warna_teks = '';
$teks_status = '';

if ($nilai_akhir >= 60) {
    $warna_teks = 'text-success';
    $teks_status = 'LULUS';
} else {
    $warna_teks = 'text-danger';
    $teks_status = 'GAGAL';
}
?>
<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Laporan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
    .signature-left {
        float: left;
        width: 50%;
        text-align: center;
    }

    .signature-right {
        float: right;
        width: 50%;
        text-align: center;
    }

    .h6-text {
        display: inline-block;
        min-width: 150px;
    }
    </style>
</head>

<body>
    <div class="container-xxl">
        <center>
            <h2>PENILAIAN TES MENGEMUDI</h2>
            <h3>CV. RATU PADANG</h3>
        </center>
        <br>
        <h6 class="h6-text" style="float: right;">: <?php echo $row['no_sim'] === null ? '-' : $row['no_sim']; ?></h6>
        <h6 class="h6-text" style="float: right;">NO.E-SIM</h6>
        <h6 class="h6-text">Nama Peserta</h6>
        <h6 class="h6-text">: <?php echo $row['name_konsumen']; ?></h6>
        <br>
        <h6 class="h6-text" style="float: right;">: <?php echo $row['type'] === null ? '-' : $row['type']; ?></h6>
        <h6 class="h6-text" style="float: right;">Jenis SIM</h6>
        <h6 class="h6-text">Nama Instruktur</h6>
        <h6 class="h6-text">: <?php echo $row['name_instruktur']; ?></h6>
        <br>
        <h6 class="h6-text">Paket Pelatihan</h6>
        <h6 class="h6-text">: <?php echo $row['nama_jenis']; ?></h6>
        <br>
        <h6 class="h6-text">Mobil</h6>
        <h6 class="h6-text">: <?php echo $row['nama_mobil']; ?> (<?php echo $row['kategori']; ?>)</h6>
        <br><br>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="text-center" rowspan="2">No</th>
                        <th class="text-center" rowspan="2">ASPEK PENILAIAN</th>
                        <th class="text-center" colspan="2">NILAI</th>
                    </tr>
                    <tr>
                        <th class="text-center">Nilai Peserta</th>
                        <th class="text-center">Nilai Maksimal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">1</td>
                        <td>Percaya Diri Saat Mengemudi</td>
                        <td class="text-center"><?php echo $row['nilai_percaya_diri']; ?></td>
                        <td class="text-center">5</td>
                    </tr>
                    <tr>
                        <td class="text-center">2</td>
                        <td>Kesopanan Saat Mengemudi</td>
                        <td class="text-center"><?php echo $row['nilai_kesopanan_mengemudi']; ?></td>
                        <td class="text-center">5</td>
                    </tr>
                    <tr>
                        <td class="text-center">3</td>
                        <td>Kepatuhan Terhadap Lalu Lintas</td>
                        <td class="text-center"><?php echo $row['nilai_kepatuhan_lalin']; ?></td>
                        <td class="text-center">5</td>
                    </tr>
                    <tr>
                        <td class="text-center">4</td>
                        <td>Sikap Terhadap Pengguna Jalan Lainnya</td>
                        <td class="text-center"><?php echo $row['nilai_sikap']; ?></td>
                        <td class="text-center">5</td>
                    </tr>
                    <tr>
                        <td class="text-center">5</td>
                        <td>Pengetahuan Tentang Kendaraan</td>
                        <td class="text-center"><?php echo $row['nilai_pengetahuan_kendaraan']; ?></td>
                        <td class="text-center">5</td>
                    </tr>
                    <tr>
                        <td class="text-center">6</td>
                        <td>Keamanan Dalam Mengemudi</td>
                        <td class="text-center"><?php echo $row['nilai_keamanan']; ?></td>
                        <td class="text-center">5</td>
                    </tr>
                    <tr>
                        <td class="text-center">7</td>
                        <td>Nilai Teori</td>
                        <td class="text-center"><?php echo $row['nilai_teori']; ?></td>
                        <td class="text-center">50</td>
                    </tr>
                    <tr>
                        <th class="text-center" colspan="2">NILAI TOTAL</th>
                        <th class="text-center" colspan="2"><?php echo $nilai_total; ?></th>
                    </tr>
                    <tr>
                        <th class="text-center table-active" colspan="2">NILAI AKHIR</th>
                        <th class="text-center table-active <?php echo $warna_teks; ?>" colspan="2">
                            <?php echo $nilai_akhir; ?> (<?php echo $teks_status; ?>)</th>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="signature-left">
            <p><br>Penanggung Jawab, <br>Instruktur</p>
            <br><br>
            <p><u><?php echo $row['name_instruktur'];?></u></p>
        </div>
        <div class="signature-right">
            <p>
                Padang, <?php echo date('d-m-Y'); ?><br>
                Diketahui,<br>
                Pimpinan
            </p>
            <br><br>
            <p>__________________</p>
        </div>
    </div>
    <script>
    window.print();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
        integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"
        integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous">
    </script>
</body>

</html>