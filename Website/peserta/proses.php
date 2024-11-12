<?php

namespace Midtrans;

session_start();
include '../config/koneksi.php';

$id_peserta = $_SESSION['id_user'];
require_once dirname(__FILE__) . '/Midtrans.php';
// Set Your server key
// can find in Merchant Portal -> Settings -> Access keys
Config::$serverKey = 'SB-Mid-server-yZbU_u1NCKEyGDsZs_UVEmzn';

// non-relevant function only used for demo/example purpose
printExampleWarningMessage();

// Uncomment for production environment
// Config::$isProduction = true;
Config::$isProduction = false;
Config::$isSanitized = Config::$is3ds = true;
Config::$is3ds = true;

$order_id = $_GET['order_id'];

// Ambil detail transaksi
$query_transaksi = "SELECT * FROM tb_pelatihan WHERE id_pelatihan = ?";
$stmt_transaksi = $conn->prepare($query_transaksi);
$stmt_transaksi->bind_param("i", $order_id);
$stmt_transaksi->execute();
$result_transaksi = $stmt_transaksi->get_result()->fetch_assoc();
$id_jenis_pelatihan = $result_transaksi['id_jenis_pelatihan'];
$id_mobil = $result_transaksi['id_mobil'];
$tanggal_bo = $result_transaksi['tanggal_bo'];

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

// Required
$transaction_details = array(
    'order_id' => $order_id,
    'gross_amount' => $harga, // no decimal allowed for creditcard
);
// Optional
$item_details = array(
    array(
        'id' => $id_jenis_pelatihan,
        'price' => $harga,
        'quantity' => 1,
        'name' => $nama_jenis
    ),
);
// Optional
$customer_details = array(
    'first_name' => $name_konsumen,
    'last_name' => "",
    'email' => "tes@tes.com",
    'phone' => $nohp,
);
// Fill transaction details
$transaction = array(
    'transaction_details' => $transaction_details,
    'customer_details' => $customer_details,
    'item_details' => $item_details,
);

$snap_token = '';
try {
    $snap_token = Snap::getSnapToken($transaction);
} catch (\Exception $e) {
    echo $e->getMessage();
}

echo "snapToken = " . $snap_token;

function printExampleWarningMessage()
{
    if (strpos(Config::$serverKey, 'your ') != false) {
        echo "<code>";
        echo "<h4>Please set your server key from sandbox</h4>";
        echo "In file: " . __FILE__;
        echo "<br>";
        echo "<br>";
        echo htmlspecialchars('Config::$serverKey = \'<your server key>\';');
        die();
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment </title>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-beta/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto">
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="card-body">
                <p>Pendaftaran Berhasil, Silahkan selesaikan pembayaran anda !</p>
                <button id="pay-button" class="btn btn-outline-primary">Metode Pembayaran</button>
                <script src="https://app.sandbox.midtrans.com/snap/snap.js"
                    data-client-key="<?php echo Config::$clientKey; ?>"></script>
                <script type="text/javascript">
                    document.getElementById('pay-button').onclick = function () {
                        // SnapToken acquired from previous step
                        snap.pay('<?php echo $snap_token ?>');
                    };
                </script>

            </div>
        </div>
    </div>
</body>

</html>