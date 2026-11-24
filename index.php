<?php
session_start();
require_once "koneksi.php";

// Proteksi halaman: Wajib login petugas
if (!isset($_SESSION['id_petugas'])) {
    header("Location: login.php");
    exit;
}

$id_petugas   = $_SESSION['id_petugas'];
$nama_petugas = $_SESSION['nama_petugas'];

// Ambil pesan notifikasi jika ada
$status_pesan = $_GET['status'] ?? '';
$isi_pesan    = $_GET['msg'] ?? '';

// Query SELECT data transaksi parkir join petugas
$query = "SELECT t.*, p.nama_petugas 
          FROM tbl_transaksi_parkir t
          JOIN tbl_petugas p ON t.id_petugas = p.id_petugas
          ORDER BY t.id_parkir DESC";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sistem Informasi Parkir - Nusantara Mall</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="kotak-utama">

    <!-- Header & Info Petugas -->
    <div class="header-info">
        <div style="float: right; text-align: right;">
            <span>Petugas: <b><?php echo htmlspecialchars($nama_petugas); ?></b></span> |
            <a href="logout.php" class="btn btn-logout" onclick="return confirm('Apakah Anda yakin ingin logout?')">Logout</a>
            <br>
            <!-- Tampilan Hari, Tanggal dan Jam Real-Time dengan JavaScript -->
            <div id="jam-realtime" class="jam-box">Memuat waktu...</div>
        </div>
        <h2>NUSANTARA MALL PARKING</h2>
        <div style="font-size: 13px; color: #444;">Sistem Manajemen Parkir Kendaraan Masuk & Keluar</div>
        <div style="clear: both;"></div>
    </div>

    <!-- Notifikasi Pesan -->
    <?php if ($status_pesan === 'sukses'): ?>
        <div class="pesan-sukses"><?php echo htmlspecialchars($isi_pesan); ?></div>
    <?php elseif ($status_pesan === 'error'): ?>
        <div class="pesan-error"><?php echo htmlspecialchars($isi_pesan); ?></div>
    <?php endif; ?>

    <!-- Form Check-In Kendaraan Masuk -->
    <div class="form-parkir">
        <h3>Form Check-In Kendaraan Masuk</h3>
        <form action="proses_masuk.php" method="POST">
            <div class="form-group">
                <label for="plat_nomor">Plat Nomor:</label>
                <input type="text" name="plat_nomor" id="plat_nomor" placeholder="Contoh: B 1234 ABC" required>
            </div>
            <div class="form-group">
                <label for="jenis_kendaraan">Jenis Kendaraan:</label>
                <select name="jenis_kendaraan" id="jenis_kendaraan" required>
                    <option value="">-- Pilih Jenis --</option>
                    <option value="Motor">Motor (Rp 3.000 / Jam 1, Rp 2.000 / Jam Berikutnya)</option>
                    <option value="Mobil">Mobil (Rp 5.000 / Jam 1, Rp 4.000 / Jam Berikutnya)</option>
                    <option value="Box/Truk Ringan">Box/Truk Ringan (Rp 10.000 / Jam 1, Rp 7.000 / Jam Berikutnya)</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-masuk">Check-In Kendaraan</button>
                <span style="font-size: 12px; color: #666; margin-left: 10px;">*Waktu masuk akan otomatis tercatat saat tombol ditekan (Toleransi gratis 10 menit).</span>
            </div>
        </form>
    </div>

    <!-- Tabel Data Transaksi Parkir -->
    <h3>Data Transaksi Parkir</h3>
    <table>
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="14%">Plat Nomor</th>
                <th width="12%">Jenis</th>
                <th width="16%">Waktu Masuk</th>
                <th width="16%">Waktu Keluar</th>
                <th width="10%">Durasi</th>
                <th width="12%">Total Biaya</th>
                <th width="8%">Status</th>
                <th width="8%">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            if ($result && mysqli_num_rows($result) > 0):
                while ($row = mysqli_fetch_assoc($result)):
            ?>
                <tr>
                    <td align="center"><?php echo $no++; ?></td>
                    <td><b><?php echo htmlspecialchars($row['plat_nomor']); ?></b></td>
                    <td><?php echo htmlspecialchars($row['jenis_kendaraan']); ?></td>
                    <td align="center"><?php echo htmlspecialchars($row['waktu_masuk']); ?></td>
                    <td align="center"><?php echo $row['waktu_keluar'] ? htmlspecialchars($row['waktu_keluar']) : '-'; ?></td>
                    <td align="center"><?php echo $row['durasi'] !== null ? htmlspecialchars($row['durasi']) . " Menit" : '-'; ?></td>
                    <td align="right">
                        <?php 
                        if ($row['total_biaya'] !== null) {
                            echo "Rp " . number_format($row['total_biaya'], 0, ',', '.');
                        } else {
                            echo "-";
                        }
                        ?>
                    </td>
                    <td align="center">
                        <?php if ($row['status'] === 'Masuk'): ?>
                            <span class="status-masuk">Masuk</span>
                        <?php else: ?>
                            <span class="status-selesai">Selesai</span>
                        <?php endif; ?>
                    </td>
                    <td align="center">
                        <?php if ($row['status'] === 'Masuk'): ?>
                            <!-- Tombol Check-Out hanya pada kendaraan berstatus Masuk -->
                            <a href="proses_keluar.php?id=<?php echo $row['id_parkir']; ?>" 
                               class="btn-keluar" 
                               onclick="return confirm('Proses Check-Out kendaraan <?php echo htmlspecialchars($row['plat_nomor']); ?>?')">
                                Check-Out
                            </a>
                        <?php else: ?>
                            <!-- Transaksi yang sudah selesai tidak dapat diproses ulang -->
                            <span style="color: #999; font-size: 12px;">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php 
                endwhile; 
            else: 
            ?>
                <tr>
                    <td colspan="9" align="center" style="padding: 15px; color: #888;">Belum ada data transaksi parkir.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div style="margin-top: 15px; font-size: 11px; color: #777; border-top: 1px dotted #ccc; padding-top: 8px;">
        Petunjuk Uji Penguji:
        <br>- Uji gratis: Input kendaraan baru, lalu langsung tekan Check-Out (&le; 10 menit = Rp 0).
        <br>- Uji 90 menit: Tersedia di database sample data plat <b>B 5678 DEF</b> (Mobil: Rp 9.000) dan <b>B 9999 XYZ</b> (Box: Rp 17.000) yang diset masuk 90 menit lalu.
    </div>

</div>

<!-- JavaScript Waktu Real Time -->
<script>
function perbaruiJam() {
    const sekarang = new Date();

    const daftarHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const daftarBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    const namaHari = daftarHari[sekarang.getDay()];
    const tanggal = sekarang.getDate();
    const namaBulan = daftarBulan[sekarang.getMonth()];
    const tahun = sekarang.getFullYear();

    const jam = String(sekarang.getHours()).padStart(2, '0');
    const menit = String(sekarang.getMinutes()).padStart(2, '0');
    const detik = String(sekarang.getSeconds()).padStart(2, '0');

    const teksWaktu = namaHari + ', ' + tanggal + ' ' + namaBulan + ' ' + tahun + ' | ' + jam + ':' + menit + ':' + detik + ' WIB';
    
    document.getElementById('jam-realtime').innerText = teksWaktu;
}

// Jalankan saat halaman dibuka dan ulangi setiap detik
perbaruiJam();
setInterval(perbaruiJam, 1000);
</script>

</body>
</html>
