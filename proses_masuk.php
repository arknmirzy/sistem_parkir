<?php
session_start();
require_once "koneksi.php";

// Pastikan petugas sudah login
if (!isset($_SESSION['id_petugas'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plat_nomor      = strtoupper(trim($_POST['plat_nomor'] ?? ''));
    $jenis_kendaraan = trim($_POST['jenis_kendaraan'] ?? '');
    $id_petugas      = $_SESSION['id_petugas'];

    // Validasi plat nomor tidak kosong
    if (empty($plat_nomor)) {
        header("Location: index.php?status=error&msg=Plat nomor wajib diisi!");
        exit;
    }

    // Validasi jenis kendaraan
    $daftar_jenis = ['Motor', 'Mobil', 'Box/Truk Ringan'];
    if (!in_array($jenis_kendaraan, $daftar_jenis)) {
        header("Location: index.php?status=error&msg=Jenis kendaraan tidak valid!");
        exit;
    }

    // Waktu masuk otomatis zona Asia/Jakarta
    $waktu_masuk = date('Y-m-d H:i:s');
    $status_awal = 'Masuk';

    // Query berparameter INSERT
    $query = "INSERT INTO tbl_transaksi_parkir (plat_nomor, jenis_kendaraan, waktu_masuk, status, id_petugas) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ssssi", $plat_nomor, $jenis_kendaraan, $waktu_masuk, $status_awal, $id_petugas);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        header("Location: index.php?status=sukses&msg=Kendaraan " . urlencode($plat_nomor) . " berhasil Check-In pada " . $waktu_masuk);
        exit;
    } else {
        mysqli_stmt_close($stmt);
        header("Location: index.php?status=error&msg=Gagal menyimpan data kendaraan!");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
?>
