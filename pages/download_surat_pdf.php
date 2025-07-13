<?php
// Memastikan file konfigurasi database di-include
require_once __DIR__ . '/../config.php';

// session_start(); // BARIS INI DIHAPUS KARENA SUDAH DIMULAI DI admin.php (jika diakses via admin.php)
// Namun, karena ini akan menjadi skrip mandiri, kita perlu session_start() di sini.
session_start();

// Cek apakah pengguna sudah login. Jika tidak, alihkan ke halaman login.
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Akses ditolak. Silakan login terlebih dahulu.'); window.location.href='login.php';</script>";
    exit;
}

// Hanya izinkan admin atau superadmin untuk mengunduh data
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'superadmin')) {
    echo "<script>alert('Akses ditolak. Anda tidak memiliki izin untuk mengunduh data.'); window.location.href='admin.php?page=dashboard';</script>";
    exit;
}

$surat_masuk_data = [];
$surat_keluar_data = [];
$error = "";

// Ambil data surat masuk
try {
    $stmt_masuk = $db->prepare("SELECT id, nomor_surat, tanggal, pengirim, perihal FROM surat_masuk ORDER BY tanggal DESC");
    $stmt_masuk->execute();
    $result_masuk = $stmt_masuk->get_result();
    while ($row = $result_masuk->fetch_assoc()) {
        $surat_masuk_data[] = $row;
    }
    $stmt_masuk->close();
} catch (Exception $e) {
    $error .= "Gagal mengambil data surat masuk: " . $e->getMessage() . "<br>";
}

// Ambil data surat keluar
try {
    $stmt_keluar = $db->prepare("SELECT id, nomor_surat, tanggal, penerima, perihal FROM surat_keluar ORDER BY tanggal DESC");
    $stmt_keluar->execute();
    $result_keluar = $stmt_keluar->get_result();
    while ($row = $result_keluar->fetch_assoc()) {
        $surat_keluar_data[] = $row;
    }
    $stmt_keluar->close();
} catch (Exception $e) {
    $error .= "Gagal mengambil data surat keluar: " . $e->getMessage() . "<br>";
}

$db->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Data Surat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6; /* bg-gray-100 */
            color: #1f2937; /* text-gray-900 */
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 2rem auto;
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 0.75rem; /* rounded-xl */
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); /* shadow-lg */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }
        th, td {
            border: 1px solid #e5e7eb; /* border-gray-200 */
            padding: 0.75rem;
            text-align: left;
        }
        th {
            background-color: #f9fafb; /* bg-gray-50 */
            font-weight: 600; /* font-semibold */
            text-transform: uppercase;
            font-size: 0.75rem; /* text-xs */
            color: #6b7280; /* text-gray-500 */
        }
        .section-title {
            font-size: 1.5rem; /* text-2xl */
            font-weight: 700; /* font-bold */
            color: #1f2937; /* text-gray-800 */
            margin-top: 2rem;
            margin-bottom: 1rem;
            border-bottom: 2px solid #6366f1; /* border-indigo-500 */
            padding-bottom: 0.5rem;
        }
        .no-data {
            text-align: center;
            padding: 1rem;
            color: #6b7280; /* text-gray-500 */
        }

        /* Print-specific styles */
        @media print {
            body {
                background-color: #fff;
                margin: 0;
                padding: 0;
            }
            .container {
                box-shadow: none;
                border: none;
                margin: 0;
                padding: 0;
                max-width: none;
            }
            .print-button {
                display: none; /* Hide print button when printing */
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-3xl font-extrabold text-center text-gray-900 mb-6">Laporan Data Arsip Surat</h1>
        <p class="text-center text-gray-600 mb-8">Tanggal Laporan: <?= date('d M Y H:i:s') ?></p>

        <?php if ($error) : ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md" role="alert">
                <p class="font-bold">Error!</p>
                <p><?= $error ?></p>
            </div>
        <?php endif; ?>

        <h2 class="section-title">Data Surat Masuk</h2>
        <?php if (!empty($surat_masuk_data)) : ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nomor Surat</th>
                        <th>Tanggal</th>
                        <th>Pengirim</th>
                        <th>Perihal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($surat_masuk_data as $surat) : ?>
                        <tr>
                            <td><?= htmlspecialchars($surat['id']) ?></td>
                            <td><?= htmlspecialchars($surat['nomor_surat']) ?></td>
                            <td><?= htmlspecialchars($surat['tanggal']) ?></td>
                            <td><?= htmlspecialchars($surat['pengirim']) ?></td>
                            <td><?= htmlspecialchars($surat['perihal']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p class="no-data">Tidak ada data surat masuk yang ditemukan.</p>
        <?php endif; ?>

        <h2 class="section-title">Data Surat Keluar</h2>
        <?php if (!empty($surat_keluar_data)) : ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nomor Surat</th>
                        <th>Tanggal</th>
                        <th>Penerima</th>
                        <th>Perihal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($surat_keluar_data as $surat) : ?>
                        <tr>
                            <td><?= htmlspecialchars($surat['id']) ?></td>
                            <td><?= htmlspecialchars($surat['nomor_surat']) ?></td>
                            <td><?= htmlspecialchars($surat['tanggal']) ?></td>
                            <td><?= htmlspecialchars($surat['penerima']) ?></td>
                            <td><?= htmlspecialchars($surat['perihal']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p class="no-data">Tidak ada data surat keluar yang ditemukan.</p>
        <?php endif; ?>

        <div class="flex justify-center mt-8 print-button">
            <button onclick="window.print()" class="bg-blue-600 text-white px-8 py-3 rounded-lg shadow-md hover:bg-blue-700 transition-colors text-lg font-semibold">
                Cetak ke PDF
            </button>
        </div>
    </div>
</body>
</html>
