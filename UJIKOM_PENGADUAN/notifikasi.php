<?php
session_start();
// 1. SET TIMEZONE INDONESIA
date_default_timezone_set('Asia/Jakarta'); 
require 'functions.php';

// 2. PROTEKSI HALAMAN
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'siswa') {
    header("Location: index.php");
    exit;
}

$nis = $_SESSION['id'];

// 3. AMBIL DATA NOTIFIKASI
$query = "SELECT aspirasi.*, kategori.nama_kategori 
          FROM aspirasi 
          JOIN kategori ON aspirasi.id_kategori = kategori.id_kategori
          WHERE nis = '$nis' AND (status != 'Menunggu' OR feedback != '')
          ORDER BY tanggal DESC";
          
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi | Minecloud</title>
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

        /* --- SIDEBAR --- */
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

        /* --- NOTIF LIST --- */
        .notif-container {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .notif-item {
            background: var(--white);
            padding: 24px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.2s;
            border: 1px solid transparent;
        }
        
        .notif-item:hover {
            transform: scale(1.01);
            border-color: var(--primary-light);
        }

        .icon-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .icon-status-selesai { background: #E6FAF5; color: #05CD99; }
        .icon-status-proses { background: #E2F0FB; color: #0075FF; }
        .icon-status-menunggu { background: #FFF9E6; color: #FFB800; }

        .notif-content { flex-grow: 1; }
        .notif-title { font-weight: 700; font-size: 16px; margin-bottom: 4px; }
        .notif-desc { font-size: 14px; color: var(--secondary); line-height: 1.5; }

        .btn-detail {
            padding: 10px 18px;
            background: var(--primary-light);
            color: var(--primary);
            text-decoration: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 13px;
            transition: 0.3s;
            white-space: nowrap;
        }
        .btn-detail:hover { background: var(--primary); color: white; }

        .empty-state {
            text-align: center;
            padding: 100px 0;
            color: var(--text-muted);
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
            .notif-item { flex-direction: column; align-items: flex-start; gap: 15px; }
            .btn-detail { width: 100%; text-align: center; }
            .page-title { font-size: 28px; }
        }

        /* --- SweetAlert Custom Style --- */
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
            <a href="notifikasi.php" class="nav-item active"><i class="fa-solid fa-bell"></i> Notifikasi</a>
            <a href="pengaturan.php" class="nav-item"><i class="fa-solid fa-user-gear"></i> Pengaturan</a>
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
                <p style="font-size: 14px; color: var(--secondary); font-weight: 500;">Utama / Notifikasi</p>
                <h1 class="page-title">Pemberitahuan</h1>
            </div>
        </header>

        <div class="notif-container">
            <?php if(mysqli_num_rows($result) > 0) : ?>
                <?php while($row = mysqli_fetch_assoc($result)) : ?>
                    <div class="notif-item">
                        <div class="icon-circle icon-status-<?= strtolower($row['status']); ?>">
                            <i class="fa-solid <?= $row['status'] == 'Selesai' ? 'fa-check-double' : 'fa-spinner fa-spin'; ?>"></i>
                        </div>
                        <div class="notif-content">
                            <div class="notif-title">
                                Update Laporan: <?= htmlspecialchars($row['nama_kategori']); ?>
                            </div>
                            <div class="notif-desc">
                                Laporan anda di <strong><?= htmlspecialchars($row['lokasi']); ?></strong> sekarang berstatus <strong><?= htmlspecialchars($row['status']); ?></strong>.
                                <?php if($row['feedback']) echo "<br><em>\"" . htmlspecialchars($row['feedback']) . "\"</em>"; ?>
                            </div>
                        </div>
                        <a href="laporan_saya.php" class="btn-detail">Detail</a>
                    </div>
                <?php endwhile; ?>
            <?php else : ?>
                <div class="empty-state">
                    <i class="fa-solid fa-bell-slash" style="font-size: 50px; margin-bottom: 20px; opacity: 0.3;"></i>
                    <p>Belum ada aktivitas terbaru.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Pop-up Konfirmasi Keluar
        document.getElementById('btnLogout').addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Yakin ingin keluar?',
                text: 'Sesi Anda akan berakhir.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4318FF',
                cancelButtonColor: '#707EAE',
                confirmButtonText: 'Keluar Sekarang',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'logout.php';
                }
            });
        });
    </script>
</body>
</html>