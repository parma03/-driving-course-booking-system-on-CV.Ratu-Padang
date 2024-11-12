<?php

namespace Midtrans;

require_once dirname(__FILE__) . '/Midtrans.php';
Config::$isProduction = false;
Config::$serverKey = 'SB-Mid-server-yZbU_u1NCKEyGDsZs_UVEmzn';
$notif = new Notification();

$transaction = $notif->transaction_status;
$type = $notif->payment_type;
$order_id = $notif->order_id;
$fraud = $notif->fraud_status;

include '../config/koneksi.php'; // Pastikan koneksi database sebelum eksekusi query

switch ($transaction) {
    case 'capture':
        if ($type == 'credit_card') {
            $status = ($fraud == 'challenge') ? 'Challenge' : 'Success';
        }
        break;
    case 'settlement':
        $status = 'Dibayar';
        break;
    case 'pending':
        $status = 'Pending';
        break;
    case 'deny':
        $status = 'Deny';
        break;
    case 'expire':
        $status = 'Expired';
        break;
    case 'cancel':
        $status = 'Canceled';
        break;
    default:
        $status = 'Unknown';
        break;
}

mysqli_query($conn, "UPDATE tb_pelatihan SET status='$status' WHERE id_pelatihan='$order_id'");

// Log transaction status
file_put_contents('notification_status_log.txt', "Order ID: $order_id, Status: $status\n", FILE_APPEND);
?>