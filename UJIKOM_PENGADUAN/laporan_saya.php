<?php
session_start();
require 'functions.php';

// 1. PROTEKSI HALAMAN
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'siswa') {
    header("Location: index.php");
    exit;
}

$nis = $_SESSION['id'];

// 2. AMBIL DATA LAPORAN
$query = "SELECT aspirasi.*, kategori.nama_kategori 
          FROM aspirasi 
          JOIN kategori ON aspirasi.id_kategori = kategori.id_kategori
          WHERE nis = '$nis' 
          ORDER BY tanggal DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Saya | Dashboard Siswa</title>
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
            --shadow-sm: 0px 2px 12px rgba(0, 0, 0, 0.04);
            --shadow-md: 0px 40px 58px -20px rgba(112, 144, 176, 0.12);
            --radius-lg: 20px;
            --radius-md: 12px;
            --sidebar-width: 280px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            letter-spacing: -0.02em;
            overflow-x: hidden;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            padding: 40px 0;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0;
            height: 100vh;
            z-index: 1200;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow-y: auto;
        }

        /* ===== OVERLAY ===== */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 1100;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        /* ===== MOBILE HEADER ===== */
        .mobile-header {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 65px;
            background: var(--white);
            z-index: 1300;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            border-bottom: 1px solid #E0E5F2;
            box-shadow: var(--shadow-sm);
        }

        .hamburger-btn {
            background: var(--primary-light);
            border: none;
            width: 40px; height: 40px;
            border-radius: var(--radius-md);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            color: var(--primary);
            font-size: 18px;
            transition: background 0.2s;
        }
        .hamburger-btn:hover { background: var(--primary); color: white; }

        /* ===== BRAND ===== */
        .brand {
            padding: 0 32px;
            font-size: 26px;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand span { color: var(--primary); }

        /* ===== NAV ===== */
        .nav-menu { list-style: none; padding: 0 16px; flex-grow: 1; }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            color: var(--secondary);
            text-decoration: none;
            font-weight: 600;
            border-radius: var(--radius-md);
            transition: all 0.3s ease;
            margin-bottom: 4px;
        }
        .nav-item i { font-size: 18px; }
        .nav-item.active {
            background: transparent;
            color: var(--text-main);
            position: relative;
        }
        .nav-item.active::after {
            content: '';
            position: absolute;
            right: -16px;
            width: 4px;
            height: 36px;
            background: var(--primary);
            border-radius: 4px;
        }
        .nav-item.active i { color: var(--primary); }
        .nav-item:hover:not(.active) { background: var(--primary-light); color: var(--primary); }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 40px 50px;
            width: calc(100% - var(--sidebar-width));
            transition: margin-left 0.3s ease, width 0.3s ease;
            min-height: 100vh;
        }

        /* ===== HEADER ===== */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 35px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .breadcrumb { font-size: 14px; color: var(--secondary); font-weight: 500; margin-bottom: 4px; }
        .page-title { font-size: 34px; font-weight: 700; color: var(--text-main); }

        .user-pill {
            display: flex;
            align-items: center;
            background: var(--white);
            padding: 8px;
            border-radius: 50px;
            box-shadow: var(--shadow-sm);
            flex-shrink: 0;
        }
        .user-avatar {
            width: 40px; height: 40px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 16px;
            flex-shrink: 0;
        }

        /* ===== CARD ===== */
        .card {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 30px;
            box-shadow: var(--shadow-md);
        }
        .card-header {
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* ===== TABLE ===== */
        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            width: 100%;
        }

        table { width: 100%; border-collapse: collapse; min-width: 600px; }
        th {
            text-align: left;
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 15px 10px;
            border-bottom: 1px solid #F4F7FE;
            white-space: nowrap;
        }
        td {
            padding: 20px 10px;
            font-size: 14px;
            border-bottom: 1px solid #F4F7FE;
            vertical-align: top;
        }

        /* ===== BADGE ===== */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }
        .status-menunggu { background: #FFFBD1; color: #FFB800; }
        .status-proses { background: #E2F0FB; color: #0075FF; }
        .status-selesai { background: #E6FAF5; color: #05CD99; }
        .dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex-shrink: 0; }

        .thumb-img {
            width: 50px; height: 50px;
            object-fit: cover;
            border-radius: 10px;
            flex-shrink: 0;
        }

        .feedback-box {
            background: var(--primary-light);
            padding: 10px;
            border-radius: 8px;
            font-size: 12px;
            color: var(--primary);
            margin-top: 5px;
            border-left: 3px solid var(--primary);
        }

        /* ===== SWEETALERT ===== */
        .swal2-popup {
            border-radius: 24px !important;
            padding: 2rem !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }
        .swal2-title {
            color: #1B2559 !important;
            font-weight: 700 !important;
            font-size: 28px !important;
            margin-top: 20px !important;
        }
        .swal2-icon.swal2-question {
            border-color: #A3AED0 !important;
            color: #A3AED0 !important;
        }
        .swal2-styled.swal2-confirm {
            background-color: #4318FF !important;
            border-radius: 12px !important;
            padding: 12px 30px !important;
            font-weight: 600 !important;
            box-shadow: 0px 10px 20px rgba(67, 24, 255, 0.2) !important;
        }
        .swal2-styled.swal2-cancel {
            background-color: #707EAE !important;
            border-radius: 12px !important;
            padding: 12px 30px !important;
            font-weight: 600 !important;
        }

        /* ===== RESPONSIVE ===== */

        /* Tablet: <= 1024px — sidebar jadi drawer */
        @media (max-width: 1024px) {
            .mobile-header { display: flex; }

            .sidebar {
                transform: translateX(-100%);
                box-shadow: none;
                padding-top: 80px;
            }

            .sidebar.is-open {
                transform: translateX(0);
                box-shadow: 10px 0 40px rgba(0,0,0,0.15);
            }

            .sidebar-overlay.is-open {
                display: block;
                opacity: 1;
            }

            .nav-item.active::after { display: none; }

            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 90px 24px 40px 24px;
            }
        }

        /* Mobile: <= 768px */
        @media (max-width: 768px) {
            .main-content { padding: 85px 16px 40px 16px; }
            .page-title { font-size: 26px; }
            .card { padding: 18px; border-radius: 16px; }
        }

        /* Small Mobile: <= 480px */
        @media (max-width: 480px) {
            .page-title { font-size: 22px; }
            .brand { font-size: 20px; padding: 0 24px; }
        }

        /* ===== PRINT ===== */
        @media print {
            .sidebar, .mobile-header, .sidebar-overlay { display: none !important; }
            .main-content { margin-left: 0; width: 100%; padding: 0; }
            .card { box-shadow: none; }
        }
    </style>
</head>
<body>

<!-- Overlay Sidebar -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- Mobile Header -->
<div class="mobile-header">
    <div class="brand" style="margin-bottom: 0; font-size: 18px; padding: 0;">
        <i class="fa-solid fa-rocket" style="font-size: 20px;"></i>
        <span>Dashboard Siswa</span>
    </div>
    <button class="hamburger-btn" onclick="toggleSidebar()" aria-label="Toggle Menu">
        <i class="fa-solid fa-bars"></i>
    </button>
</div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="brand">
        <i class="fa-solid fa-rocket"></i>
        <span>Dashboard Siswa</span>
    </div>
    <nav class="nav-menu">
        <a href="siswa.php" class="nav-item">
            <i class="fa-solid fa-house"></i> Dashboard
        </a>
        <a href="laporan_saya.php" class="nav-item active">
            <i class="fa-solid fa-file-invoice"></i> Laporan Saya
        </a>
        <a href="notifikasi.php" class="nav-item">
            <i class="fa-solid fa-bell"></i> Notifikasi
        </a>
        <a href="pengaturan.php" class="nav-item">
            <i class="fa-solid fa-user-gear"></i> Pengaturan
        </a>
    </nav>
    <div style="padding: 0 16px;">
        <a href="#" class="nav-item" style="color: #EE5D50;" id="btnLogout">
            <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar
        </a>
    </div>
</aside>

<!-- Main Content -->
<main class="main-content">
    <header class="header">
        <div>
            <p class="breadcrumb">Utama / Laporan Saya</p>
            <h1 class="page-title">Arsip Aspirasi</h1>
        </div>
        <div class="user-pill">
            <div class="user-avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 1)); ?></div>
        </div>
    </header>

    <section class="card">
        <div class="card-header">
            <h3 style="font-weight: 700; color: var(--text-main);">Daftar Seluruh Laporan</h3>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Detail & Feedback</th>
                        <th>Foto</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0) : ?>
                        <?php while($row = mysqli_fetch_assoc($result)) : ?>
                        <tr>
                            <td style="color: var(--secondary); font-weight: 600; white-space: nowrap;">
                                <?= date('d M Y', strtotime($row['tanggal'])); ?>
                            </td>
                            <td>
                                <span style="font-weight: 700; color: var(--text-main); white-space: nowrap;"><?= $row['nama_kategori']; ?></span>
                            </td>
                            <td style="min-width: 200px;">
                                <div style="font-weight: 700; color: var(--text-main);"><?= $row['lokasi']; ?></div>
                                <div style="color: var(--secondary); margin-bottom: 8px; font-size: 13px;"><?= $row['keterangan']; ?></div>
                                <?php if(!empty($row['feedback'])) : ?>
                                    <div class="feedback-box">
                                        <strong>Respon Petugas:</strong><br>
                                        <?= $row['feedback']; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($row['foto']): ?>
                                    <img src="./assets/img/<?= $row['foto']; ?>" class="thumb-img">
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 12px; white-space: nowrap;">Tanpa Foto</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge status-<?= strtolower($row['status']); ?>">
                                    <div class="dot"></div>
                                    <?= $row['status']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 50px; color: var(--text-muted);">
                                Belum ada riwayat laporan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    /* ===== Sidebar Toggle ===== */
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.toggle('is-open');
        overlay.classList.toggle('is-open');
        document.body.style.overflow = sidebar.classList.contains('is-open') ? 'hidden' : '';
    }

    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('is-open');
        document.getElementById('sidebarOverlay').classList.remove('is-open');
        document.body.style.overflow = '';
    }

    window.addEventListener('resize', () => {
        if (window.innerWidth > 1024) closeSidebar();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeSidebar();
    });

    /* ===== Logout ===== */
    document.getElementById('btnLogout').addEventListener('click', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Yakin ingin keluar?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4318FF',
            cancelButtonColor: '#707EAE',
            confirmButtonText: 'Keluar Sekarang',
            cancelButtonText: 'Tetap Disini',
            reverseButtons: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'logout.php';
            }
        });
    });
</script>

</body>
</html>