<?php
session_start();
require 'functions.php';

// Proteksi Halaman
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Ambil ID dari URL
if (!isset($_GET["id"])) {
    header("Location: admin.php");
    exit;
}

$id = $_GET["id"];

// Ambil data laporan detail
$result = query("SELECT aspirasi.*, siswa.nama, kategori.nama_kategori 
                  FROM aspirasi 
                  JOIN siswa ON aspirasi.nis = siswa.nis 
                  JOIN kategori ON aspirasi.id_kategori = kategori.id_kategori 
                  WHERE id_aspirasi = $id");

if (!$result) {
    header("Location: admin.php");
    exit;
}
$laporan = $result[0]; 

if (isset($_POST["update"])) {
    $status_baru = $_POST["status"];
    $feedback_baru = htmlspecialchars($_POST["feedback"]);
    
    $query = "UPDATE aspirasi SET 
              status = '$status_baru', 
              feedback = '$feedback_baru' 
              WHERE id_aspirasi = $id";
              
    mysqli_query($conn, $query);

    if (mysqli_affected_rows($conn) > 0) {
        $_SESSION['swal_success'] = "Laporan Berhasil Ditanggapi!";
        header("Location: admin.php");
        exit;
    } else {
        $_SESSION['swal_error'] = "Gagal update atau tidak ada perubahan data";
        header("Location: admin.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elite Detail - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root {
            --primary: #4318FF;
            --primary-light: #F4F7FE;
            --bg-body: #F4F7FE;
            --white: #FFFFFF;
            --text-main: #1B2559;
            --text-muted: #A3AED0;
            --grad: linear-gradient(135deg, #4318FF 0%, #b45fff 100%);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg-body); 
            color: var(--text-main);
            padding: 40px 20px;
        }

        .container { max-width: 900px; margin: auto; }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .btn-back {
            text-decoration: none;
            color: var(--primary);
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }
        .btn-back:hover { transform: translateX(-5px); }

        .elite-card {
            background: var(--white);
            border-radius: 30px;
            border: 1px solid #E0E5F2;
            padding: 40px;
            box-shadow: 0px 40px 58px -20px rgba(112, 144, 176, 0.12);
            margin-bottom: 30px;
        }

        .card-title {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 30px;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .card-title i { color: var(--primary); }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
            margin-bottom: 40px;
        }

        .info-item { display: flex; flex-direction: column; gap: 5px; }
        .info-label { font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }
        .info-value { font-size: 16px; font-weight: 600; color: var(--text-main); }

        .content-box {
            background: var(--primary-light);
            padding: 25px;
            border-radius: 20px;
            margin-top: 10px;
            line-height: 1.6;
            font-size: 15px;
        }

        .foto-wrapper {
            margin-top: 25px;
            border-radius: 20px;
            overflow: hidden;
            border: 4px solid var(--white);
            box-shadow: 0px 10px 30px rgba(0,0,0,0.05);
            max-width: 400px;
            margin-left: 0;
        }
        .foto-bukti { 
            width: 100%; 
            display: block; 
            object-fit: contain; 
        }

        .form-group { margin-bottom: 25px; }
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--text-main);
        }

        select, textarea {
            width: 100%;
            background: var(--primary-light);
            border: 1px solid transparent;
            padding: 15px 20px;
            border-radius: 15px;
            font-family: inherit;
            font-weight: 600;
            color: var(--text-main);
            transition: all 0.3s;
        }

        select:focus, textarea:focus {
            background: var(--white);
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 4px rgba(67, 24, 255, 0.1);
        }

        .btn-submit {
            background: var(--grad);
            color: white;
            padding: 16px 35px;
            border: none;
            border-radius: 18px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0px 20px 27px rgba(67, 24, 255, 0.2);
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0px 25px 35px rgba(67, 24, 255, 0.3);
        }

        .status-pill {
            padding: 6px 16px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
        }
        .pill-menunggu { background: #FFF9E6; color: #FFB800; }
        .pill-proses { background: #E6F0FF; color: #4318FF; }
        .pill-selesai { background: #E6FFF1; color: #05CD99; }

        @media (max-width: 600px) {
            .info-grid { grid-template-columns: 1fr; }
            .elite-card { padding: 25px; }
            .foto-wrapper { max-width: 100%; }
        }
    </style>
</head>
<body>

<div class="container">
    <header class="page-header">
        <div>
            <h4 style="color: var(--text-muted); font-size: 14px;">Halaman / Laporan / Detail</h4>
            <h2 style="font-weight: 800; letter-spacing: -1px;">Proses Laporan</h2>
        </div>
        <a href="admin.php" class="btn-back">
            <i class="fa-solid fa-arrow-left"></i> Kembali Ke Dashboard
        </a>
    </header>

    <div class="elite-card">
        <h3 class="card-title"><i class="fa-solid fa-file-invoice"></i> Informasi Laporan</h3>
        
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Pelapor</span>
                <span class="info-value"><?= htmlspecialchars($laporan['nama'] ?? 'Unknown'); ?></span>
                <span style="font-size: 12px; color: var(--text-muted);">NIS: <?= $laporan['nis']; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Tanggal Masuk</span>
                <span class="info-value"><?= date('d M Y', strtotime($laporan['tanggal'])); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Kategori</span>
                <span class="info-value" style="color: var(--primary);"><?= htmlspecialchars($laporan['nama_kategori'] ?? 'Umum'); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Lokasi Kejadian</span>
                <span class="info-value"><?= htmlspecialchars($laporan['lokasi'] ?? '-'); ?></span>
            </div>
        </div>

        <div class="info-item" style="margin-bottom: 25px;">
            <span class="info-label">Isi Aspirasi</span>
            <div class="content-box">
                <?= nl2br(htmlspecialchars($laporan['keterangan'] ?? '')); ?>
            </div>
        </div>

        <div class="info-item">
            <span class="info-label">Status Saat Ini</span>
            <div style="margin-top: 5px;">
                <span class="status-pill pill-<?= strtolower($laporan['status']); ?>">
                    <i class="fa-solid fa-circle" style="font-size: 8px; margin-right: 5px;"></i>
                    <?= $laporan['status']; ?>
                </span>
            </div>
        </div>

        <?php if($laporan['foto']) : ?>
        <div class="info-item" style="margin-top: 30px;">
            <span class="info-label">Bukti Foto</span>
            <div class="foto-wrapper">
                <img src="./assets/img/<?= $laporan['foto']; ?>" class="foto-bukti" alt="Bukti Laporan">
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="elite-card">
        <h3 class="card-title"><i class="fa-solid fa-reply"></i> Ambil Tugas</h3>
        
        <form action="" method="POST" id="formTanggapan">
            <div class="form-group">
                <label for="status">Perbarui Status</label>
                <select name="status" id="status" required>
                    <option value="Menunggu" <?= $laporan['status'] == 'Menunggu' ? 'selected' : ''; ?>>🟡 Menunggu</option>
                    <option value="Proses" <?= $laporan['status'] == 'Proses' ? 'selected' : ''; ?>>🔵 Proses</option>
                    <option value="Selesai" <?= $laporan['status'] == 'Selesai' ? 'selected' : ''; ?>>🟢 Selesai</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="feedback">Feedback / Tanggapan untuk Siswa</label>
                <textarea name="feedback" id="feedback" rows="6" placeholder="Berikan penjelasan atau tanggapan resmi Anda di sini..." required><?= htmlspecialchars($laporan['feedback'] ?? ''); ?></textarea>
            </div>

            <button type="button" class="btn-submit" id="btnKirim">
                Kirim Respon Anda <i class="fa-solid fa-paper-plane"></i>
            </button>
            <input type="hidden" name="update" value="1">
        </form>
    </div>
</div>

<script>
    // Logika Pop-up Konfirmasi Modern
    document.getElementById('btnKirim').addEventListener('click', function() {
        Swal.fire({
            title: 'Kirim Tanggapan?',
            text: "Pastikan status dan feedback sudah sesuai.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4318FF',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Kirim!',
            cancelButtonText: 'Batal',
            borderRadius: '20px'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formTanggapan').submit();
            }
        });
    });
</script>

</body>
</html>