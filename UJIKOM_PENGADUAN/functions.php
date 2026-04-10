<?php
$conn = mysqli_connect("localhost", "root", "", "db_pengaduan_sekolah1");

function query($query) {
    global $conn;
    $result = mysqli_query($conn, $query);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) { 
        $rows[] = $row;
    }
    return $rows;
}

function tambahAspirasi($data) {
    global $conn;
    $nis = htmlspecialchars($data["nis"]);
    $lokasi = htmlspecialchars($data["lokasi"]);
    
    $query = "INSERT INTO aspirasi VALUES ...";
    mysqli_query($conn, $query);

    return mysqli_affected_rows($conn);
}

// 🔥 LOGIN DENGAN ROLE
function cek_login($username, $password, $role) {
    global $conn;

    // LOGIN ADMIN
    if ($role == 'admin') {
        $q_admin = mysqli_query($conn, "SELECT * FROM admin WHERE username='$username' AND password='$password'");
        
        if (mysqli_num_rows($q_admin) > 0) {
            $data = mysqli_fetch_assoc($q_admin);

            $_SESSION['login'] = true;
            $_SESSION['role'] = 'admin';
            $_SESSION['nama'] = $data['nama_petugas'];
            $_SESSION['id'] = $data['id_admin'];

            return "admin";
        }
    }

    // LOGIN SISWA
    if ($role == 'siswa') {
        $q_siswa = mysqli_query($conn, "SELECT * FROM siswa WHERE nis='$username' AND password='$password'");
        
        if (mysqli_num_rows($q_siswa) > 0) {
            $data = mysqli_fetch_assoc($q_siswa);

            $_SESSION['login'] = true;
            $_SESSION['role'] = 'siswa';
            $_SESSION['nama'] = $data['nama'];
            $_SESSION['id'] = $data['nis'];

            return "siswa";
        }
    }

    return false;
}
?>