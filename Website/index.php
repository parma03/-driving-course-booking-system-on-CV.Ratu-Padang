<?php
session_start();
include 'config/koneksi.php';

// Pengecekan session untuk redirect jika sudah login
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/index.php");
        exit();
    } else if ($_SESSION['role'] === 'peserta') {
        header("Location: peserta/index.php");
        exit();
    } else if ($_SESSION['role'] === 'instruktur') {
        header("Location: instruktur/index.php");
        exit();
    } else if ($_SESSION['role'] === 'manejer') {
        header("Location: manejer/index.php");
        exit();
    }
}

function setNotification($message, $type)
{
    $_SESSION['notification'] = ['message' => $message, 'type' => $type];
}

function isPasswordValid($password)
{
    return preg_match('/[A-Za-z]/', $password) && preg_match('/[0-9]/', $password) && preg_match('/[^A-Za-z0-9]/', $password);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $name = $_POST['name'];
    $nohp = $_POST['phone'];

    if (!isPasswordValid($password)) {
        setNotification("Password harus mengandung huruf, angka, dan simbol!", "danger");
        header("Location: index.php");
        exit();
    }

    $hashed_password = md5($password);

    $query_user = "INSERT INTO tb_user (username, password, role) VALUES (?, ?, 'peserta')";
    $stmt = $conn->prepare($query_user);
    $stmt->bind_param("ss", $username, $hashed_password);

    if ($stmt->execute()) {
        $last_id = $stmt->insert_id;

        $query_konsumen = "INSERT INTO tb_konsumen (id_konsumen, name_konsumen, nohp) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query_konsumen);
        $stmt->bind_param("iss", $last_id, $name, $nohp);

        if ($stmt->execute()) {
            setNotification("Berhasil Register Silahkan Login", "success");
            header("Location: index.php#success");
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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = md5($_POST['password']);

    $query = "SELECT * FROM tb_user WHERE username=? AND password=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['username'];

        if ($user['role'] === 'admin') {
            setNotification("Berhasil Login Admin", "success");
            header("Location: admin/index.php");
            exit();
        } else if ($user['role'] === 'peserta') {
            setNotification("Berhasil Login Peserta", "success");
            header("Location: peserta/index.php");
            exit();
        } else if ($user['role'] === 'instruktur') {
            setNotification("Berhasil Login Instruktur", "success");
            header("Location: instruktur/index.php");
            exit();
        } else if ($user['role'] === 'manajer') {
            setNotification("Berhasil Login Manajer", "success");
            header("Location: manajer/index.php");
            exit();
        }

    } else {
        setNotification("Username atau password salah!", "danger");
        header("Location: index.php#success");
        exit();
    }
    $stmt->close();
    $conn->close();
}

$query_matic = "SELECT * FROM tb_jenis_pelatihan WHERE kategori = 'Mobil Matic'";
$result_matic = $conn->query($query_matic);

$query_manual = "SELECT * FROM tb_jenis_pelatihan WHERE kategori = 'Mobil Manual'";
$result_manual = $conn->query($query_manual);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV. RATU MENGEMUDI</title>
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
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
</head>

<body class="main-content">
    <!-- Notification -->
    <?php if (isset($_SESSION['notification'])): ?>
        <div class="notification is-<?php echo $_SESSION['notification']['type']; ?>" id="notification">
            <?php echo $_SESSION['notification']['message']; ?>
        </div>
        <?php unset($_SESSION['notification']); ?>
    <?php endif; ?>
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            if (window.location.hash === '#success') {
                document.querySelector(".active-btn").classList.remove("active-btn");
                document.querySelector("[data-id='login']").classList.add("active-btn");
                document.querySelector(".active").classList.remove("active");
                document.getElementById('login').classList.add("active");
                document.getElementById('form-login').style.display = 'block';
            }
            const notification = document.getElementById('notification');
            if (notification) {
                notification.style.display = 'block';
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 5000);
            }

        });
    </script>
    <header class="container header active" id="home">
        <div class="header-content">
            <div class="left-header">
                <div class="h-shape"></div>
                <div class="image">
                    <img src="img/logo.png" alt="">
                </div>
            </div>
            <div class="right-header">
                <h1 class="name">
                    CV. <span>RATU MENGEMUDI</span>
                    Kursus Mengemudi
                </h1>
                <p>
                    Selamat datang di CV. Ratu Mengemudi merupakan pelaku bisnis dalam bidang kursus mengemudi kepada
                    individu yang ingin
                    memperoleh
                    keterampilan mengemudi dengan aman dan professional.!
                </p>
            </div>
        </div>
    </header>
    <main>
        <section class="container about" id="about">
            <div class="main-title">
                <h2>Ser<span>vice</span><span class="bg-text">Service</span></h2>
            </div>
            <div class="about-container">
                <div class="left-about">
                    <h4>Kenapa Memilih Kami?</h4>
                    <p>
                        Beberapa keunggulan yang kami miliki untuk menjaga kualitas dan mutu pengajaran yang baik
                    </p>
                </div>
                <div class="right-about">
                    <div class="about-item">
                        <div class="abt-text">
                            <p class="large-text">200+</p>
                            <p class="small-text">Siswa <br /> Mengemudi</p>
                        </div>
                    </div>
                    <div class="about-item">
                        <div class="abt-text">
                            <p class="large-text">5+</p>
                            <p class="small-text">Rating</p>
                        </div>
                    </div>
                    <div class="about-item">
                        <div class="abt-text">
                            <p class="large-text">500+</p>
                            <p class="small-text">Jam <br /> Mengajar</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="container" id="matic">
            <div class="blogs-content">
                <div class="main-title">
                    <h2>Paket <span>Matic</span><span class="bg-text">Paket Matic</span></h2>
                </div>
                <div class="blogs">
                    <?php while ($matic = mysqli_fetch_array($result_matic)) { ?>
                        <div class="blog">
                            <img src="img/matic.jpg" alt="">
                            <div class="blog-text">
                                <h3 style="font-size: 1em;">
                                    <?php echo $matic['nama_jenis']; ?> <span
                                        style="float: right; font-size: 1em;"><?php echo "Rp " . number_format($matic['harga'], 0, ',', '.'); ?></span>
                                </h3>
                                <p style="font-size: 0.8em;">
                                    <?php echo $matic['keterangan']; ?>
                                </p>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </section>
        <section class="container" id="manual">
            <div class="blogs-content">
                <div class="main-title">
                    <h2>Paket <span>Manual</span><span class="bg-text">Paket Manual</span></h2>
                </div>
                <div class="blogs">
                    <?php while ($manual = mysqli_fetch_array($result_manual)) { ?>
                        <div class="blog">
                            <img src="img/manual.jpg" alt="">
                            <div class="blog-text">
                                <h3 style="font-size: 1em;">
                                    <?php echo $manual['nama_jenis']; ?> <span
                                        style="float: right; font-size: 1em;"><?php echo "Rp " . number_format($manual['harga'], 0, ',', '.'); ?></span>
                                </h3>
                                <p style="font-size: 0.8em;">
                                    <?php echo $manual['keterangan']; ?>
                                </p>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </section>
        <section class="container contact" id="login">
            <div class="contact-container">
                <div class="main-title">
                    <h2 style="font-size: 2rem;">LOGIN<span style="font-size: 2rem;">/REGISTER</span><span
                            style="font-size: 2.5rem;" class="bg-text">LOGIN/REGISTER</span>
                    </h2>
                </div>
                <div class="contact-content-con">
                    <div class="right-contact">
                        <div style="float: center; justify-content: center;" id="logreg" class="submit-btn">
                            <a class="main-btn" id="btn-login">
                                <span class="btn-text">Login</span>
                            </a>
                            &nbsp<p>/</p>&nbsp
                            <a class="main-btn" id="btn-register">
                                <span class="btn-text">Register</span>
                            </a>
                        </div>
                        <form action="index.php" method="post" class="contact-form" id="form-login">
                            <div class="input-control">
                                <input type="text" name="username" required placeholder="Username">
                            </div>
                            <div class="input-control" style="position: relative;">
                                <input type="password" name="password" required placeholder="Password" id="password1"
                                    style="padding-right: 30px;">
                                <i class="fas fa-eye" id="togglePassword1"
                                    style="cursor: pointer; position: absolute; right: 15px; top: 15px;"></i>
                            </div>
                            <div style="float: right;" class="submit-btn">
                                <button type="submit" name="login" class="main-btn">
                                    <span class="btn-text">Login</span>
                                    <span class="btn-icon"><i class="fas fa-lock-open"></i></span>
                                </button>
                            </div>
                        </form>

                        <form action="index.php" method="post" class="contact-form" id="form-register">
                            <div class="input-control i-c-2">
                                <input type="text" name="name" required placeholder="Nama Anda">&nbsp;
                                <input type="tel" id="phone" name="phone" maxlength="15" placeholder="NOHP: 628xxxxxx"
                                    pattern="628[0-9]{9,12}"
                                    oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                                    required>
                                <span id="phone-error" style="color: red; display: none;">Harus diawali dengan
                                    628</span>
                            </div>
                            <div class="input-control">
                                <input type="text" name="username" required placeholder="Username">
                            </div>
                            <div class="input-control" style="position: relative;">
                                <input type="password" name="password" required placeholder="Password" id="password"
                                    style="padding-right: 30px;">
                                <i class="fas fa-eye" id="togglePassword"
                                    style="cursor: pointer; position: absolute; right: 15px; top: 15px;"></i>
                                <span style="color: red; font-size: 14px;">Password Menggunakan
                                    Angka,
                                    Huruf, dan simbol</span>
                            </div>

                            <div style="float: right;" class="submit-btn">
                                <button type="submit" id="button-reg" name="register" class="main-btn">
                                    <span class="btn-text">Register</span>
                                    <span class="btn-icon"><i class="fas fa-registered"></i></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <div class="controls">
        <div class="control active-btn" data-id="home">
            <i class="fas fa-home"></i>
        </div>
        <div class="control" data-id="about">
            <i class="fas fa-business-time"></i>
        </div>
        <div class="control" data-id="matic">
            <i class="fas fa-car"></i>
        </div>
        <div class="control" data-id="manual">
            <i class="fas fa-car-side"></i>
        </div>
        <div class="control" data-id="login">
            <i class="fas fa-user-lock"></i>
        </div>
    </div>
    <div class="theme-btn">
        <i class="fas fa-adjust"></i>
    </div>

    <script>
        (function () {
            [...document.querySelectorAll(".control")].forEach(button => {
                button.addEventListener("click", function () {
                    document.querySelector(".active-btn").classList.remove("active-btn");
                    this.classList.add("active-btn");
                    document.querySelector(".active").classList.remove("active");
                    document.getElementById(button.dataset.id).classList.add("active");
                })
            });
            document.querySelector(".theme-btn").addEventListener("click", () => {
                document.body.classList.toggle("light-mode");
            })
        })();

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

            document.getElementById('form-login').style.display = 'none';
            document.getElementById('form-register').style.display = 'none';

            document.getElementById('btn-login').addEventListener('click', function () {
                document.getElementById('form-login').style.display = 'block';
                document.getElementById('form-register').style.display = 'none';
                updateHeaderText('Log', 'in', 'Login');
            });

            document.getElementById('btn-register').addEventListener('click', function () {
                document.getElementById('form-login').style.display = 'none';
                document.getElementById('form-register').style.display = 'block';
                updateHeaderText('Regi', 'ster', 'Register');
            });

            function updateHeaderText(mainText, spanText, bgText) {
                document.querySelector('.contact-container .main-title h2').innerHTML = mainText + '<span>' +
                    spanText + '</span><span class="bg-text">' + bgText + '</span>';
            }
        });

        document.getElementById('togglePassword').addEventListener('click', function (e) {
            const password = document.getElementById('password');
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });

        document.getElementById('togglePassword1').addEventListener('click', function (e) {
            const password = document.getElementById('password1');
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>

</html>