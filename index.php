<?php
require_once 'config.php'; // Pastikan file ini berisi koneksi ke database ($db)

// --- Logika PHP yang Ditingkatkan ---

// 1. Ambil semua tahun unik dari kedua tabel untuk filter dinamis
$query_tahun = "
    SELECT DISTINCT YEAR(tanggal) AS tahun FROM surat_masuk
    UNION
    SELECT DISTINCT YEAR(tanggal) AS tahun FROM surat_keluar
    ORDER BY tahun DESC
";
$hasil_tahun = $db->query($query_tahun);
$tahun_tersedia = [];
while ($row = $hasil_tahun->fetch_assoc()) {
    $tahun_tersedia[] = $row['tahun'];
}

// 2. Tentukan tahun yang aktif (dari GET request atau tahun terbaru)
$tahun_aktif = $_GET['tahun'] ?? ($tahun_tersedia[0] ?? date('Y'));

// 3. Gunakan prepared statement untuk keamanan dan efisiensi
$stmt_masuk = $db->prepare("SELECT COUNT(*) as total FROM surat_masuk WHERE YEAR(tanggal) = ?");
$stmt_masuk->bind_param("i", $tahun_aktif);
$stmt_masuk->execute();
$total_masuk = $stmt_masuk->get_result()->fetch_assoc()['total'];
$stmt_masuk->close();

$stmt_keluar = $db->prepare("SELECT COUNT(*) as total FROM surat_keluar WHERE YEAR(tanggal) = ?");
$stmt_keluar->bind_param("i", $tahun_aktif);
$stmt_keluar->execute();
$total_keluar = $stmt_keluar->get_result()->fetch_assoc()['total'];
$stmt_keluar->close();

$total_semua = $total_masuk + $total_keluar;

// 4. Ambil data surat untuk ditampilkan di tabel
$stmt_data_masuk = $db->prepare("SELECT * FROM surat_masuk WHERE YEAR(tanggal) = ? ORDER BY tanggal DESC");
$stmt_data_masuk->bind_param("i", $tahun_aktif);
$stmt_data_masuk->execute();
$data_surat_masuk = $stmt_data_masuk->get_result();
$stmt_data_masuk->close();

$stmt_data_keluar = $db->prepare("SELECT * FROM surat_keluar WHERE YEAR(tanggal) = ? ORDER BY tanggal DESC");
$stmt_data_keluar->bind_param("i", $tahun_aktif);
$stmt_data_keluar->execute();
$data_surat_keluar = $stmt_data_keluar->get_result();
$stmt_data_keluar->close();

?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <title>Beranda | Arsip Surat SDN 1 Cibeureum</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Sistem informasi dan arsip digital untuk surat masuk dan keluar SDN 1 Cibeureum.">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; }
    /* Efek gradien untuk background dots yang lebih menarik */
    .bg-dots-colorful {
      background-image: radial-gradient(circle at 1px 1px, #a78bfa 1px, transparent 0),
                        radial-gradient(circle at 1px 1px, #60a5fa 1px, transparent 0);
      background-size: 32px 32px;
      background-position: 0 0, 16px 16px;
    }

    /* Gaya untuk modal popup */
    .modal {
      display: none; /* Sembunyikan secara default */
      position: fixed; /* Tetap di posisi yang sama saat scroll */
      z-index: 1000; /* Atur z-index lebih tinggi dari elemen lain */
      left: 0;
      top: 0;
      width: 100%; /* Lebar penuh */
      height: 100%; /* Tinggi penuh */
      overflow: auto; /* Aktifkan scroll jika konten terlalu besar */
      background-color: rgba(0,0,0,0.7); /* Warna latar belakang gelap */
      justify-content: center; /* Pusatkan konten secara horizontal */
      align-items: center; /* Pusatkan konten secara vertikal */
    }

    .modal-content {
      background-color: #fefefe;
      margin: auto;
      padding: 20px;
      border-radius: 15px;
      width: 95%; /* Lebar responsif */
      max-width: 1100px; /* Lebar maksimum */
      box-shadow: 0 4px 20px rgba(0,0,0,0.3);
      position: relative;
      display: flex;
      flex-direction: column;
      height: 95vh; /* Menggunakan height agar iframe dapat menghitung tingginya */
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-bottom: 15px;
      border-bottom: 1px solid #eee;
      margin-bottom: 15px;
    }

    .modal-header h2 {
      font-size: 1.5rem;
      font-weight: bold;
      color: #333;
    }

    .close-button {
      color: #aaa;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
      padding: 5px 10px;
      border-radius: 50%;
      transition: background-color 0.3s ease;
    }

    .close-button:hover,
    .close-button:focus {
      color: #000;
      background-color: #f0f0f0;
      text-decoration: none;
    }

    .modal-body {
      flex-grow: 1;
      /* overflow: hidden; -- Dihapus agar iframe dapat mengelola scrollnya sendiri */
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .modal-body iframe {
      width: 100%;
      height: 100%;
      border: none;
      border-radius: 10px;
      overflow: auto; /* Memastikan iframe dapat di-scroll jika kontennya melebihi ukuran */
    }
  </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 to-blue-100 text-slate-800">

  <!-- Header -->
  <header class="bg-white/90 backdrop-blur-lg sticky top-0 z-50 shadow-xl border-b border-blue-100">
    <div class="container mx-auto flex justify-between items-center p-4">
      <a href="#" class="flex items-center gap-2 text-2xl font-extrabold text-blue-700">
        <span class="iconify text-indigo-600" data-icon="mdi:folder-file-outline"></span>
        <span>ArsipSurat</span>
      </a>
      <a href="login.php" class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-2.5 rounded-full font-semibold shadow-lg shadow-blue-500/30 hover:from-blue-700 hover:to-indigo-700 transition-all transform hover:scale-105">
        Login Admin
      </a>
    </div>
  </header>

  <main>
    <!-- Hero Section -->
    <section class="relative py-24 md:py-36 text-center overflow-hidden bg-gradient-to-br from-blue-500 to-indigo-600 text-white">
        <div class="absolute inset-0 bg-dots-colorful opacity-30"></div>
        <div class="container mx-auto px-4 relative">
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-extrabold leading-tight drop-shadow-lg">
                Sistem Arsip Surat Digital
            </h1>
            <p class="mt-6 text-lg md:text-xl lg:text-2xl max-w-4xl mx-auto opacity-90">
                Selamat datang di pusat arsip surat <span class="font-bold text-yellow-300">SDN 1 Cibeureum</span>. Temukan dan kelola surat masuk & keluar dengan mudah, cepat, dan terorganisir.
            </p>
        </div>
    </section>

    <!-- Konten Utama: Statistik dan Tabel -->
    <div class="container mx-auto px-4 py-16 md:py-20">
        <!-- Filter dan Statistik -->
        <div class="bg-white p-8 rounded-3xl shadow-2xl mb-12 border border-blue-100">
            <div class="md:flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold text-slate-800 mb-4 md:mb-0 flex items-center gap-3">
                    <span class="iconify text-blue-500" data-icon="mdi:chart-bar"></span>
                    Statistik Arsip Tahun <?= htmlspecialchars($tahun_aktif) ?>
                </h2>
                <form method="GET" class="flex items-center gap-3">
                    <label for="tahun" class="text-base font-medium text-slate-600">Pilih Tahun:</label>
                    <select name="tahun" id="tahun" onchange="this.form.submit()" class="border-blue-300 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-all text-base py-2 px-4">
                        <?php if (empty($tahun_tersedia)) : ?>
                            <option><?= date('Y') ?></option>
                        <?php else : ?>
                            <?php foreach ($tahun_tersedia as $thn) : ?>
                                <option value="<?= $thn ?>" <?= $tahun_aktif == $thn ? 'selected' : '' ?>><?= $thn ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </form>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Kartu Surat Masuk -->
                <div class="bg-gradient-to-br from-blue-600 to-blue-800 text-white p-7 rounded-2xl shadow-xl transform hover:-translate-y-2 transition-transform duration-300 ease-in-out">
                    <div class="flex justify-between items-start">
                        <span class="text-4xl font-bold"><?= $total_masuk ?></span>
                        <span class="iconify text-5xl opacity-60" data-icon="mdi:inbox-arrow-down-outline"></span>
                    </div>
                    <p class="mt-3 font-semibold text-lg">Surat Masuk</p>
                </div>
                <!-- Kartu Surat Keluar -->
                <div class="bg-gradient-to-br from-green-500 to-emerald-700 text-white p-7 rounded-2xl shadow-xl transform hover:-translate-y-2 transition-transform duration-300 ease-in-out">
                    <div class="flex justify-between items-start">
                        <span class="text-4xl font-bold"><?= $total_keluar ?></span>
                        <span class="iconify text-5xl opacity-60" data-icon="mdi:send-outline"></span>
                    </div>
                    <p class="mt-3 font-semibold text-lg">Surat Keluar</p>
                </div>
                <!-- Kartu Total Surat -->
                <div class="bg-gradient-to-br from-purple-600 to-indigo-800 text-white p-7 rounded-2xl shadow-xl transform hover:-translate-y-2 transition-transform duration-300 ease-in-out">
                    <div class="flex justify-between items-start">
                        <span class="text-4xl font-bold"><?= $total_semua ?></span>
                        <span class="iconify text-5xl opacity-60" data-icon="mdi:file-document-multiple-outline"></span>
                    </div>
                    <p class="mt-3 font-semibold text-lg">Total Arsip</p>
                </div>
            </div>
        </div>

        <!-- Tabel Surat Masuk -->
        <div class="bg-white p-8 rounded-3xl shadow-2xl mb-12 border border-blue-100">
            <h3 class="text-2xl font-bold text-slate-800 mb-6 flex items-center gap-3">
                <span class="iconify text-blue-600 text-3xl" data-icon="mdi:inbox-full-outline"></span>
                Daftar Surat Masuk
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-base text-left text-slate-700">
                    <thead class="bg-blue-50 text-blue-700 uppercase text-sm rounded-t-xl">
                        <tr>
                            <th class="px-6 py-4 rounded-tl-xl">Nomor Surat</th>
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Pengirim</th>
                            <th class="px-6 py-4">Perihal</th>
                            <th class="px-6 py-4 text-center rounded-tr-xl">File</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if ($data_surat_masuk->num_rows > 0) : ?>
                            <?php while ($row = $data_surat_masuk->fetch_assoc()) : ?>
                                <tr class="hover:bg-blue-50/50 transition-colors">
                                    <td class="px-6 py-4 font-medium whitespace-nowrap"><?= htmlspecialchars($row['nomor_surat']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= date('d F Y', strtotime($row['tanggal'])) ?></td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($row['pengirim']) ?></td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($row['perihal']) ?></td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex flex-col items-center space-y-2">
                                            <button onclick="openModal('uploads/<?= htmlspecialchars($row['file_surat']) ?>', 'Surat Masuk: <?= htmlspecialchars($row['nomor_surat']) ?>')" class="bg-gradient-to-r from-blue-500 to-indigo-500 text-white px-4 py-2 rounded-full text-sm font-semibold shadow-md shadow-blue-300/50 hover:from-blue-600 hover:to-indigo-600 transition-all transform hover:scale-105 w-full">
                                                Lihat
                                            </button>
                                            <a href="uploads/<?= htmlspecialchars($row['file_surat']) ?>" download class="bg-gradient-to-r from-gray-500 to-gray-600 text-white px-4 py-2 rounded-full text-sm font-semibold shadow-md shadow-gray-300/50 hover:from-gray-600 hover:to-gray-700 transition-all transform hover:scale-105 w-full">
                                                Download
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr><td colspan="5" class="text-center py-10 text-slate-500 text-lg">Tidak ada data surat masuk untuk tahun <?= htmlspecialchars($tahun_aktif) ?>.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tabel Surat Keluar -->
        <div class="bg-white p-8 rounded-3xl shadow-2xl">
            <h3 class="text-2xl font-bold text-slate-800 mb-6 flex items-center gap-3">
                <span class="iconify text-green-600 text-3xl" data-icon="mdi:send-check-outline"></span>
                Daftar Surat Keluar
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-base text-left text-slate-700">
                    <thead class="bg-green-50 text-green-700 uppercase text-sm rounded-t-xl">
                        <tr>
                            <th class="px-6 py-4 rounded-tl-xl">Nomor Surat</th>
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Penerima</th>
                            <th class="px-6 py-4">Perihal</th>
                            <th class="px-6 py-4 text-center rounded-tr-xl">File</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if ($data_surat_keluar->num_rows > 0) : ?>
                            <?php while ($row = $data_surat_keluar->fetch_assoc()) : ?>
                                <tr class="hover:bg-green-50/50 transition-colors">
                                    <td class="px-6 py-4 font-medium whitespace-nowrap"><?= htmlspecialchars($row['nomor_surat']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= date('d F Y', strtotime($row['tanggal'])) ?></td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($row['penerima']) ?></td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($row['perihal']) ?></td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex flex-col items-center space-y-2">
                                            <button onclick="openModal('uploads/<?= htmlspecialchars($row['file_surat']) ?>', 'Surat Keluar: <?= htmlspecialchars($row['nomor_surat']) ?>')" class="bg-gradient-to-r from-green-500 to-emerald-500 text-white px-4 py-2 rounded-full text-sm font-semibold shadow-md shadow-green-300/50 hover:from-green-600 hover:to-emerald-600 transition-all transform hover:scale-105 w-full">
                                                Lihat
                                            </button>
                                            <a href="uploads/<?= htmlspecialchars($row['file_surat']) ?>" download class="bg-gradient-to-r from-gray-500 to-gray-600 text-white px-4 py-2 rounded-full text-sm font-semibold shadow-md shadow-gray-300/50 hover:from-gray-600 hover:to-gray-700 transition-all transform hover:scale-105 w-full">
                                                Download
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr><td colspan="5" class="text-center py-10 text-slate-500 text-lg">Tidak ada data surat keluar untuk tahun <?= htmlspecialchars($tahun_aktif) ?>.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="bg-slate-800 text-white mt-16 py-8 shadow-inner">
    <div class="container mx-auto text-center text-sm">
      &copy; <?= date('Y') ?> Asep Hilmi. All Rights Reserved.
    </div>
  </footer>

  <!-- Modal untuk menampilkan file -->
  <div id="fileModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 id="modalTitle"></h2>
        <span class="close-button" onclick="closeModal()">&times;</span>
      </div>
      <div class="modal-body">
        <iframe id="fileViewer" src="" frameborder="0"></iframe>
      </div>
    </div>
  </div>

  <script>
    // Fungsi untuk membuka modal
    function openModal(filePath, title) {
      const modal = document.getElementById('fileModal');
      const fileViewer = document.getElementById('fileViewer');
      const modalTitle = document.getElementById('modalTitle');

      modalTitle.innerText = title;
      fileViewer.src = filePath;
      modal.style.display = 'flex'; // Gunakan flexbox untuk centering
      document.body.style.overflow = 'hidden'; // Nonaktifkan scroll body saat modal terbuka
    }

    // Fungsi untuk menutup modal
    function closeModal() {
      const modal = document.getElementById('fileModal');
      const fileViewer = document.getElementById('fileViewer');
      modal.style.display = 'none';
      fileViewer.src = ''; // Kosongkan src iframe untuk menghentikan pemutaran jika ada media
      document.body.style.overflow = ''; // Aktifkan kembali scroll body
    }

    // Tutup modal jika mengklik di luar area konten modal
    window.onclick = function(event) {
      const modal = document.getElementById('fileModal');
      if (event.target == modal) {
        closeModal();
      }
    }
  </script>

</body>
</html>
