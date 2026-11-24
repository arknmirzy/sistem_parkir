<?php
session_start();
require_once "koneksi.php";

// Pastikan petugas sudah login
if (!isset($_SESSION['id_petugas'])) {
    header("Location: login.php");
    exit;
}

// Ambil ID parkir dari parameter GET atau POST
$id_parkir = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id_parkir']) ? intval($_POST['id_parkir']) : 0);

if ($id_parkir <= 0) {
    header("Location: index.php?status=error&msg=ID parkir tidak valid!");
    exit;
}

// Ambil data transaksi hanya jika status masih 'Masuk'
$query = "SELECT * FROM tbl_transaksi_parkir WHERE id_parkir = ? AND status = 'Masuk'";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $id_parkir);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$transaksi = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Jika data tidak ditemukan atau status sudah 'Selesai'
if (!$transaksi) {
    header("Location: index.php?status=error&msg=Transaksi tidak ditemukan atau sudah selesai diproses!");
    exit;
}

// Waktu keluar otomatis zona Asia/Jakarta
$waktu_keluar = date('Y-m-d H:i:s');
$waktu_masuk  = $transaksi['waktu_masuk'];

// Hitung selisih waktu dalam detik
$selisih_detik = strtotime($waktu_keluar) - strtotime($waktu_masuk);
if ($selisih_detik < 0) {
    $selisih_detik = 0;
}

// Durasi disimpan dalam menit (dibulatkan ke atas)
$durasi_menit = (int) ceil($selisih_detik / 60);

// Hitung tarif sesuai aturan bisnis:
// 1) Toleransi parkir gratis <= 10 menit
// 2) Jika > 10 menit, seluruh durasi dihitung pembulatan ke atas per jam
$total_biaya = 0;

if ($durasi_menit <= 10) {
    // Gratis jika tidak lebih dari 10 menit
    $total_biaya = 0;
} else {
    // Pembulatan ke atas per jam
    $durasi_jam = (int) ceil($durasi_menit / 60);

    $jenis = $transaksi['jenis_kendaraan'];
    if ($jenis === 'Motor') {
        $tarif_awal   = 3000;
        $tarif_lanjut = 2000;
    } elseif ($jenis === 'Mobil') {
        $tarif_awal   = 5000;
        $tarif_lanjut = 4000;
    } elseif ($jenis === 'Box/Truk Ringan') {
        $tarif_awal   = 10000;
        $tarif_lanjut = 7000;
    } else {
        $tarif_awal   = 0;
        $tarif_lanjut = 0;
    }

    if ($durasi_jam <= 1) {
        $total_biaya = $tarif_awal;
    } else {
        $total_biaya = $tarif_awal + (($durasi_jam - 1) * $tarif_lanjut);
    }
}

// Update data transaksi menjadi 'Selesai'
$query_update = "UPDATE tbl_transaksi_parkir SET waktu_keluar = ?, durasi = ?, total_biaya = ?, status = 'Selesai' WHERE id_parkir = ? AND status = 'Masuk'";
$stmt_update = mysqli_prepare($koneksi, $query_update);
mysqli_stmt_bind_param($stmt_update, "siii", $waktu_keluar, $durasi_menit, $total_biaya, $id_parkir);

if (mysqli_stmt_execute($stmt_update) && mysqli_stmt_affected_rows($stmt_update) > 0) {
    mysqli_stmt_close($stmt_update);
    $pesan_sukses = "Check-Out berhasil! Plat: " . $transaksi['plat_nomor'] . " | Durasi: " . $durasi_menit . " Menit | Total Biaya: Rp " . number_format($total_biaya, 0, ',', '.');
    header("Location: index.php?status=sukses&msg=" . urlencode($pesan_sukses));
    exit;
} else {
    mysqli_stmt_close($stmt_update);
    header("Location: index.php?status=error&msg=Gagal menyelesaikan transaksi atau transaksi sudah pernah diselesaikan!");
    exit;
}
?>
