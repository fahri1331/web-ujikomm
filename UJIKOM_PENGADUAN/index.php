<?php
session_start();

// Koneksi Database
$conn = mysqli_connect("localhost", "root", "", "db_pengaduan_sekolah1");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

$swal = ""; 
$nama_user = ""; // Variabel pembantu agar tidak undefined

// --- LOGIKA LOGIN ---
if (isset($_POST['login'])) {
    $user_input = mysqli_real_escape_string($conn, $_POST['username']);
    $password   = $_POST['password'];

    $query_admin = mysqli_query($conn, "SELECT * FROM admin WHERE username='$user_input'");
    
    if (mysqli_num_rows($query_admin) === 1) {
        $row = mysqli_fetch_assoc($query_admin);
        if ($password === $row['password']) {
            $_SESSION['login'] = true;
            $_SESSION['role'] = 'admin';
            $_SESSION['id']   = $row['username'];
            $_SESSION['nama_petugas'] = $row['nama_petugas'];
            $swal = "success-login-admin";
        } else {
            $swal = "error-password-admin";
        }
    } else {
        $query_siswa = mysqli_query($conn, "SELECT * FROM siswa WHERE nis='$user_input'");
        if (mysqli_num_rows($query_siswa) === 1) {
            $row = mysqli_fetch_assoc($query_siswa);
            if ($password === $row['password']) {
                $_SESSION['login'] = true;
                $_SESSION['role'] = 'siswa';
                $_SESSION['id']   = $row['nis']; 
                $_SESSION['nama'] = $row['nama'];
                $nama_user = $row['nama']; // Simpan ke variabel lokal untuk JS
                $swal = "success-login-siswa";
            } else {
                $swal = "error-password-siswa";
            }
        } else {
            $swal = "error-notfound";
        }
    }
}

// --- LOGIKA REGISTER ---
if (isset($_POST['register'])) {
    $nis      = mysqli_real_escape_string($conn, $_POST['username']);
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $kelas    = mysqli_real_escape_string($conn, $_POST['kelas']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $cek_nis = mysqli_query($conn, "SELECT * FROM siswa WHERE nis='$nis'");
    if (mysqli_num_rows($cek_nis) > 0) {
        $swal = "error-exists";
    } else {
        $insert = mysqli_query($conn, "INSERT INTO siswa (nis, nama, kelas, password) VALUES ('$nis', '$nama', '$kelas', '$password')");
        if ($insert) {
            $swal = "success-register";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aspirasi Siswa | Masuk</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-blue: #4318FF; 
            --dark-blue: #3311CC;
            --soft-bg: #F4F7FE;
            --white: #ffffff;
            --text-main: #1B2559;
            --text-muted: #8F9BBA;
            --input-border: #E0E5F2;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--soft-bg);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .main-card {
            background: var(--white);
            width: 100%;
            max-width: 1000px;
            height: 720px; 
            border-radius: 24px;
            display: flex;
            overflow: hidden;
            box-shadow: 0px 40px 58px -20px rgba(112, 144, 176, 0.12);
            position: relative;
        }

        .left-side {
            flex: 1;
            padding: 45px 60px 80px 60px;
            display: flex;
            flex-direction: column;
            background: white;
            position: relative;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text-main);
            font-weight: 800;
            font-size: 24px;
            margin-bottom: 30px;
        }
        .brand-logo i, .brand-logo span { color: var(--primary-blue); }

        .toggle-container {
            display: flex;
            background: #F4F7FE;
            padding: 6px;
            border-radius: 14px;
            margin-bottom: 30px;
            width: fit-content;
        }

        .toggle-btn {
            padding: 10px 30px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-family: inherit;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.3s ease;
            background: transparent;
            color: var(--text-muted);
        }

        .toggle-btn.active {
            background: var(--white);
            color: var(--primary-blue);
            box-shadow: 0px 10px 20px rgba(112, 144, 176, 0.1);
        }

        .form-container {
            flex: 1;
            position: relative;
            overflow: hidden;
        }

        .form-slider {
            display: flex;
            width: 200%;
            height: 100%;
            transition: transform 0.6s cubic-bezier(0.7, 0, 0.3, 1);
        }

        .form-section {
            width: 50%;
            height: 100%;
            display: flex;
            flex-direction: column;
            padding-right: 15px;
        }

        .form-slider.slide-active {
            transform: translateX(-50%);
        }

        .welcome-text h2 { 
            font-size: 32px; 
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 6px;
        }

        .welcome-text p { 
            color: var(--text-muted); 
            font-size: 15px;
            margin-bottom: 25px;
        }

        .input-group { 
            margin-bottom: 16px; 
        }

        .input-group label { 
            display: block; 
            font-size: 14px; 
            font-weight: 700; 
            margin-bottom: 8px; 
            color: var(--text-main);
        }

        .input-group input {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid var(--input-border);
            border-radius: 12px;
            font-family: inherit;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #fff;
        }

        .input-group input:focus { 
            outline: none; 
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 4px rgba(67, 24, 255, 0.05);
        }

        .submit-btn {
            width: 100%;
            padding: 16px;
            background: var(--primary-blue);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 5px;
            box-shadow: 0px 10px 20px rgba(67, 24, 255, 0.2);
        }

        .submit-btn:hover { 
            background: var(--dark-blue); 
            transform: translateY(-2px);
            box-shadow: 0px 15px 25px rgba(67, 24, 255, 0.3);
        }

        .back-home {
            margin-top: 25px;
            text-align: center;
        }

        .back-home a {
            text-decoration: none;
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 600;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-home a:hover {
            color: var(--primary-blue);
        }

        .footer-note { 
            position: absolute;
            bottom: 30px;
            left: 60px;
            font-size: 12px; 
            color: var(--text-muted);
            font-weight: 500;
        }

        .right-side {
            flex: 1.1;
            background: linear-gradient(135deg, #4318FF 0%, #3311CC 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px;
            color: white;
            position: relative;
        }

        .visual-cards { 
            position: relative; 
            width: 100%; 
            height: 300px;
            margin-bottom: 40px;
        }

        .floating-card {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 18px;
            padding: 22px;
            position: absolute;
            width: 220px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .card-1 { top: 0; left: 10%; transform: rotate(-6deg); }
        .card-2 { top: 60px; right: 5%; transform: rotate(4deg); }
        .card-3 { bottom: 10px; left: 20%; }

        .right-side h3 { 
            font-size: 28px; 
            text-align: center; 
            line-height: 1.3; 
            max-width: 420px;
            margin-bottom: 15px;
            font-weight: 800;
        }

        .right-side p { 
            text-align: center; 
            opacity: 0.85; 
            max-width: 380px; 
            font-size: 15px; 
            line-height: 1.6;
            font-weight: 500;
        }

        @media (max-width: 900px) {
            .main-card { height: auto; flex-direction: column; }
            .right-side { display: none; }
            .left-side { padding: 40px; }
            .footer-note { position: static; margin-top: 30px; }
        }
    </style>
</head>
<body>

<div class="main-card">
    <div class="left-side">
        <div class="brand-logo">
            <i class="fa-solid fa-rocket"></i> 
            <span>Aspirasi Siswa</span>
        </div>

        <div class="toggle-container">
            <button id="tgl-login" class="toggle-btn active" onclick="switchForm('login')">Masuk</button>
            <button id="tgl-reg" class="toggle-btn" onclick="switchForm('register')">Daftar</button>
        </div>

        <div class="form-container">
            <div class="form-slider" id="mainSlider">
                <div class="form-section">
                    <div class="welcome-text">
                        <h2>Selamat Datang</h2>
                        <p>Silakan masuk untuk akses dashboard aspirasi.</p>
                    </div>

                    <form action="" method="POST">
                        <div class="input-group">
                            <label>ID Pengguna / NIS</label>
                            <input type="text" name="username" placeholder="Masukkan NIS Anda" required>
                        </div>
                        <div class="input-group">
                            <label>Kata Sandi</label>
                            <input type="password" name="password" placeholder="Masukkan kata sandi" required>
                        </div>
                        <button type="submit" name="login" class="submit-btn">Masuk Sekarang</button>
                    </form>

                    <div class="back-home">
                        <a href="deskripsi_web.php"><i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda</a>
                    </div>
                </div>

                <div class="form-section">
                    <div class="welcome-text">
                        <h2>Daftar Akun</h2>
                        <p>Buat akun baru untuk mulai berkontribusi.</p>
                    </div>

                    <form action="" method="POST">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div class="input-group">
                                <label>NIS</label>
                                <input type="text" name="username" placeholder="NIS" required>
                            </div>
                            <div class="input-group">
                                <label>Kelas</label>
                                <input type="text" name="kelas" placeholder="Cth: XII RPL 1" required>
                            </div>
                        </div>
                        <div class="input-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="nama" placeholder="Sesuai Kartu Pelajar" required>
                        </div>
                        <div class="input-group">
                            <label>Kata Sandi</label>
                            <input type="password" name="password" placeholder="Yang Mudah Anda Ingat" required>
                        </div>
                        <button type="submit" name="register" class="submit-btn">Daftar Sekarang</button>
                    </form>
                    
                    <div class="back-home">
                        <a href="deskripsi_web.php"><i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-note">
            Hak Cipta © 2026 Minecloud. Seluruh Hak Dilindungi.
        </div>
    </div>

    <div class="right-side">
        <div class="visual-cards">
            <div class="floating-card card-1">
                <small style="opacity: 0.8; font-size: 10px; font-weight: 700;">LAPORAN TERKIRIM</small>
                <div style="font-weight: 700; font-size: 15px; margin: 8px 0;">Fasilitas Kelas</div>
                <div style="width: 100%; height: 5px; background: rgba(255,255,255,0.2); border-radius: 10px; overflow: hidden;">
                    <div style="width: 75%; height: 100%; background: #fff;"></div>
                </div>
            </div>
            <div class="floating-card card-2">
                <small style="opacity: 0.8; font-size: 10px; font-weight: 700;">STATUS TERBARU</small>
                <div style="font-weight: 700; font-size: 15px; margin: 8px 0;">Selesai</div>
                <div style="display: flex; align-items: center; gap: 5px; font-size: 11px; color: #fff;">
                    <i class="fa-solid fa-circle-check"></i> Terverifikasi Admin
                </div>
            </div>
            <div class="floating-card card-3">
                <small style="opacity: 0.8; font-size: 10px; font-weight: 700;">TINGKAT RESPON</small>
                <div style="font-weight: 700; font-size: 16px; margin-top: 5px;">95% Cepat</div>
            </div>
        </div>
        <h3>Platform Aspirasi Digital Modern</h3>
        <p>Sampaikan keluhan dan saranmu secara langsung. Mari berkolaborasi menciptakan lingkungan sekolah yang lebih transparan.</p>
    </div>
</div>

<script>
    function switchForm(type) {
        const slider = document.getElementById('mainSlider');
        const btnLogin = document.getElementById('tgl-login');
        const btnReg = document.getElementById('tgl-reg');
        if (type === 'register') {
            slider.classList.add('slide-active');
            btnReg.classList.add('active');
            btnLogin.classList.remove('active');
        } else {
            slider.classList.remove('slide-active');
            btnLogin.classList.add('active');
            btnReg.classList.remove('active');
        }
    }

    const swalType = "<?= $swal; ?>";
    const namaUser = "<?= $nama_user; ?>";
    
    if (swalType === "success-login-admin") {
        Swal.fire({
            icon: 'success',
            title: 'Login Berhasil',
            text: 'Selamat datang, Administrator!',
            confirmButtonColor: '#4318FF'
        }).then(() => { window.location = 'admin.php'; });
    } else if (swalType === "success-login-siswa") {
        Swal.fire({
            icon: 'success',
            title: 'Login Berhasil',
            text: 'Selamat datang, ' + namaUser + '!',
            confirmButtonColor: '#4318FF'
        }).then(() => { window.location = 'siswa.php'; });
    } else if (swalType === "error-password-admin" || swalType === "error-password-siswa") {
        Swal.fire({
            icon: 'error',
            title: 'Gagal Masuk',
            text: 'Kata sandi yang Anda masukkan salah!',
            confirmButtonColor: '#4318FF'
        });
    } else if (swalType === "error-notfound") {
        Swal.fire({
            icon: 'warning',
            title: 'Akun Tidak Ada',
            text: 'NIS atau Username tidak terdaftar di sistem.',
            confirmButtonColor: '#4318FF'
        });
    } else if (swalType === "success-register") {
        Swal.fire({
            icon: 'success',
            title: 'Registrasi Berhasil',
            text: 'Akun Anda sudah terdaftar, silakan login.',
            confirmButtonColor: '#4318FF'
        }).then(() => { switchForm('login'); });
    } else if (swalType === "error-exists") {
        Swal.fire({
            icon: 'error',
            title: 'Registrasi Gagal',
            text: 'NIS tersebut sudah digunakan akun lain!',
            confirmButtonColor: '#4318FF'
        });
    }
</script>
</body>
</html>