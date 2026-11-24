<?php
session_start();
require_once "koneksi.php";

// Jika sudah login, langsung lempar ke halaman utama
if (isset($_SESSION['id_petugas'])) {
    header("Location: index.php");
    exit;
}

$error = "";

// Cek apakah form login disubmit
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Username dan password wajib diisi!";
    } else {
        // Query berparameter untuk keamanan
        $query = "SELECT id_petugas, nama_petugas, username, password FROM tbl_petugas WHERE username = ?";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            // Verifikasi password hash
            if (password_verify($password, $row['password'])) {
                // Simpan ke session
                $_SESSION['id_petugas']   = $row['id_petugas'];
                $_SESSION['nama_petugas'] = $row['nama_petugas'];
                $_SESSION['username']     = $row['username'];

                header("Location: index.php");
                exit;
            } else {
                $error = "Password yang Anda masukkan salah!";
            }
        } else {
            $error = "Username tidak terdaftar!";
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Petugas - Nusantara Mall Parking</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="login-box">
    <h3 style="text-align: center;">LOGIN PETUGAS</h3>
    <p style="text-align: center; font-size: 12px; color: #555;">Smart Parking System - Nusantara Mall</p>
    <hr>

    <?php if (!empty($error)): ?>
        <div class="pesan-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label for="username">Username:</label><br>
            <input type="text" name="username" id="username" style="width: 95%;" required autofocus>
        </div>
        <div class="form-group">
            <label for="password">Password:</label><br>
            <input type="password" name="password" id="password" style="width: 95%;" required>
        </div>
        <div style="margin-top: 15px;">
            <input type="submit" name="login" value="Masuk" class="btn" style="width: 100%; background-color: #0f4c81; color: white;">
        </div>
    </form>
    
    <div style="margin-top: 15px; font-size: 11px; color: #777; text-align: center;">
        Default akun: <b>petugas</b> / <b>123456</b>
    </div>
</div>

</body>
</html>
