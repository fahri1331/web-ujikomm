<?php
session_start();
require 'functions.php';

// 1. PROTEKSI HALAMAN
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'siswa') {
    header("Location: index.php");
    exit;
}

$nis = $_SESSION['id'];
$status_msg = "";

// 2. LOGIKA TAMBAH
if (isset($_POST["kirim"])) {
    $isi_laporan = htmlspecialchars($_POST["isi_laporan"]);
    $id_kategori = $_POST["id_kategori"];
    $lokasi = htmlspecialchars($_POST["lokasi"]);
    $tanggal = date("Y-m-d");
    $status = "Menunggu";

    $nama_foto = $_FILES['foto']['name'];
    $sumber_foto = $_FILES['foto']['tmp_name'];
    $folder_tujuan = './assets/img/';
    
    if($nama_foto != "") {
        $nama_foto = time() . "_" . $nama_foto;
        if (!is_dir($folder_tujuan)) mkdir($folder_tujuan, 0777, true);
        move_uploaded_file($sumber_foto, $folder_tujuan . $nama_foto);
    } else { $nama_foto = ""; }

    $query = "INSERT INTO aspirasi (nis, id_kategori, lokasi, keterangan, foto, tanggal, status, feedback) 
              VALUES ('$nis', '$id_kategori', '$lokasi', '$isi_laporan', '$nama_foto', '$tanggal', '$status', '')";
    
    if(mysqli_query($conn, $query)) {
        $status_msg = "berhasil_tambah";
    }
}

// 3. LOGIKA UPDATE
if (isset($_POST["update"])) {
    $id = $_POST["id"];
    $isi_laporan = htmlspecialchars($_POST["isi_laporan"]);
    $id_kategori = $_POST["id_kategori"];
    $lokasi = htmlspecialchars($_POST["lokasi"]);
    $foto_lama = $_POST["foto_lama"];

    if ($_FILES['foto']['error'] === 4) {
        $foto = $foto_lama;
    } else {
        $nama_foto = $_FILES['foto']['name'];
        $sumber_foto = $_FILES['foto']['tmp_name'];
        $foto = time() . "_" . $nama_foto;
        move_uploaded_file($sumber_foto, './assets/img/' . $foto);
        if ($foto_lama != "" && file_exists("./assets/img/" . $foto_lama)) {
            unlink("./assets/img/" . $foto_lama);
        }
    }

    $query = "UPDATE aspirasi SET 
                id_kategori = '$id_kategori', 
                lokasi = '$lokasi', 
                keterangan = '$isi_laporan', 
                foto = '$foto' 
              WHERE id_aspirasi = '$id'";

    if(mysqli_query($conn, $query)) {
        $status_msg = "berhasil_update";
    }
}

// 4. LOGIKA HAPUS
if (isset($_GET["hapus"])) {
    $id_hapus = $_GET["hapus"];
    
    $data_lama = mysqli_query($conn, "SELECT foto FROM aspirasi WHERE id_aspirasi = '$id_hapus'");
    $row_foto = mysqli_fetch_assoc($data_lama);
    if ($row_foto['foto'] != "" && file_exists("./assets/img/" . $row_foto['foto'])) {
        unlink("./assets/img/" . $row_foto['foto']);
    }

    if(mysqli_query($conn, "DELETE FROM aspirasi WHERE id_aspirasi = '$id_hapus'")) {
        $status_msg = "berhasil_hapus";
    }
}

// 5. AMBIL DATA
$kategori_list = mysqli_query($conn, "SELECT * FROM kategori");
$riwayat = mysqli_query($conn, "SELECT aspirasi.*, kategori.nama_kategori 
                  FROM aspirasi 
                  JOIN kategori ON aspirasi.id_kategori = kategori.id_kategori
                  WHERE nis = '$nis' ORDER BY tanggal DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa | Pengaduan Sekolah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 1100;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

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

        .nav-item:hover:not(.active) {
            background: var(--primary-light);
            color: var(--primary);
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 40px 50px;
            width: calc(100% - var(--sidebar-width));
            transition: margin-left 0.3s ease, width 0.3s ease;
        }

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
            gap: 12px;
            background: var(--white);
            padding: 8px 10px 8px 18px;
            border-radius: 50px;
            box-shadow: var(--shadow-sm);
            flex-shrink: 0;
        }
        .user-name { font-size: 14px; font-weight: 700; color: var(--text-main); }
        .user-avatar {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, var(--primary), #6d4aff);
            color: white;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 14px;
            flex-shrink: 0;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 24px;
            align-items: start;
        }

        .card {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 28px 28px 32px 28px;
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(255,255,255,0.8);
        }

        .card-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-container { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        table { width: 100%; border-collapse: collapse; min-width: 550px; }

        th {
            text-align: left;
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 12px 10px;
            border-bottom: 1px solid #F4F7FE;
            white-space: nowrap;
        }

        td {
            padding: 20px 10px;
            font-size: 14px;
            color: var(--text-main);
            border-bottom: 1px solid #F4F7FE;
            vertical-align: middle;
        }

        .loc-text { font-weight: 700; color: var(--text-main); display: block; margin-bottom: 2px; white-space: nowrap; }
        .desc-preview { font-size: 13px; color: var(--secondary); display: block; max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

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

        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 8px;
            margin-left: 4px;
        }
        input:not([type="file"]), textarea, select {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid #E0E5F2;
            border-radius: var(--radius-md);
            font-family: inherit;
            font-size: 14px;
            color: var(--text-main);
            transition: all 0.2s ease;
            background: #fdfdfd;
        }
        input:not([type="file"]):focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(67, 24, 255, 0.08);
            background: white;
        }

        /* ===== CUSTOM FILE INPUT (MODERN) ===== */
        .file-upload-wrapper {
            position: relative;
            width: 100%;
        }

        .file-upload-input {
            width: 100%;
            height: 55px;
            opacity: 0;
            position: absolute;
            left: 0; top: 0;
            cursor: pointer;
            z-index: 2;
        }

        .file-upload-design {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 18px;
            height: 55px;
            background: #F4F7FE;
            border: 2px dashed #D0D7EE;
            border-radius: var(--radius-md);
            color: var(--secondary);
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .file-upload-design i {
            font-size: 18px;
            color: var(--primary);
        }

        .file-upload-button {
            background: var(--white);
            color: var(--primary);
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            border: 1px solid #E0E5F2;
            margin-left: auto;
        }

        .file-upload-input:hover + .file-upload-design {
            border-color: var(--primary);
            background: #f0f3ff;
        }

        .file-upload-input:focus + .file-upload-design {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(67, 24, 255, 0.08);
        }

        .file-upload-wrapper .file-name-display {
            display: block;
            margin-top: 8px;
            font-size: 12px;
            font-weight: 600;
            color: var(--primary);
            padding-left: 4px;
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-family: inherit;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0px 10px 20px rgba(67, 24, 255, 0.2);
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0px 14px 24px rgba(67, 24, 255, 0.3);
        }

        .action-btns { display: flex; gap: 8px; }
        .btn-icon {
            width: 36px; height: 36px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        .btn-edit { background: #F4F7FE; color: var(--primary); }
        .btn-edit:hover { background: var(--primary); color: white; }
        .btn-delete { background: #FFF5F5; color: #EE5D50; }
        .btn-delete:hover { background: #EE5D50; color: white; }

        .thumb-img {
            width: 48px; height: 48px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #F4F7FE;
        }
        .no-img {
            width: 48px; height: 48px;
            background: #F4F7FE;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: var(--text-muted);
            font-size: 18px;
        }

        .swal2-popup {
            border-radius: var(--radius-lg) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }
        .swal2-styled.swal2-confirm {
            background-color: var(--primary) !important;
            border-radius: var(--radius-md) !important;
            padding: 12px 30px !important;
        }

        @media (max-width: 1200px) {
            .dashboard-grid { grid-template-columns: 1fr; }
            #form-container { order: -1; }
        }

        @media (max-width: 1024px) {
            .mobile-header { display: flex; }
            .sidebar {
                transform: translateX(-100%);
                padding-top: 80px;
            }
            .sidebar.is-open { transform: translateX(0); box-shadow: 10px 0 40px rgba(0,0,0,0.15); }
            .sidebar-overlay.is-open { display: block; opacity: 1; }
            .nav-item.active::after { display: none; }
            .main-content {
                margin-left: 0; width: 100%;
                padding: 90px 24px 40px 24px;
            }
        }

        @media (max-width: 768px) {
            .main-content { padding: 85px 16px 40px 16px; }
            .page-title { font-size: 26px; }
            .card { padding: 20px; border-radius: 16px; }
            #form-container { order: 0; }
        }
    </style>
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<div class="mobile-header">
    <div class="brand" style="margin-bottom: 0; font-size: 18px; padding: 0;">
        <i class="fa-solid fa-rocket" style="font-size: 20px;"></i>
        <span>Dashboard Siswa</span>
    </div>
    <button class="hamburger-btn" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i>
    </button>
</div>

<aside class="sidebar" id="sidebar">
    <div class="brand">
        <i class="fa-solid fa-rocket"></i>
        <span>Dashboard Siswa</span>
    </div>
    <nav class="nav-menu">
        <a href="siswa.php" class="nav-item active">
            <i class="fa-solid fa-house"></i> Dashboard
        </a>
        <a href="laporan_saya.php" class="nav-item">
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
        <a href="#" class="nav-item" style="color: #EE5D50;" onclick="confirmLogout()">
            <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar
        </a>
    </div>
</aside>

<main class="main-content">
    <header class="header">
        <div>
            <p class="breadcrumb">Utama / Dashboard Siswa</p>
            <h1 class="page-title">Ringkasan Laporan</h1>
        </div>
        <div class="user-pill">
            <span class="user-name"><?= $_SESSION['nama']; ?></span>
            <div class="user-avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 1)); ?></div>
        </div>
    </header>

    <div class="dashboard-grid">

        <section class="card">
            <div class="card-title">
                Riwayat Aspirasi
                <button style="background: none; border: none; color: var(--secondary);">
                    <i class="fa-solid fa-sliders"></i>
                </button>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Informasi</th>
                            <th>Kategori</th>
                            <th>Bukti</th>
                            <th>Status</th>
                            <th style="text-align: center;">Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($riwayat) > 0) : ?>
                            <?php while($row = mysqli_fetch_assoc($riwayat)) : ?>
                            <tr>
                                <td>
                                    <span class="loc-text"><?= $row['lokasi']; ?></span>
                                    <span class="desc-preview"><?= $row['keterangan']; ?></span>
                                    <small style="color: var(--text-muted); font-size: 11px;"><?= date('d M Y', strtotime($row['tanggal'])); ?></small>
                                </td>
                                <td style="font-weight: 600; color: var(--secondary);"><?= $row['nama_kategori']; ?></td>
                                <td>
                                    <?php if($row['foto']): ?>
                                        <img src="./assets/img/<?= $row['foto']; ?>" class="thumb-img">
                                    <?php else: ?>
                                        <div class="no-img"><i class="fa-solid fa-image"></i></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge status-<?= strtolower($row['status']); ?>">
                                        <div class="dot"></div>
                                        <?= $row['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns" style="justify-content: center;">
                                        <?php if($row['status'] == 'Menunggu'): ?>
                                            <button onclick="prepareEdit('<?= $row['id_aspirasi'] ?>','<?= $row['id_kategori'] ?>','<?= $row['lokasi'] ?>','<?= addslashes($row['keterangan']) ?>','<?= $row['foto'] ?>')" class="btn-icon btn-edit" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <button onclick="confirmDelete('<?= $row['id_aspirasi']; ?>')" class="btn-icon btn-delete" title="Hapus">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        <?php else: ?>
                                            <div style="background: #F4F7FE; padding: 6px 12px; border-radius: 8px; font-size: 11px; color: var(--secondary); font-weight: 700;">LOCKED</div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-muted);">Belum ada laporan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card" id="form-container">
            <h3 class="card-title" id="form-title">Buat Laporan Baru</h3>
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit-id">
                <input type="hidden" name="foto_lama" id="edit-foto-lama">

                <div class="form-group">
                    <label>Pilih Kategori</label>
                    <select name="id_kategori" id="edit-kategori" required>
                        <option value="" disabled selected>Pilih kategori masalah...</option>
                        <?php mysqli_data_seek($kategori_list, 0); ?>
                        <?php while($row_kat = mysqli_fetch_assoc($kategori_list)) : ?>
                            <option value="<?= $row_kat['id_kategori']; ?>"><?= $row_kat['nama_kategori']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Lokasi Kejadian</label>
                    <input type="text" name="lokasi" id="edit-lokasi" placeholder="Misal: Kantin, Kelas 10A..." required>
                </div>

                <div class="form-group">
                    <label>Deskripsi Laporan</label>
                    <textarea name="isi_laporan" id="edit-isi" rows="5" placeholder="Tuliskan secara detail apa yang terjadi..." required></textarea>
                </div>

                <div class="form-group">
                    <label>Lampiran Foto (Optional)</label>
                    <div class="file-upload-wrapper">
                        <input type="file" name="foto" id="file-input" class="file-upload-input" onchange="updateFileName(this)">
                        <div class="file-upload-design">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <span id="file-text">Pilih atau Seret Foto</span>
                            <span class="file-upload-button">Browse</span>
                        </div>
                        <span id="file-name-display" class="file-name-display"></span>
                    </div>
                    <p style="font-size: 11px; color: var(--text-muted); margin-top: 8px;">Format: JPG, PNG. Max 2MB.</p>
                </div>

                <button type="submit" name="kirim" id="btn-submit" class="btn-submit">
                    <i class="fa-solid fa-paper-plane" style="margin-right: 8px;"></i> Kirim Aspirasi
                </button>
                
                <button type="button" onclick="cancelEdit()" id="btn-cancel" class="btn-submit" style="display:none; background: #8F9BBA; margin-top: 12px; box-shadow: none;">
                    Batalkan Edit
                </button>
            </form>
        </section>

    </div>
</main>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.toggle('is-open');
        overlay.classList.toggle('is-open');
    }

    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('is-open');
        document.getElementById('sidebarOverlay').classList.remove('is-open');
    }

    // Update Label Nama File Saat Dipilih
    function updateFileName(input) {
        const display = document.getElementById('file-name-display');
        const fileText = document.getElementById('file-text');
        if (input.files && input.files[0]) {
            display.innerHTML = "<i class='fa-solid fa-check-circle'></i> " + input.files[0].name;
            fileText.innerText = "Foto terpilih";
        } else {
            display.innerText = "";
            fileText.innerText = "Pilih atau Seret Foto";
        }
    }

    <?php if($status_msg == "berhasil_tambah"): ?>
        Swal.fire({ icon: 'success', title: 'Berhasil Terkirim!', text: 'Laporan aspirasi kamu sudah kami terima.', confirmButtonColor: '#4318FF' }).then(() => { window.location='siswa.php'; });
    <?php elseif($status_msg == "berhasil_update"): ?>
        Swal.fire({ icon: 'success', title: 'Berhasil Diperbarui!', text: 'Perubahan laporan telah disimpan.', confirmButtonColor: '#4318FF' }).then(() => { window.location='siswa.php'; });
    <?php elseif($status_msg == "berhasil_hapus"): ?>
        Swal.fire({ icon: 'success', title: 'Dihapus!', text: 'Laporan berhasil dihapus.', confirmButtonColor: '#4318FF' }).then(() => { window.location='siswa.php'; });
    <?php endif; ?>

    function confirmDelete(id) {
        Swal.fire({ title: 'Hapus Laporan?', text: "Data tidak bisa dikembalikan!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#EE5D50', cancelButtonColor: '#707EAE', confirmButtonText: 'Ya, Hapus!' }).then((result) => {
            if (result.isConfirmed) { window.location.href = "?hapus=" + id; }
        });
    }

    function confirmLogout() {
        Swal.fire({ title: 'Yakin ingin keluar?', icon: 'question', showCancelButton: true, confirmButtonColor: '#4318FF', cancelButtonColor: '#707EAE', confirmButtonText: 'Keluar' }).then((result) => {
            if (result.isConfirmed) { window.location.href = 'logout.php'; }
        });
    }

    function prepareEdit(id, kat, lok, isi, foto) {
        const formContainer = document.getElementById('form-container');
        formContainer.style.border = '2px solid var(--primary)';
        document.getElementById('form-title').innerHTML = "Edit Laporan <small style='font-weight:400; font-size:12px; color:var(--primary)'>(Mode Edit)</small>";
        document.getElementById('edit-id').value = id;
        document.getElementById('edit-kategori').value = kat;
        document.getElementById('edit-lokasi').value = lok;
        document.getElementById('edit-isi').value = isi;
        document.getElementById('edit-foto-lama').value = foto;
        document.getElementById('btn-submit').name = "update";
        document.getElementById('btn-submit').innerHTML = "<i class='fa-solid fa-check'></i> Simpan Perubahan";
        document.getElementById('btn-cancel').style.display = "block";
        formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function cancelEdit() { window.location = 'siswa.php'; }
</script>
</body>
</html>