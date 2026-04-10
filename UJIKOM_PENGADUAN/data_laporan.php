<?php
session_start();
require 'functions.php';

// Proteksi Halaman
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Ambil semua data aspirasi lengkap
$query_laporan = "SELECT aspirasi.*, siswa.nama, kategori.nama_kategori 
                  FROM aspirasi 
                  JOIN siswa ON aspirasi.nis = siswa.nis 
                  JOIN kategori ON aspirasi.id_kategori = kategori.id_kategori 
                  ORDER BY aspirasi.tanggal DESC";

$laporan = query($query_laporan) ?: [];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Laporan - Elite Admin</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary: #4318FF;
            --primary-light: #F4F7FE;
            --secondary: #6AD2FF;
            --bg-body: #F4F7FE;
            --white: #FFFFFF;
            --text-main: #1B2559;
            --text-muted: #A3AED0;
            --success: #05CD99;
            --warning: #FFB800;
            --danger: #FF5B5B;
            --grad: linear-gradient(135deg, #4318FF 0%, #b45fff 100%);
            --sidebar-width: 290px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg-body); 
            color: var(--text-main);
            overflow-x: hidden;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--white);
            position: fixed;
            top: 0;
            left: 0;
            padding: 40px 30px;
            border-right: 1px solid #E0E5F2;
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
        }

        .hamburger-btn {
            background: var(--primary-light);
            border: none;
            width: 40px; height: 40px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            color: var(--primary);
            font-size: 18px;
            transition: background 0.2s;
        }
        .hamburger-btn:hover { background: var(--primary); color: white; }

        /* ===== MAIN CONTENT ===== */
        .main-content { 
            margin-left: var(--sidebar-width); 
            padding: 40px; 
            animation: fadeIn 0.8s ease-out;
            transition: margin-left 0.3s ease;
            min-height: 100vh;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ===== BRAND ===== */
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 22px;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 50px;
            letter-spacing: -1px;
        }

        .brand i { color: var(--primary); font-size: 28px; }

        /* ===== NAV ===== */
        .nav-menu { list-style: none; }
        .nav-item {
            display: flex;
            align-items: center;
            padding: 16px 20px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            border-radius: 15px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 8px;
            cursor: pointer;
        }

        .nav-item i { font-size: 20px; width: 30px; }
        .nav-item:hover { color: var(--primary); background: var(--primary-light); transform: translateX(5px); }
        
        .nav-item.active {
            background: var(--grad);
            color: var(--white);
            box-shadow: 0px 20px 27px rgba(67, 24, 255, 0.2);
        }

        /* ===== TOPBAR ===== */
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(10px);
            padding: 15px 25px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            flex-wrap: wrap;
            gap: 15px;
        }

        .user-profile { display: flex; align-items: center; gap: 15px; }
        .avatar { 
            width: 45px; height: 45px; 
            background: var(--grad); 
            border-radius: 12px; 
            display: flex; align-items: center; justify-content: center; 
            color: white; font-weight: bold;
            flex-shrink: 0;
        }

        /* ===== TABLE CONTAINER ===== */
        .table-container { 
            background: var(--white); 
            border-radius: 30px; 
            padding: 30px; 
            border: 1px solid #E0E5F2;
            box-shadow: 0px 40px 58px -20px rgba(112, 144, 176, 0.12);
        }

        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            width: 100%;
        }
        
        table { width: 100%; border-collapse: separate; border-spacing: 0 15px; min-width: 650px; }
        th { color: var(--text-muted); font-size: 13px; font-weight: 700; text-transform: uppercase; text-align: left; padding: 0 15px; white-space: nowrap; }
        
        tr td { background: white; padding: 20px 15px; transition: all 0.3s; border-top: 1px solid #f6f6f6; border-bottom: 1px solid #f6f6f6; }
        tr td:first-child { border-radius: 20px 0 0 20px; border-left: 1px solid #f6f6f6; }
        tr td:last-child { border-radius: 0 20px 20px 0; border-right: 1px solid #f6f6f6; }
        
        tr:hover td { background: #F9FAFF; }

        /* ===== STATUS PILL ===== */
        .status-pill {
            padding: 8px 16px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }
        .status-pill::before { content: ''; width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
        .pill-menunggu { background: #FFF9E6; color: #FFB800; }
        .pill-menunggu::before { background: #FFB800; }
        .pill-proses { background: #E6F0FF; color: #4318FF; }
        .pill-proses::before { background: #4318FF; }
        .pill-selesai { background: #E6FFF1; color: #05CD99; }
        .pill-selesai::before { background: #05CD99; }

        /* ===== BTN DETAIL ===== */
        .btn-detail {
            background: var(--primary-light);
            color: var(--primary);
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 13px;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }
        .btn-detail:hover { background: var(--primary); color: white; transform: scale(1.05); }

        /* ===== SWEETALERT ===== */
        .swal2-popup {
            border-radius: 25px !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }
        .swal2-styled.swal2-confirm {
            background: var(--grad) !important;
            border-radius: 12px !important;
        }
        .swal2-styled.swal2-cancel {
            border-radius: 12px !important;
        }

        /* ===== RESPONSIVE ===== */

        /* Tablet: <= 1024px — Sidebar jadi drawer */
        @media (max-width: 1024px) {
            .mobile-header { display: flex; }

            .sidebar {
                transform: translateX(-100%);
                padding-top: 80px;
                box-shadow: none;
            }

            .sidebar.is-open {
                transform: translateX(0);
                box-shadow: 10px 0 40px rgba(0,0,0,0.15);
            }

            .sidebar-overlay.is-open {
                display: block;
                opacity: 1;
            }

            .main-content {
                margin-left: 0;
                padding: 90px 24px 40px 24px;
            }
        }

        /* Mobile: <= 768px */
        @media (max-width: 768px) {
            .main-content { padding: 85px 16px 40px 16px; }

            .topbar { padding: 12px 18px; border-radius: 16px; }

            .table-container { padding: 18px; border-radius: 22px; }

            .table-container > div:first-child { margin-bottom: 18px; }
        }

        /* Small Mobile: <= 480px */
        @media (max-width: 480px) {
            .topbar h2 { font-size: 18px; }
            .brand { font-size: 18px; }
            .brand i { font-size: 22px; }
        }

        /* ===== PRINT ===== */
        @media print {
            .sidebar, .topbar, .mobile-header, .sidebar-overlay { display: none !important; }
            .main-content { margin-left: 0; padding: 0; }
            .table-container { border: none; box-shadow: none; padding: 0; }
        }
    </style>
</head>
<body>

<!-- Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- Mobile Header -->
<div class="mobile-header">
    <div class="brand" style="margin-bottom: 0; font-size: 18px;">
        <i class="fa-solid fa-bolt-lightning" style="font-size: 22px;"></i>
        <span>Admin Dashboard</span>
    </div>
    <button class="hamburger-btn" onclick="toggleSidebar()" aria-label="Toggle Menu">
        <i class="fa-solid fa-bars"></i>
    </button>
</div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="brand">
        <i class="fa-solid fa-bolt-lightning"></i>
        <span>Dashboard Admin</span>
    </div>
    <ul class="nav-menu">
        <li><a href="admin.php" class="nav-item"><i class="fa-solid fa-chart-pie"></i> <span>Dashboard</span></a></li>
        <li><a href="data_laporan.php" class="nav-item active"><i class="fa-solid fa-folder-open"></i> <span>Data Laporan</span></a></li>
        <li><a href="daftar_siswa.php" class="nav-item"><i class="fa-solid fa-user-gear"></i> <span>Data Siswa</span></a></li>
        <li>
            <a href="javascript:void(0)" onclick="konfirmasiKeluar()" class="nav-item" style="margin-top: 50px; color: var(--danger);">
                <i class="fa-solid fa-power-off"></i> <span>Keluar</span>
            </a>
        </li>
    </ul>
</aside>

<!-- Main Content -->
<main class="main-content">
    <header class="topbar">
        <div>
            <h4 style="color: var(--text-muted); font-size: 14px;">Halaman / Data Laporan</h4>
            <h2 style="font-weight: 800; letter-spacing: -0.5px;">Semua Aspirasi</h2>
        </div>
        <div class="user-profile">
            <div style="text-align: right;">
                <p style="font-weight: 700; font-size: 14px;"><?= htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></p>
                <p style="font-size: 12px; color: var(--text-muted);">Administrator</p>
            </div>
            <div class="avatar"><?= substr(htmlspecialchars($_SESSION['nama'] ?? 'A'), 0, 1); ?></div>
        </div>
    </header>

    <div class="table-container">
        <div style="margin-bottom: 25px;">
            <h3 style="font-weight: 700;">Master Data Aspirasi</h3>
            <p style="color: var(--text-muted); font-size: 14px;">Manajemen seluruh laporan masuk dari siswa.</p>
        </div>

        <!-- Wrapper scroll horizontal -->
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Nama Siswa</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($laporan)) : ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 40px;">Belum ada data laporan.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($laporan as $row) : ?>
                    <tr>
                        <td style="font-weight: 700; color: var(--primary); white-space: nowrap;">#<?= $row['id_aspirasi']; ?></td>
                        <td style="font-weight: 600; color: var(--text-muted); white-space: nowrap;"><?= date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                        <td style="font-weight: 700; white-space: nowrap;"><?= htmlspecialchars($row['nama'] ?? ''); ?></td>
                        <td>
                            <span style="background: var(--primary-light); padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; color: var(--primary); white-space: nowrap; display: inline-block;">
                                <?= htmlspecialchars($row['nama_kategori'] ?? ''); ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-pill pill-<?= strtolower($row['status'] ?? 'menunggu'); ?>">
                                <?= htmlspecialchars($row['status'] ?? 'Menunggu'); ?>
                            </span>
                        </td>
                        <td>
                            <a href="proses_laporan.php?id=<?= $row['id_aspirasi']; ?>" class="btn-detail">
                                <i class="fa-solid fa-eye"></i> Periksa
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

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

    /* ===== Konfirmasi Keluar ===== */
    function konfirmasiKeluar() {
        Swal.fire({
            title: 'Yakin ingin keluar?',
            text: "Pastikan semua pekerjaan Anda telah selesai.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#4318FF',
            cancelButtonColor: '#A3AED0',
            confirmButtonText: 'Ya, Keluar!',
            cancelButtonText: 'Batal',
            showClass: { popup: 'animate__animated animate__zoomIn' },
            hideClass: { popup: 'animate__animated animate__fadeOutDown' }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Sampai Jumpa!',
                    text: 'Menghapus sesi Anda...',
                    timer: 1000,
                    showConfirmButton: false,
                    didOpen: () => { Swal.showLoading(); }
                }).then(() => {
                    window.location.href = 'logout.php';
                });
            }
        });
    }
</script>

</body>
</html>