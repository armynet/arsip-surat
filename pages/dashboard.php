<?php
// Pastikan file config.php di-include dengan benar
// Path __DIR__ . '/../config.php' mengasumsikan file ini ada di dalam folder 'pages'
require_once __DIR__ . '/../config.php';

// Pastikan session sudah dimulai di file admin.php
if (!isset($_SESSION['user_id'])) {
    // Seharusnya tidak akan pernah terjadi jika alur sudah benar
    exit('Akses ditolak.');
}

// Ambil tahun aktif dari URL atau default ke tahun sekarang
$tahun_aktif = $_GET['tahun'] ?? date('Y');

// Ambil data statistik dari database untuk tahun aktif
$stmt_masuk = $db->prepare("SELECT COUNT(*) as total FROM surat_masuk WHERE YEAR(tanggal) = ?");
$stmt_masuk->bind_param("i", $tahun_aktif);
$stmt_masuk->execute();
$masuk = $stmt_masuk->get_result()->fetch_assoc()['total'];
$stmt_masuk->close();

$stmt_keluar = $db->prepare("SELECT COUNT(*) as total FROM surat_keluar WHERE YEAR(tanggal) = ?");
$stmt_keluar->bind_param("i", $tahun_aktif);
$stmt_keluar->execute();
$keluar = $stmt_keluar->get_result()->fetch_assoc()['total'];
$stmt_keluar->close();

$total = $masuk + $keluar;

// Ambil semua tahun unik dari kedua tabel untuk filter dinamis
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
// Jika tidak ada tahun tersedia, tambahkan tahun saat ini sebagai opsi
if (empty($tahun_tersedia)) {
    $tahun_tersedia[] = date('Y');
}


// Ambil 5 surat masuk terbaru untuk tahun aktif
$stmt_data_masuk_terbaru = $db->prepare("SELECT * FROM surat_masuk WHERE YEAR(tanggal) = ? ORDER BY tanggal DESC LIMIT 5");
$stmt_data_masuk_terbaru->bind_param("i", $tahun_aktif);
$stmt_data_masuk_terbaru->execute();
$data_surat_masuk_terbaru = $stmt_data_masuk_terbaru->get_result();
$stmt_data_masuk_terbaru->close();

// Ambil 5 surat keluar terbaru untuk tahun aktif
$stmt_data_keluar_terbaru = $db->prepare("SELECT * FROM surat_keluar WHERE YEAR(tanggal) = ? ORDER BY tanggal DESC LIMIT 5");
$stmt_data_keluar_terbaru->bind_param("i", $tahun_aktif);
$stmt_data_keluar_terbaru->execute();
$data_surat_keluar_terbaru = $stmt_data_keluar_terbaru->get_result();
$stmt_data_keluar_terbaru->close();

?>

<h1 class="text-4xl font-extrabold mb-8 text-gray-900">Dashboard Admin</h1>

<!-- Filter Tahun -->
<div class="bg-white p-6 rounded-2xl shadow-lg mb-8 flex flex-col sm:flex-row justify-between items-center">
    <h2 class="text-2xl font-bold text-slate-800 mb-4 sm:mb-0">Statistik Arsip Tahun <?= htmlspecialchars($tahun_aktif) ?></h2>
    <form method="GET" class="flex items-center gap-3">
        <label for="tahun_filter" class="text-base font-medium text-slate-600">Pilih Tahun:</label>
        <select name="tahun" id="tahun_filter" onchange="this.form.submit()" class="border-blue-300 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-all text-base py-2 px-4">
            <?php foreach ($tahun_tersedia as $thn) : ?>
                <option value="<?= $thn ?>" <?= $tahun_aktif == $thn ? 'selected' : '' ?>><?= $thn ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>


<!-- Kartu Statistik -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
  <!-- Surat Masuk -->
  <div class="bg-gradient-to-br from-blue-500 to-blue-700 text-white rounded-2xl shadow-xl p-7 transform hover:scale-105 transition-transform duration-300">
    <div class="flex justify-between items-center mb-3">
      <div class="text-xl font-semibold">Surat Masuk</div>
      <span class="iconify text-4xl opacity-75" data-icon="mdi:email-open-outline"></span>
    </div>
    <div class="text-5xl font-bold"><?= $masuk ?></div>
  </div>
  <!-- Surat Keluar -->
  <div class="bg-gradient-to-br from-green-500 to-green-700 text-white rounded-2xl shadow-xl p-7 transform hover:scale-105 transition-transform duration-300">
    <div class="flex justify-between items-center mb-3">
      <div class="text-xl font-semibold">Surat Keluar</div>
      <span class="iconify text-4xl opacity-75" data-icon="mdi:send-circle-outline"></span>
    </div>
    <div class="text-5xl font-bold"><?= $keluar ?></div>
  </div>
  <!-- Total Surat -->
  <div class="bg-gradient-to-br from-purple-500 to-purple-700 text-white rounded-2xl shadow-xl p-7 transform hover:scale-105 transition-transform duration-300">
    <div class="flex justify-between items-center mb-3">
      <div class="text-xl font-semibold">Total Surat (<?= htmlspecialchars($tahun_aktif) ?>)</div>
      <span class="iconify text-4xl opacity-75" data-icon="mdi:file-document-multiple-outline"></span>
    </div>
    <div class="text-5xl font-bold"><?= $total ?></div>
  </div>
  <!-- Info Admin (Dihapus karena dependensi tabel 'users') -->
  <!-- <div class="bg-gradient-to-br from-gray-700 to-gray-900 text-white rounded-2xl shadow-xl p-7 transform hover:scale-105 transition-transform duration-300">
    <div class="flex justify-between items-center mb-3">
      <div class="text-xl font-semibold">Admin Login</div>
      <span class="iconify text-4xl opacity-75" data-icon="mdi:account-circle-outline"></span>
    </div>
    <div class="text-3xl font-bold truncate"><?= htmlspecialchars($_SESSION['username'] ?? 'N/A') ?></div>
  </div> -->
</div>

<!-- Tabel Surat Masuk Terbaru -->
<div class="bg-white rounded-2xl shadow-xl p-8 mb-12 border border-blue-100">
  <div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
        <span class="iconify text-blue-600 text-3xl" data-icon="mdi:inbox-full-outline"></span>
        Surat Masuk Terbaru (Tahun <?= htmlspecialchars($tahun_aktif) ?>)
    </h2>
    <a href="admin.php?page=surat_masuk" class="text-base font-medium text-blue-600 hover:underline hover:text-blue-800 transition-colors">Lihat Semua</a>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-base text-left text-gray-700">
      <thead class="bg-blue-50 text-blue-700 uppercase text-sm">
        <tr>
          <th class="py-3 px-4 rounded-tl-lg">Nomor Surat</th>
          <th class="py-3 px-4">Tanggal</th>
          <th class="py-3 px-4">Pengirim</th>
          <th class="py-3 px-4">Perihal</th>
          <th class="py-3 px-4 text-center rounded-tr-lg">Aksi</th>
        </tr>
      </thead>
      <tbody class="text-gray-800 divide-y divide-gray-200">
        <?php
        if ($data_surat_masuk_terbaru->num_rows > 0) {
          while ($row = $data_surat_masuk_terbaru->fetch_assoc()) {
            echo "<tr class='hover:bg-blue-50/50 transition-colors'>
                    <td class='py-3 px-4 font-medium whitespace-nowrap'>" . htmlspecialchars($row['nomor_surat']) . "</td>
                    <td class='py-3 px-4 whitespace-nowrap'>" . date('d F Y', strtotime($row['tanggal'])) . "</td>
                    <td class='py-3 px-4'>" . htmlspecialchars($row['pengirim']) . "</td>
                    <td class='py-3 px-4'>" . htmlspecialchars($row['perihal']) . "</td>
                    <td class='py-3 px-4 text-center'>
                        <a href='uploads/" . htmlspecialchars($row['file_surat']) . "' target='_blank' class='bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-full text-sm font-semibold shadow-md transition-colors'>Lihat</a>
                    </td>
                  </tr>";
          }
        } else {
          echo "<tr><td colspan='5' class='text-center text-gray-500 py-6'>Tidak ada data surat masuk untuk tahun " . htmlspecialchars($tahun_aktif) . ".</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Tabel Surat Keluar Terbaru -->
<div class="bg-white rounded-2xl shadow-xl p-8 border border-green-100">
  <div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
        <span class="iconify text-green-600 text-3xl" data-icon="mdi:send-check-outline"></span>
        Surat Keluar Terbaru (Tahun <?= htmlspecialchars($tahun_aktif) ?>)
    </h2>
    <a href="admin.php?page=surat_keluar" class="text-base font-medium text-green-600 hover:underline hover:text-green-800 transition-colors">Lihat Semua</a>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-base text-left text-gray-700">
      <thead class="bg-green-50 text-green-700 uppercase text-sm">
        <tr>
          <th class="py-3 px-4 rounded-tl-lg">Nomor Surat</th>
          <th class="py-3 px-4">Tanggal</th>
          <th class="py-3 px-4">Penerima</th>
          <th class="py-3 px-4">Perihal</th>
          <th class="py-3 px-4 text-center rounded-tr-lg">Aksi</th>
        </tr>
      </thead>
      <tbody class="text-gray-800 divide-y divide-gray-200">
        <?php
        if ($data_surat_keluar_terbaru->num_rows > 0) {
          while ($row = $data_surat_keluar_terbaru->fetch_assoc()) {
            echo "<tr class='hover:bg-green-50/50 transition-colors'>
                    <td class='py-3 px-4 font-medium whitespace-nowrap'>" . htmlspecialchars($row['nomor_surat']) . "</td>
                    <td class='py-3 px-4 whitespace-nowrap'>" . date('d F Y', strtotime($row['tanggal'])) . "</td>
                    <td class='py-3 px-4'>" . htmlspecialchars($row['penerima']) . "</td>
                    <td class='py-3 px-4'>" . htmlspecialchars($row['perihal']) . "</td>
                    <td class='py-3 px-4 text-center'>
                        <a href='uploads/" . htmlspecialchars($row['file_surat']) . "' target='_blank' class='bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-full text-sm font-semibold shadow-md transition-colors'>Lihat</a>
                    </td>
                  </tr>";
          }
        } else {
          echo "<tr><td colspan='5' class='text-center text-gray-500 py-6'>Tidak ada data surat keluar untuk tahun " . htmlspecialchars($tahun_aktif) . ".</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>
