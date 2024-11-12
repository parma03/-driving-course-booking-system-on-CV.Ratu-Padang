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
    } elseif ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/index.php");
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

function isPasswordValid($password)
{
    return preg_match('/[A-Za-z]/', $password) && preg_match('/[0-9]/', $password) && preg_match('/[^A-Za-z0-9]/', $password);
}

$id_user = $_SESSION['id_user'];

$query = "SELECT * FROM tb_instruktur WHERE id_instruktur = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$nama = @$result['name_konsumen'];
$role = @$result['role'];
$username = @$result['username'];
$password = @$result['password'];
$nohp = @$result['nohp'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $id_user = $_POST['id_user'];
    $username = $_POST['username'];
    $nama = $_POST['nama'];
    $nohp = $_POST['phone'];
    $file = $_FILES['file']['name'];
    $file_temp = $_FILES['file']['tmp_name'];
    $file_path = "img/" . $file;

    $query_get_old_file = "SELECT img FROM tb_instruktur WHERE id_instruktur = ?";
    $stmt = $conn->prepare($query_get_old_file);
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $old_file = $row['img'];

    if (move_uploaded_file($file_temp, $file_path)) {
        if (!empty($old_file) && file_exists("img/" . $old_file)) {
            unlink("img/" . $old_file);
        }

        if (!empty($_POST['password'])) {
            $password = $_POST['password'];
            if (!isPasswordValid($password)) {
                setNotification("Password harus mengandung huruf, angka, dan simbol!", "danger");
                header("Location: settings.php");
                exit();
            }
            $hashed_password = md5($password);
            $query_user = "UPDATE tb_user SET username = ?, password = ? WHERE id_user = ?";
            $stmt = $conn->prepare($query_user);
            $stmt->bind_param("ssi", $username, $hashed_password, $id_user);
        } else {
            $query_user = "UPDATE tb_user SET username = ? WHERE id_user = ?";
            $stmt = $conn->prepare($query_user);
            $stmt->bind_param("si", $username, $id_user);
        }

        if ($stmt->execute()) {
            $query_update_konsumen = "UPDATE tb_manajer SET name_manajer = ?, nohp = ?, img = ? WHERE id_manajer = ?";
            $stmt = $conn->prepare($query_update_konsumen);
            $stmt->bind_param("sssi", $nama, $nohp, $file, $id_user);
            $stmt->execute();
            setNotification("Berhasil Mengedit Profile", "success");
            header("Location: settings.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        if (!empty($_POST['password'])) {
            $password = $_POST['password'];
            if (!isPasswordValid($password)) {
                setNotification("Password harus mengandung huruf, angka, dan simbol!", "danger");
                header("Location: settings.php");
                exit();
            }
            $hashed_password = md5($password);
            $query_user = "UPDATE tb_user SET username = ?, password = ? WHERE id_user = ?";
            $stmt = $conn->prepare($query_user);
            $stmt->bind_param("ssi", $username, $hashed_password, $id_user);
        } else {
            $query_user = "UPDATE tb_user SET username = ? WHERE id_user = ?";
            $stmt = $conn->prepare($query_user);
            $stmt->bind_param("si", $username, $id_user);
        }

        if ($stmt->execute()) {
            $query_update_konsumen = "UPDATE tb_manajer SET name_manajer = ?, nohp = ? WHERE id_manajer = ?";
            $stmt = $conn->prepare($query_update_konsumen);
            $stmt->bind_param("ssi", $nama, $nohp, $id_user);
            $stmt->execute();
            setNotification("Berhasil Mengedit Profile", "success");
            header("Location: settings.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    }

    $stmt->close();
    $conn->close();
}

// Query untuk mendapatkan data user
$id_user = $_SESSION['id_user'];
$query = "SELECT * FROM tb_user INNER JOIN tb_manajer ON tb_user.id_user = tb_manajer.id_manajer WHERE tb_user.id_user = ?";
$result = $conn->prepare($query);
$result->bind_param("i", $id_user);
$result->execute(); // Add this line to execute the query
$result = $result->get_result(); // Get the result set
$row = $result->fetch_assoc(); // Fetch the data
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
                    <?php if ($row['img'] == NULL) { ?>
                        <img src="../img/instruktur.png" alt="Foto Profil" width="40" height="40">
                    <?php } else { ?>
                        <img src="img/<?php echo $row['img'] ?>" alt="Foto Profil" width="40" height="40">
                    <?php } ?>
                    <span><?php echo $nama; ?></span>
                </div>
            </div>

            <!-- ======================= Cards ================== -->
            <div style="display: flex; justify-content: center; align-items: center;">
                <div class="card"
                    style="width: 300px; padding: 20px; border: 2px solid black; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                    <div class="cardHeader">
                        <center>
                            <?php if ($row['img'] == NULL) { ?>
                                <img src="../img/instruktur.png" alt="Foto Profil" width="60" height="60">
                            <?php } else { ?>
                                <img src="img/<?php echo $row['img'] ?>" alt="Foto Profil" width="60" height="60">
                            <?php } ?>
                        </center>

                        <form action="settings.php" method="post" class="form-container" enctype="multipart/form-data">
                            <input class="form-input" type="file" id="file" name="file">
                            <input type="hidden" name="id_user" value="<?php echo $row['id_user']; ?>">
                            <label class="form-label" for="nama">
                                Nama:
                            </label>
                            <input class="form-input" type="text" placeholder="Masukan Nama" id="nama" name="nama"
                                value="<?php echo $row['name_manajer']; ?>" required>

                            <label class="form-label" for="nohp">
                                NOHP:
                            </label>
                            <input class="form-input" type="tel" id="phone" name="phone" maxlength="15"
                                placeholder="628xxxxxx" value="<?php echo $row['nohp'] ?>" pattern="628[0-9]{9,12}"
                                oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                                required>

                            <label class="form-label" for="username">
                                Username:
                            </label>
                            <input class="form-input" type="text" placeholder="Masukan Username" id="username"
                                name="username" value="<?php echo $row['username']; ?>" required>

                            <label class="form-label" for="password">
                                Password:
                            </label>
                            <input class="form-input" type="password" placeholder="Masukan Password" id="password"
                                name="password">
                            <ion-icon name="eye-outline" id="togglePassword"
                                style="cursor: pointer; position: relative; left: 210px; bottom: 50px;"></ion-icon>

                            <button class="btn-submit" type="submit" name="submit">
                                Submit
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function (e) {
            const password = document.getElementById('password');
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
    </script>

    <!-- =========== Scripts =========  -->
    <script src="styles/app.js"></script>

    <!-- ====== ionicons ======= -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>

</html>