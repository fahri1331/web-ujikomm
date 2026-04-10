<?php
session_start();
require 'functions.php';

// Proteksi Halaman
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Hitung Statistik
$total_laporan = count(query("SELECT id_aspirasi FROM aspirasi"));
$total_selesai = count(query("SELECT id_aspirasi FROM aspirasi WHERE status = 'Selesai'"));
$total_proses = count(query("SELECT id_aspirasi FROM aspirasi WHERE status = 'Proses'"));
$total_menunggu = count(query("SELECT id_aspirasi FROM aspirasi WHERE status = 'Menunggu'"));

$kategori_list = query("SELECT * FROM kategori");

$query_dasar = "SELECT aspirasi.*, siswa.nama, kategori.nama_kategori 
                FROM aspirasi 
                JOIN siswa ON aspirasi.nis = siswa.nis 
                JOIN kategori ON aspirasi.id_kategori = kategori.id_kategori 
                WHERE 1=1";

if (isset($_POST['cari'])) {
    if (!empty($_POST['id_kategori'])) {
        $id_kat = mysqli_real_escape_string($db, $_POST['id_kategori']);
        $query_dasar .= " AND aspirasi.id_kategori = '$id_kat'";
    }
    if (!empty($_POST['status'])) {
        $stat = mysqli_real_escape_string($db, $_POST['status']);
        $query_dasar .= " AND aspirasi.status = '$stat'";
    }
    if (!empty($_POST['tgl_awal']) && !empty($_POST['tgl_akhir'])) {
        $awal = $_POST['tgl_awal'];
        $akhir = $_POST['tgl_akhir'];
        // Menggunakan DATE() agar membandingkan tanggal saja tanpa jam, 
        // dan memastikan inklusif untuk rentang yang sama
        $query_dasar .= " AND DATE(aspirasi.tanggal) BETWEEN '$awal' AND '$akhir'";
    } elseif (!empty($_POST['tgl_awal'])) {
        $awal = $_POST['tgl_awal'];
        $query_dasar .= " AND DATE(aspirasi.tanggal) >= '$awal'";
    } elseif (!empty($_POST['tgl_akhir'])) {
        $akhir = $_POST['tgl_akhir'];
        $query_dasar .= " AND DATE(aspirasi.tanggal) <= '$akhir'";
    }
}

$query_dasar .= " ORDER BY aspirasi.tanggal DESC";
$laporan = query($query_dasar) ?: [];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elite Dashboard - Panel Admin</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
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

        /* ===== STATS CARDS ===== */
        .stats-container { 
            display: grid; 
            grid-template-columns: repeat(4, 1fr); 
            gap: 25px; 
            margin-bottom: 40px; 
        }
        
        .elite-card {
            background: var(--white);
            padding: 25px;
            border-radius: 25px;
            border: 1px solid #E0E5F2;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .elite-card:hover { transform: translateY(-10px); box-shadow: 0px 40px 50px -20px rgba(202, 209, 231, 0.6); }

        .card-icon {
            width: 56px; height: 56px;
            background: var(--primary-light);
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; color: var(--primary);
            margin-bottom: 20px;
        }

        .card-val { font-size: 28px; font-weight: 700; color: var(--text-main); margin-bottom: 5px; }
        .card-label { font-size: 14px; font-weight: 600; color: var(--text-muted); }

        /* ===== FILTER ===== */
        .filter-panel {
            background: var(--white);
            padding: 30px;
            border-radius: 30px;
            margin-bottom: 35px;
            border: 1px solid #E0E5F2;
        }

        .filter-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); 
            gap: 20px; 
            align-items: flex-end; 
        }
        
        .input-group { display: flex; flex-direction: column; gap: 8px; }
        .input-group label { font-size: 12px; font-weight: 700; color: var(--text-main); text-transform: uppercase; letter-spacing: 1px; }
        
        .input-group select, .input-group input {
            background: var(--primary-light);
            border: 1px solid transparent;
            padding: 12px 16px;
            border-radius: 14px;
            font-family: inherit;
            font-weight: 600;
            color: var(--text-main);
            transition: all 0.3s;
            width: 100%;
        }

        .input-group select:focus, .input-group input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .btn-premium {
            background: var(--grad);
            color: white;
            padding: 12px 30px;
            border-radius: 14px;
            border: none;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s, opacity 0.2s;
            height: 48px;
            font-family: inherit;
            font-size: 14px;
        }

        .btn-premium:hover { opacity: 0.9; transform: translateY(-1px); }

        /* ===== TABLE ===== */
        .table-container { 
            background: var(--white); 
            border-radius: 30px; 
            padding: 30px; 
            border: 1px solid #E0E5F2; 
        }

        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            width: 100%;
        }
        
        table { width: 100%; border-collapse: separate; border-spacing: 0 15px; min-width: 750px; }
        th { color: var(--text-muted); font-size: 13px; font-weight: 700; text-transform: uppercase; text-align: left; padding: 0 15px; white-space: nowrap; }
        
        tr td { background: white; padding: 18px 15px; transition: all 0.3s; }
        tr td:first-child { border-radius: 20px 0 0 20px; }
        tr td:last-child { border-radius: 0 20px 20px 0; }
        
        tr:hover td { background: #F9FAFF; }

        /* ===== STATUS ===== */
        .status-pill { padding: 8px 16px; border-radius: 12px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; }
        .status-pill::before { content: ''; width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
        .pill-menunggu { background: #FFF9E6; color: #FFB800; }
        .pill-menunggu::before { background: #FFB800; }
        .pill-proses { background: #E6F0FF; color: #4318FF; }
        .pill-proses::before { background: #4318FF; }
        .pill-selesai { background: #E6FFF1; color: #05CD99; }
        .pill-selesai::before { background: #05CD99; }

        .btn-view-icon {
            background: var(--primary-light);
            color: var(--primary);
            width: 38px; height: 38px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            text-decoration: none;
            transition: all 0.3s;
            flex-shrink: 0;
        }
        .btn-view-icon:hover { background: var(--primary); color: white; transform: scale(1.1); }

        .table-header-row {
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 25px; 
            flex-wrap: wrap; 
            gap: 15px;
        }

        .export-actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .btn-export {
            padding: 10px 18px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            display: flex; align-items: center; gap: 8px;
            border: 1px solid #E0E5F2;
            background: white;
            transition: all 0.2s;
            font-family: inherit;
        }

        .btn-export:hover { background: var(--primary-light); }

        /* ===== RESPONSIVE BREAKPOINTS ===== */
        @media (max-width: 1100px) {
            .stats-container { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 1024px) {
            .mobile-header { display: flex; }
            .sidebar { transform: translateX(-100%); padding-top: 80px; box-shadow: none; }
            .sidebar.is-open { transform: translateX(0); box-shadow: 10px 0 40px rgba(0,0,0,0.15); }
            .sidebar-overlay.is-open { display: block; opacity: 1; }
            .main-content { margin-left: 0; padding: 90px 24px 40px 24px; }
        }

        @media (max-width: 768px) {
            .main-content { padding: 85px 16px 40px 16px; }
            .topbar { padding: 12px 18px; border-radius: 16px; }
            .filter-panel { padding: 20px; border-radius: 22px; }
            .table-container { padding: 18px; border-radius: 22px; }
            .btn-export { flex: 1; justify-content: center; }
            .stats-container { gap: 15px; }
            .elite-card { padding: 20px; border-radius: 20px; }
            .card-val { font-size: 24px; }
        }

        @media (max-width: 480px) {
            .stats-container { grid-template-columns: 1fr 1fr; gap: 12px; }
            .elite-card { padding: 15px; border-radius: 18px; }
            .card-icon { width: 44px; height: 44px; font-size: 18px; border-radius: 14px; margin-bottom: 12px; }
            .card-val { font-size: 22px; }
            .card-label { font-size: 12px; }
            .filter-grid { grid-template-columns: 1fr; }
            .topbar h2 { font-size: 18px; }
            .brand { font-size: 18px; }
            .brand i { font-size: 22px; }
        }

        @media (max-width: 360px) {
            .stats-container { grid-template-columns: 1fr; }
        }

        @media print {
            .sidebar, .filter-panel, .topbar, .btn-view-icon, 
            .export-actions, .mobile-header, .sidebar-overlay { display: none !important; }
            .main-content { margin-left: 0; padding: 0; }
            .table-container { border: none; padding: 0; }
            .no-export { display: none; }
        }
    </style>
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<div class="mobile-header">
    <div class="brand" style="margin-bottom: 0; font-size: 18px;">
        <i class="fa-solid fa-bolt-lightning" style="font-size: 22px;"></i>
        <span>Admin Dashboard</span>
    </div>
    <button class="hamburger-btn" onclick="toggleSidebar()" aria-label="Toggle Menu">
        <i class="fa-solid fa-bars"></i>
    </button>
</div>

<aside class="sidebar" id="sidebar">
    <div class="brand">
        <i class="fa-solid fa-bolt-lightning"></i>
        <span>Dashboard Admin</span>
    </div>
    <ul class="nav-menu">
        <li><a href="admin.php" class="nav-item active"><i class="fa-solid fa-chart-pie"></i> <span>Dashboard</span></a></li>
        <li><a href="data_laporan.php" class="nav-item"><i class="fa-solid fa-folder-open"></i> <span>Data Laporan</span></a></li>
        <li><a href="daftar_siswa.php" class="nav-item"><i class="fa-solid fa-user-gear"></i> <span>Data Siswa</span></a></li>
        <li>
            <a href="javascript:void(0)" onclick="konfirmasiKeluar()" class="nav-item" style="margin-top: 50px; color: var(--danger);">
                <i class="fa-solid fa-power-off"></i> <span>Keluar</span>
            </a>
        </li>
    </ul>
</aside>

<main class="main-content">
    <header class="topbar">
        <div>
            <h4 style="color: var(--text-muted); font-size: 14px;">Halaman / Dashboard</h4>
            <h2 style="font-weight: 800; letter-spacing: -0.5px;">Dashboard Utama</h2>
        </div>
        <div class="user-profile">
            <div style="text-align: right;">
                <p style="font-weight: 700; font-size: 14px;"><?= htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></p>
                <p style="font-size: 12px; color: var(--text-muted);">Administrator</p>
            </div>
            <div class="avatar"><?= substr(htmlspecialchars($_SESSION['nama'] ?? 'A'), 0, 1); ?></div>
        </div>
    </header>

    <div class="stats-container">
        <div class="elite-card">
            <div class="card-icon"><i class="fa-solid fa-book"></i></div>
            <div class="card-val"><?= $total_laporan; ?></div>
            <div class="card-label">Total Aspirasi</div>
        </div>
        <div class="elite-card">
            <div class="card-icon" style="color: var(--warning); background: #FFF9E6;"><i class="fa-solid fa-envelope-open-text"></i></div>
            <div class="card-val"><?= $total_menunggu; ?></div>
            <div class="card-label">Belum Ditangani</div>
        </div>
        <div class="elite-card">
            <div class="card-icon" style="color: var(--primary); background: #E6F0FF;"><i class="fa-solid fa-clock-rotate-left"></i></div>
            <div class="card-val"><?= $total_proses; ?></div>
            <div class="card-label">Dalam Proses</div>
        </div>
        <div class="elite-card">
            <div class="card-icon" style="color: var(--success); background: #E6FFF1;"><i class="fa-solid fa-check-double"></i></div>
            <div class="card-val"><?= $total_selesai; ?></div>
            <div class="card-label">Selesai Ditangani</div>
        </div>
    </div>

    <section class="filter-panel">
        <form action="" method="POST" class="filter-grid">
            <div class="input-group">
                <label>Kategori</label>
                <select name="id_kategori">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($kategori_list as $kat) : ?>
                        <option value="<?= $kat['id_kategori']; ?>" <?= (isset($_POST['id_kategori']) && $_POST['id_kategori'] == $kat['id_kategori']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($kat['nama_kategori'] ?? ''); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group">
                <label>Status</label>
                <select name="status">
                    <option value="">Semua Status</option>
                    <option value="Menunggu" <?= (isset($_POST['status']) && $_POST['status'] == 'Menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                    <option value="Proses" <?= (isset($_POST['status']) && $_POST['status'] == 'Proses') ? 'selected' : ''; ?>>Proses</option>
                    <option value="Selesai" <?= (isset($_POST['status']) && $_POST['status'] == 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
                </select>
            </div>
            <div class="input-group">
                <label>Tanggal Awal</label>
                <input type="date" name="tgl_awal" value="<?= $_POST['tgl_awal'] ?? ''; ?>">
            </div>
            <div class="input-group">
                <label>Tanggal Akhir</label>
                <input type="date" name="tgl_akhir" value="<?= $_POST['tgl_akhir'] ?? ''; ?>">
            </div>
            <button type="submit" name="cari" class="btn-premium">
                <i class="fa-solid fa-magnifying-glass" style="margin-right: 6px;"></i> Filter
            </button>
        </form>
    </section>

    <div class="table-container">
        <div class="table-header-row">
            <h3 style="font-weight: 700;">Laporan Terbaru</h3>
            <div class="export-actions">
                <button onclick="window.print()" class="btn-export">
                    <i class="fa-solid fa-print"></i> Cetak
                </button>
                <button onclick="exportToPDF()" class="btn-export" style="background: var(--primary); color: white; border: none;">
                    <i class="fa-solid fa-file-pdf"></i> PDF
                </button>
            </div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama Siswa</th>
                        <th>Kategori</th>
                        <th>Keterangan</th>
                        <th>Status</th>
                        <th class="no-export">Aksi</th>
                    </tr>
                </thead>
                <tbody id="reportTable">
                    <?php foreach ($laporan as $row) : ?>
                    <?php 
                        $ket = $row['keterangan'] ?? ''; 
                        $safe_ket = htmlspecialchars($ket);
                    ?>
                    <tr>
                        <td style="font-weight: 600; color: var(--text-muted); white-space: nowrap;"><?= date('d M, Y', strtotime($row['tanggal'])); ?></td>
                        <td style="font-weight: 700; white-space: nowrap;"><?= htmlspecialchars($row['nama'] ?? ''); ?></td>
                        <td><span style="background: #F4F7FE; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; white-space: nowrap;"><?= htmlspecialchars($row['nama_kategori'] ?? ''); ?></span></td>
                        <td style="color: var(--text-muted); font-size: 14px; max-width: 220px;">
                            <?= (strlen($ket) > 45) ? substr($safe_ket, 0, 45) . '...' : $safe_ket; ?>
                        </td>
                        <td>
                            <span class="status-pill pill-<?= strtolower($row['status'] ?? 'menunggu'); ?>">
                                <?= htmlspecialchars($row['status'] ?? 'Menunggu'); ?>
                            </span>
                        </td>
                        <td class="no-export">
                            <a href="proses_laporan.php?id=<?= $row['id_aspirasi']; ?>" class="btn-view-icon" title="Detail Laporan">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($laporan)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">Tidak ada data laporan ditemukan.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
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

    function konfirmasiKeluar() {
        Swal.fire({
            title: 'Yakin ingin keluar?',
            text: "Sesi Anda akan diakhiri sekarang.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#4318FF',
            cancelButtonColor: '#A3AED0',
            confirmButtonText: 'Ya, Keluar!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'logout.php';
            }
        });
    }

    function exportToPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('l', 'mm', 'a4'); 
        doc.setFontSize(18);
        doc.text("Laporan Aspirasi Siswa", 14, 20);
        doc.autoTable({
            html: 'table',
            startY: 30,
            theme: 'grid',
            headStyles: { fillColor: [67, 24, 255] },
            columns: [0, 1, 2, 3, 4]
        });
        doc.save('Laporan_Aspirasi_' + new Date().getTime() + '.pdf');
    }
</script>

</body>
</html>