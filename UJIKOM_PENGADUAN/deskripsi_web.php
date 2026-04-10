<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Minecloud | Sistem Aspirasi Siswa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4318FF;
            --primary-hover: #3311CC;
            --primary-light: rgba(67, 24, 255, 0.1);
            --bg-body: #F4F7FE;
            --text-main: #1B2559;
            --secondary: #A3AED0;
            --white: #FFFFFF;
            --shadow: 0px 40px 58px -20px rgba(112, 144, 176, 0.2);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            overflow-x: hidden;
        }

        /* --- Custom Background Ornaments --- */
        .bg-glow {
            position: absolute;
            top: -100px;
            right: -100px;
            width: 400px;
            height: 400px;
            background: var(--primary);
            filter: blur(150px);
            opacity: 0.1;
            z-index: -1;
        }

        /* --- Navbar --- */
        .navbar {
            padding: 20px 8%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            background: rgba(244, 247, 254, 0.8);
            backdrop-filter: blur(10px);
            z-index: 1000;
        }

        .brand {
            font-size: 22px; /* Dikecilkan sedikit untuk mobile-first */
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 12px;
            letter-spacing: -1px;
        }
        .brand i { 
            color: var(--white);
            background: var(--primary);
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 16px;
        }
        .brand span { color: var(--primary); }

        .btn-login {
            background: var(--primary);
            color: white;
            padding: 12px 24px;
            border-radius: 14px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0px 10px 20px rgba(67, 24, 255, 0.25);
            white-space: nowrap; /* Mencegah teks turun ke bawah */
        }

        .btn-login:hover {
            transform: translateY(-5px);
            box-shadow: 0px 20px 30px rgba(67, 24, 255, 0.35);
            background: var(--primary-hover);
        }

        /* --- Hero Section --- */
        .hero {
            display: flex;
            align-items: center;
            padding: 60px 8% 100px 8%;
            min-height: 85vh;
            gap: 50px;
        }

        .hero-content { flex: 1.2; }
        
        .hero-tag {
            background: var(--primary-light);
            color: var(--primary);
            font-weight: 700;
            font-size: 13px;
            padding: 8px 20px;
            border-radius: 100px;
            display: inline-block;
            margin-bottom: 25px;
        }

        .hero-title {
            font-size: clamp(32px, 5vw, 64px); /* Ukuran font adaptif */
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 30px;
            letter-spacing: -2px;
        }

        .hero-title span { 
            color: var(--primary);
            position: relative;
        }

        .hero-desc {
            font-size: 18px;
            color: var(--secondary);
            margin-bottom: 45px;
            max-width: 580px;
            line-height: 1.8;
        }

        .hero-image {
            flex: 0.8;
            position: relative;
            display: flex;
            justify-content: center;
        }

        .floating-icon {
            font-size: 250px;
            color: var(--primary);
            opacity: 0.15;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }

        /* --- Features Section --- */
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            padding: 100px 8%;
            background: var(--white);
            border-radius: 60px 60px 0 0;
            box-shadow: 0px -20px 40px rgba(112, 144, 176, 0.05);
        }

        .feat-card {
            padding: 40px;
            border-radius: 32px;
            background: var(--bg-body);
            transition: all 0.4s ease;
            border: 1px solid transparent;
        }

        .feat-card:hover {
            transform: translateY(-15px);
            background: var(--white);
            border-color: var(--primary-light);
            box-shadow: var(--shadow);
        }

        .feat-icon {
            width: 60px;
            height: 60px;
            background: var(--primary);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: var(--white);
            margin-bottom: 25px;
            box-shadow: 0 15px 30px rgba(67, 24, 255, 0.2);
        }

        .feat-card h3 { 
            margin-bottom: 15px; 
            font-weight: 800; 
            font-size: 20px;
            letter-spacing: -0.5px;
        }
        
        .feat-card p { 
            color: var(--secondary); 
            font-size: 15px; 
            line-height: 1.7;
        }

        /* --- Footer --- */
        footer {
            text-align: center;
            padding: 40px 20px;
            background: var(--white);
            color: var(--secondary);
            font-size: 14px;
            font-weight: 500;
        }

        /* --- Responsive BREAKPOINTS --- */
        
        /* Tablet & Small Desktop */
        @media (max-width: 992px) {
            .navbar { padding: 20px 5%; }
            .hero { padding: 40px 5% 80px 5%; gap: 30px; }
            .floating-icon { font-size: 180px; }
        }

        /* Mobile (Android/iOS) */
        @media (max-width: 768px) {
            .navbar { padding: 15px 5%; }
            .brand { font-size: 18px; }
            .brand i { width: 30px; height: 30px; font-size: 14px; }
            .btn-login { padding: 10px 18px; font-size: 13px; border-radius: 10px; }
            
            .hero { 
                flex-direction: column; 
                text-align: center; 
                padding-top: 60px;
                min-height: auto;
            }
            .hero-content { order: 2; }
            .hero-image { 
                order: 1; 
                display: flex; 
                margin-bottom: 20px;
            }
            .floating-icon { font-size: 120px; opacity: 0.1; }
            
            .hero-desc { margin: 0 auto 35px; font-size: 16px; }
            .hero-title { letter-spacing: -1px; }

            .hero-btn-container {
                justify-content: center !important;
            }

            .features { 
                padding: 60px 5%; 
                border-radius: 40px 40px 0 0; 
                margin-top: -20px;
            }
            .feat-card { padding: 30px; }
        }

        /* Extra Small Mobile */
        @media (max-width: 480px) {
            .brand span { display: none; } /* Sembunyikan kata "Siswa" jika layar terlalu sempit */
            .hero-tag { font-size: 11px; }
        }
    </style>
</head>
<body>
    <div class="bg-glow"></div>

    <nav class="navbar">
        <div class="brand">
            <i class="fa-solid fa-rocket"></i> Aspirasi<span>Siswa</span>
        </div>
        <a href="index.php" class="btn-login">Masuk</a>
    </nav>

    <header class="hero">
        <div class="hero-content">
            <span class="hero-tag">✨ Sistem Aspirasi Siswa v1.0</span>
            <h1 class="hero-title">Suarakan <span>Aspirasi</span> Untuk Sekolah Lebih Baik.</h1>
            <p class="hero-desc">
                Aspirasi Siswa memberikan platform bagi siswa untuk melaporkan keluhan, memberikan saran, dan memantau status tindak lanjut secara transparan.
            </p>
            <div class="hero-btn-container" style="display: flex; gap: 20px; align-items: center;">
                <a href="index.php" class="btn-login" style="padding: 18px 35px; font-size: 16px;">Mulai Sekarang</a>
            </div>
        </div>
        <div class="hero-image">
             <i class="fa-solid fa-comments floating-icon"></i>
        </div>
    </header>

    <section class="features">
        <div class="feat-card">
            <div class="feat-icon"><i class="fa-solid fa-paper-plane"></i></div>
            <h3>Kirim Laporan</h3>
            <p>Sampaikan aspirasi atau keluhan fasilitas sekolah dengan mudah melalui formulir digital yang user-friendly.</p>
        </div>
        <div class="feat-card">
            <div class="feat-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
            <h3>Pantau Status</h3>
            <p>Dapatkan update real-time mengenai laporanmu, mulai dari proses verifikasi hingga selesai ditangani.</p>
        </div>
        <div class="feat-card">
            <div class="feat-icon"><i class="fa-solid fa-shield-halved"></i></div>
            <h3>Keamanan Data</h3>
            <p>Privasi dan identitas siswa terlindungi sepenuhnya dengan sistem enkripsi data yang aman dan terpercaya.</p>
        </div>
    </section>

    <footer>
        <p>&copy; 2026 UKK RPL • Fahri . Seluruh Hak Dilindungi.</p>
    </footer>

</body>
</html>