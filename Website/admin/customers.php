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

function isPasswordValid($password)
{
    return preg_match('/[A-Za-z]/', $password) && preg_match('/[0-9]/', $password) && preg_match('/[^A-Za-z0-9]/', $password);
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
    $username = $_POST['username'];
    $password = $_POST['password'];
    $name = $_POST['name'];
    $nohp = $_POST['phone'];
    $role = $_POST['role'];
    $tipe_instruktur = $_POST['tipe_instruktur'];

    if (!isPasswordValid($password)) {
        setNotification("Password harus mengandung huruf, angka, dan simbol!", "danger");
        header("Location: customers.php");
        exit();
    }

    $hashed_password = md5($password);

    $query_user = "INSERT INTO tb_user (username, password, role) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query_user);
    $stmt->bind_param("sss", $username, $hashed_password, $role);

    if ($stmt->execute()) {
        $last_id = $stmt->insert_id;

        if ($role == 'peserta') {
            $query_konsumen = "INSERT INTO tb_konsumen (id_konsumen, name_konsumen, nohp) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query_konsumen);
            $stmt->bind_param("iss", $last_id, $name, $nohp);
        } else if ($role == 'instruktur') {
            $query_instruktur = "INSERT INTO tb_instruktur (id_instruktur, name_instruktur, nohp, tipe_instruktur) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query_instruktur);
            $stmt->bind_param("isss", $last_id, $name, $nohp, $tipe_instruktur);
        } else if ($role == 'manajer') {
            $query_manajer = "INSERT INTO tb_manajer (id_manajer, name_manajer, nohp) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query_manajer);
            $stmt->bind_param("iss", $last_id, $name, $nohp);
        }


        if ($stmt->execute()) {
            setNotification("Berhasil Menambah Data User", "success");
            header("Location: customers.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $id_user = $_POST['id_user'];

    // Hapus data dari tabel tb_user
    $query_delete_user = "DELETE FROM tb_user WHERE id_user = ?";
    $stmt = $conn->prepare($query_delete_user);
    $stmt->bind_param("i", $id_user);

    if ($stmt->execute()) {
        $query_delete_konsumen = "DELETE FROM tb_konsumen WHERE id_konsumen = ?";
        $stmt = $conn->prepare($query_delete_konsumen);
        $stmt->bind_param("i", $id_user);
        $stmt->execute();

        $query_delete_instruktur = "DELETE FROM tb_instruktur WHERE id_instruktur = ?";
        $stmt = $conn->prepare($query_delete_instruktur);
        $stmt->bind_param("i", $id_user);
        $stmt->execute();

        setNotification("Berhasil Hapus Data User", "success");
        header("Location: customers.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id_user = $_POST['id_user'];
    $username = $_POST['username'];
    $name = $_POST['name'];
    $nohp = $_POST['phone'];
    $role = $_POST['role'];
    $tipe_instruktur = $_POST['tipe_instruktur'];

    if (!empty($_POST['password'])) {
        $password = $_POST['password'];
        if (!isPasswordValid($password)) {
            setNotification("Password harus mengandung huruf, angka, dan simbol!", "danger");
            header("Location: customers.php");
            exit();
        }
        $hashed_password = md5($password);
        $query_user = "UPDATE tb_user SET username = ?, password = ?, role = ? WHERE id_user = ?";
        $stmt = $conn->prepare($query_user);
        $stmt->bind_param("sssi", $username, $hashed_password, $role, $id_user);
    } else {
        $query_user = "UPDATE tb_user SET username = ?, role = ? WHERE id_user = ?";
        $stmt = $conn->prepare($query_user);
        $stmt->bind_param("ssi", $username, $role, $id_user);
    }

    if ($stmt->execute()) {
        if ($role == 'peserta') {
            $query_update_konsumen = "UPDATE tb_konsumen SET name_konsumen = ?, nohp = ? WHERE id_konsumen = ?";
            $stmt = $conn->prepare($query_update_konsumen);
            $stmt->bind_param("ssi", $name, $nohp, $id_user);
        } else if ($role == 'instruktur') {
            $query_update_instruktur = "UPDATE tb_instruktur SET name_instruktur = ?, nohp = ?, tipe_instruktur = ? WHERE id_instruktur = ?";
            $stmt = $conn->prepare($query_update_instruktur);
            $stmt->bind_param("sssi", $name, $nohp, $tipe_instruktur, $id_user);
        }

        if ($stmt->execute()) {
            setNotification("Berhasil Mengupdate Data User", "success");
            header("Location: customers.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

// Query untuk mendapatkan jumlah konsumen
$query = "SELECT COUNT(*) AS jumlah_konsumen FROM tb_konsumen";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$jumlah_konsumen = $row['jumlah_konsumen'];

// Query untuk mendapatkan jumlah instruktur
$query = "SELECT COUNT(*) AS jumlah_instruktur FROM tb_instruktur";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$jumlah_instruktur = $row['jumlah_instruktur'];

$query = "SELECT tb_user.id_user, tb_user.username, tb_user.password, tb_user.role, tb_instruktur.tipe_instruktur, COALESCE(tb_konsumen.name_konsumen, tb_instruktur.name_instruktur) AS name, COALESCE(tb_konsumen.nohp, tb_instruktur.nohp) AS nohp FROM tb_user LEFT JOIN tb_konsumen ON tb_user.id_user = tb_konsumen.id_konsumen LEFT JOIN tb_instruktur ON tb_user.id_user = tb_instruktur.id_instruktur WHERE tb_user.role = 'peserta' OR tb_user.role = 'instruktur'";
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

            <!-- ======================= Cards ================== -->
            <div class="cardBox">
                <div class="card">
                    <div>
                        <div class="numbers"><?php echo $jumlah_konsumen; ?></div>
                        <div class="cardName">Users</div>
                    </div>

                    <div class="iconBx">
                        <ion-icon name="people-outline"></ion-icon>
                    </div>
                </div>

                <div class="card">
                    <div>
                        <div class="numbers"><?php echo $jumlah_instruktur; ?></div>
                        <div class="cardName">Instruktur</div>
                    </div>

                    <div class="iconBx">
                        <ion-icon name="accessibility-outline"></ion-icon>
                    </div>
                </div>
            </div>

            <!-- ================ Order Details List ================= -->
            <div class="details">
                <div class="recentOrders">
                    <div class="cardHeader">
                        <h2>Data Users</h2>
                        <a class="btn" onclick="togglePopupTambah()">Tambah</a>
                    </div><br>

                    <table id="example">
                        <thead>
                            <tr>
                                <td>Nama</td>
                                <td>NOHP</td>
                                <td>Username</td>
                                <td>Role</td>
                                <td>Action</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($data = mysqli_fetch_array($result)) { ?>
                                <tr>
                                    <td><?php echo $data['name']; ?></td>
                                    <td><?php echo $data['nohp']; ?></td>
                                    <td><?php echo $data['username']; ?></td>
                                    <td>
                                        <?php
                                        if ($data['role'] === 'peserta') {
                                            echo $data['role'];
                                        } else {
                                            echo $data['role']; ?> - <?php echo $data['tipe_instruktur'];
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <button class="btn-open-update"
                                            onclick="togglePopupUpdate('<?php echo $data['id_user']; ?>', '<?php echo $data['name']; ?>', '<?php echo $data['nohp']; ?>', '<?php echo $data['username']; ?>', '<?php echo $data['role']; ?>')">UPDATE</button>
                                        <button class="btn-open-delete"
                                            onclick="togglePopupDelete('<?php echo $data['id_user']; ?>', '<?php echo $data['name']; ?>')">
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
            <h2 style="color: green;">Form Tambah Users</h2>
            <br>
            <form action="customers.php" method="post" class="form-container">
                <label class="form-label" for="name">
                    Nama:
                </label>
                <input class="form-input" type="text" placeholder="Masukan Nama" id="name" name="name" required>

                <label class="form-label" for="nohp">
                    NOHP:
                </label>
                <input class="form-input" type="tel" id="phone" name="phone" maxlength="15" placeholder="628xxxxxx"
                    pattern="628[0-9]{9,12}"
                    oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" required>

                <label class="form-label" for="username">
                    Username:
                </label>
                <input class="form-input" type="text" placeholder="Masukan Username" id="username" name="username"
                    required>

                <label class="form-label" for="role">
                    Role:
                </label>
                <select class="form-input" name="role" id="role" onchange="checkRole(this.value)">
                    <option>Pilih Role</option>
                    <option value="peserta">Peserta</option>
                    <option value="instruktur">Instruktur</option>
                    <option value="manajer">Manajer</option>
                </select>

                <div id="tipeInstrukturDiv" style="display: none;">
                    <label class="form-label" for="tipe_instruktur">
                        Tipe Instruktur:
                    </label>
                    <select class="form-input" name="tipe_instruktur" id="tipe_instruktur">
                        <option value="mobil matic">Mobil Matic</option>
                        <option value="mobil manual">Mobil Manual</option>
                    </select>
                </div>

                <label class="form-label" for="password">
                    Password:
                </label>
                <input class="form-input" type="password" placeholder="Masukan Password" id="password" name="password"
                    required>
                <ion-icon name="eye-outline" id="togglePassword"
                    style="cursor: pointer; position: relative; left: 250px; bottom: 50px;"></ion-icon>

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
            <h2 style="color: green;">Form Update Users</h2>
            <br>
            <form action="customers.php" method="post" class="form-container">
                <input type="hidden" name="id_user" value="">
                <label class="form-label" for="name">
                    Nama:
                </label>
                <input class="form-input" type="text" placeholder="Masukan Nama" id="name" name="name" required>

                <label class="form-label" for="nohp">
                    NOHP:
                </label>
                <input class="form-input" type="tel" id="phone" name="phone" maxlength="15" placeholder="628xxxxxx"
                    pattern="628[0-9]{9,12}"
                    oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" required>

                <label class="form-label" for="username">
                    Username:
                </label>
                <input class="form-input" type="text" placeholder="Masukan Username" id="username" name="username"
                    required>

                <label class="form-label" for="role">
                    Role:
                </label>
                <select class="form-input" name="role" id="role" onchange="checkRoleUpdate(this.value)">
                    <option>Pilih Role</option>
                    <option value="peserta">Peserta</option>
                    <option value="instruktur">Instruktur</option>
                    <option value="manajer">Manajer</option>
                </select>

                <div id="tipeInstrukturDivUpdate" style="display: none;">
                    <label class="form-label" for="tipe_instruktur">
                        Tipe Instruktur:
                    </label>
                    <select class="form-input" name="tipe_instruktur" id="tipe_instruktur">
                        <option value="mobil matic">Mobil Matic</option>
                        <option value="mobil manual">Mobil Manual</option>
                    </select>
                </div>

                <label class="form-label" for="password">
                    Password:
                </label>
                <input class="form-input" type="password" placeholder="Masukan Password" id="password" name="password">
                <ion-icon name="eye-outline" id="togglePassword"
                    style="cursor: pointer; position: relative; left: 250px; bottom: 50px;"></ion-icon>

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
            <h2 style="color: green;">Delete Users</h2>
            <form action="customers.php" method="post" class="form-container">
                <input type="hidden" name="id_user" value="">
                <label class="form-label" for="name">
                    Yakin Menghapus User:
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
        document.addEventListener('DOMContentLoaded', function () {
            const phoneInput = document.getElementById('phone');
            const phoneError = document.getElementById('phone-error');

            phoneInput.addEventListener('input', function () {
                const value = phoneInput.value;
                if (!value.startsWith('628')) {
                    phoneError.style.display = 'block';
                } else {
                    phoneError.style.display = 'none';
                }
            });
        });

        document.getElementById('togglePassword').addEventListener('click', function (e) {
            const password = document.getElementById('password');
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });

        function togglePopupTambah() {
            const overlay = document.getElementById('popupOverlayTambah');
            overlay.classList.toggle('show');
        }

        function togglePopupUpdate(id_user, name, nohp, username, role) {
            const overlay = document.getElementById('popupOverlayUpdate');
            overlay.classList.toggle('show');

            if (id_user && name && nohp && username && role) {
                document.querySelector('#popupOverlayUpdate input[name="id_user"]').value = id_user;
                document.querySelector('#popupOverlayUpdate input[name="name"]').value = name;
                document.querySelector('#popupOverlayUpdate input[name="phone"]').value = nohp;
                document.querySelector('#popupOverlayUpdate input[name="username"]').value = username;
                document.querySelector('#popupOverlayUpdate select[name="role"]').value = role;
            }

            if (role === 'instruktur') {
                document.getElementById('tipeInstrukturDivUpdate').style.display = 'block';
            } else {
                document.getElementById('tipeInstrukturDivUpdate').style.display = 'none';
            }
        }

        function checkRole(role) {
            if (role === 'instruktur') {
                document.getElementById('tipeInstrukturDiv').style.display = 'block';
            } else {
                document.getElementById('tipeInstrukturDiv').style.display = 'none';
            }
        }

        function togglePopupDelete(id_user, name) {
            const overlay = document.getElementById('popupOverlayDelete');
            overlay.classList.toggle('show');

            if (id_user && name) {
                document.querySelector('#popupOverlayDelete input[name="id_user"]').value = id_user;
                document.querySelector('#popupOverlayDelete .form-label').textContent = 'Yakin Menghapus User: ' + name +
                    '?';
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