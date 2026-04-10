<?php
session_start();
require 'functions.php';

// 1. PROTEKSI HALAMAN
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'siswa') {
    header("Location: index.php");
    exit;
}

$nis = $_SESSION['id'];
$status = ''; // Variabel untuk menampung status pesan

// 2. LOGIKA UPDATE PASSWORD
if (isset($_POST['save'])) {
    $pw_baru = mysqli_real_escape_string($conn, $_POST['password_baru']);
    $konfirmasi = mysqli_real_escape_string($conn, $_POST['konfirmasi_password']);

    if (!empty($pw_baru)) {
        if ($pw_baru === $konfirmasi) {
            $password_fix = $pw_baru;
            $update = mysqli_query($conn, "UPDATE siswa SET password = '$password_fix' WHERE nis = '$nis'");

            if ($update) {
                $status = 'success';
            } else {
                $status = 'error_db';
            }
        } else {
            $status = 'error_match';
        }
    } else {
        $status = 'error_empty';
    }
}

// 3. AMBIL DATA SISWA UNTUK FORM
$user = mysqli_query($conn, "SELECT * FROM siswa WHERE nis = '$nis'");
$row = mysqli_fetch_assoc($user);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan | Minecloud</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #4318FF;
            --primary-light: #F4F7FE;
            --secondary: #707EAE;
            --bg-body: #F4F7FE;
            --sidebar-bg: #FFFFFF;
            --text-main: #1B2559;
            --text-muted: #8F9BBA;
            --white: #FFFFFF;
            --shadow-md: 0px 40px 58px -20px rgba(112, 144, 176, 0.12);
            --radius-lg: 20px;
            --radius-md: 12px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* --- SIDEBAR & MOBILE TOGGLE --- */
        #menu-toggle { display: none; }

        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            padding: 40px 0;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            z-index: 1100;
            transition: all 0.3s ease;
        }

        .brand {
            padding: 0 32px;
            font-size: 26px;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 40px;
            display: flex;
            align-items: center; gap: 12px;
        }
        .brand span { color: var(--primary); }

        .nav-menu { list-style: none; padding: 0 16px; flex-grow: 1; }
        .nav-item {
            display: flex; align-items: center; gap: 16px;
            padding: 16px 20px; color: var(--secondary);
            text-decoration: none; font-weight: 600;
            border-radius: var(--radius-md); transition: 0.3s;
            margin-bottom: 4px;
        }
        .nav-item.active { color: var(--text-main); position: relative; }
        .nav-item.active::after {
            content: ''; position: absolute; right: -16px;
            width: 4px; height: 36px; background: var(--primary); border-radius: 4px;
        }
        .nav-item.active i { color: var(--primary); }
        .nav-item:hover:not(.active) { background: var(--primary-light); color: var(--primary); }

        /* --- MOBILE HEADER --- */
        .mobile-header {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 70px;
            background: var(--white);
            z-index: 1000;
            padding: 0 24px;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #E0E5F2;
        }

        /* --- MAIN CONTENT --- */
        .main-content {
            margin-left: 280px;
            padding: 40px 50px;
            width: 100%;
            transition: all 0.3s ease;
        }

        .header {
            display: flex; justify-content: space-between;
            align-items: flex-start; margin-bottom: 35px;
        }

        .page-title { font-size: 34px; font-weight: 700; color: var(--text-main); }

        /* --- SETTINGS CARD --- */
        .settings-card {
            background: var(--white);
            padding: 32px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            max-width: 650px;
        }

        .form-group { margin-bottom: 24px; }
        .form-group label { 
            display: block; 
            margin-bottom: 10px; 
            font-weight: 700; 
            font-size: 14px;
            color: var(--text-main);
        }
        .form-group input {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid #E0E5F2;
            border-radius: var(--radius-md);
            outline: none;
            font-family: inherit;
            font-size: 15px;
            transition: 0.3s;
        }
        .form-group input:focus { border-color: var(--primary); }
        .form-group input[readonly] {
            background-color: var(--primary-light);
            color: var(--secondary);
            cursor: not-allowed;
        }

        .btn-save {
            background: var(--primary);
            color: white;
            padding: 14px 30px;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: 0.3s;
            width: auto;
            box-shadow: 0px 10px 20px rgba(67, 24, 255, 0.2);
        }
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0px 15px 25px rgba(67, 24, 255, 0.3);
        }

        /* --- RESPONSIVE BREAKPOINTS --- */
        @media (max-width: 1024px) {
            .sidebar { left: -280px; }
            .main-content { margin-left: 0; padding-top: 100px; padding-left: 24px; padding-right: 24px; }
            .mobile-header { display: flex; }
            
            #menu-toggle:checked ~ .sidebar { left: 0; box-shadow: 10px 0 40px rgba(0,0,0,0.1); }
            #menu-toggle:checked ~ .main-content { opacity: 0.3; pointer-events: none; }
        }

        @media (max-width: 600px) {
            .page-title { font-size: 28px; }
            .settings-card { padding: 24px; }
            .btn-save { width: 100%; }
        }

        /* --- SweetAlert Style --- */
        .swal2-popup { border-radius: 24px !important; font-family: 'Plus Jakarta Sans', sans-serif !important; }
        .swal2-title { color: #1B2559 !important; font-weight: 700 !important; }
        .swal2-styled.swal2-confirm { background-color: #4318FF !important; border-radius: 12px !important; }
    </style>
</head>
<body>

    <input type="checkbox" id="menu-toggle">

    <div class="mobile-header">
        <div class="brand" style="padding: 0; margin-bottom: 0; font-size: 20px;">
            <i class="fa-solid fa-rocket"></i> <span>Dashboard Siswa</span>
        </div>
        <label for="menu-toggle" style="cursor: pointer; font-size: 24px; color: var(--primary);">
            <i class="fa-solid fa-bars"></i>
        </label>
    </div>

    <aside class="sidebar">
        <div class="brand"><i class="fa-solid fa-rocket"></i> <span>Dashboard Siswa</span></div>
        <nav class="nav-menu">
            <a href="siswa.php" class="nav-item"><i class="fa-solid fa-house"></i> Dashboard</a>
            <a href="laporan_saya.php" class="nav-item"><i class="fa-solid fa-file-invoice"></i> Laporan Saya</a>
            <a href="notifikasi.php" class="nav-item"><i class="fa-solid fa-bell"></i> Notifikasi</a>
            <a href="pengaturan.php" class="nav-item active"><i class="fa-solid fa-user-gear"></i> Pengaturan</a>
        </nav>
        <div style="padding: 0 16px;">
            <a href="#" class="nav-item" style="color: #EE5D50;" id="btnLogout">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar
            </a>
        </div>
    </aside>

    <main class="main-content">
        <header class="header">
            <div>
                <p style="font-size: 14px; color: var(--secondary); font-weight: 500;">Utama / Pengaturan</p>
                <h1 class="page-title">Keamanan Akun</h1>
            </div>
        </header>

        <div class="settings-card">
            <form action="" method="POST">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" value="<?= htmlspecialchars($row['nama']); ?>" readonly title="Nama tidak dapat diubah">
                </div>
                
                <div class="form-group">
                    <label>NIS (Nomor Induk Siswa)</label>
                    <input type="text" value="<?= htmlspecialchars($row['nis']); ?>" readonly>
                </div>

                <hr style="border: 0; border-top: 1px solid #E0E5F2; margin-bottom: 24px;">

                <div class="form-group">
                    <label>Password Baru</label>
                    <input type="password" name="password_baru" placeholder="Masukkan password baru" required>
                </div>
                
                <div class="form-group">
                    <label>Konfirmasi Password Baru</label>
                    <input type="password" name="konfirmasi_password" placeholder="Ulangi password baru" required>
                </div>

                <button type="submit" name="save" class="btn-save">
                    <i class="fa-solid fa-floppy-disk" style="margin-right: 8px;"></i> Simpan Perubahan
                </button>
            </form>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // SweetAlert Handler
        <?php if($status == 'success'): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Password kamu telah diperbarui.',
                showConfirmButton: false,
                timer: 2000
            }).then(() => {
                window.location.href = 'pengaturan.php';
            });
        <?php elseif($status == 'error_match'): ?>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Konfirmasi password tidak cocok!',
                confirmButtonColor: '#4318FF'
            });
        <?php elseif($status == 'error_db'): ?>
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan Sistem',
                text: 'Gagal memperbarui data ke database.',
                confirmButtonColor: '#4318FF'
            });
        <?php endif; ?>

        // Logout Handler
        document.getElementById('btnLogout').addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Yakin ingin keluar?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4318FF',
                cancelButtonColor: '#707EAE',
                confirmButtonText: 'Keluar Sekarang',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'logout.php';
                }
            });
        });
    </script>
</body>
</html>