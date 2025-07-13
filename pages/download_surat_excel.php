<?php
// Memastikan file konfigurasi database di-include
require_once __DIR__ . '/../config.php';

// Memuat autoloader Composer untuk PhpSpreadsheet
// Pastikan Composer telah dijalankan dan folder vendor/ ada di root proyek Anda
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

// Memulai sesi untuk mengakses variabel sesi seperti 'user_role'
session_start();

// Cek apakah pengguna sudah login. Jika tidak, tampilkan pesan error.
if (!isset($_SESSION['user_id'])) {
    echo "Akses ditolak. Silakan login terlebih dahulu.";
    exit;
}

// Hanya izinkan admin atau superadmin untuk mengunduh data
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'superadmin')) {
    echo "Akses ditolak. Anda tidak memiliki izin untuk mengunduh data.";
    exit;
}

// Buat objek Spreadsheet baru
$spreadsheet = new Spreadsheet();

// --- Sheet 1: Surat Masuk ---
$sheetMasuk = $spreadsheet->getActiveSheet();
$sheetMasuk->setTitle('Surat Masuk');

// Header untuk Surat Masuk
$sheetMasuk->setCellValue('A1', 'LAPORAN DATA SURAT MASUK');
$sheetMasuk->mergeCells('A1:E1'); // Gabungkan sel untuk judul
$sheetMasuk->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheetMasuk->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheetMasuk->setCellValue('A3', 'ID');
$sheetMasuk->setCellValue('B3', 'Nomor Surat');
$sheetMasuk->setCellValue('C3', 'Tanggal');
$sheetMasuk->setCellValue('D3', 'Pengirim');
$sheetMasuk->setCellValue('E3', 'Perihal');
// $sheetMasuk->setCellValue('F3', 'File Surat'); // Kolom file surat tidak disertakan untuk laporan cetak/excel

// Styling header kolom Surat Masuk
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['argb' => Color::COLOR_WHITE]],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F46E5']], // Warna ungu Tailwind 600
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => Color::COLOR_BLACK]]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
];
$sheetMasuk->getStyle('A3:E3')->applyFromArray($headerStyle); // Terapkan style ke header

// Ambil data surat masuk
$rowNum = 4; // Baris awal untuk data
try {
    $stmt_masuk = $db->prepare("SELECT id, nomor_surat, tanggal, pengirim, perihal FROM surat_masuk ORDER BY tanggal DESC");
    $stmt_masuk->execute();
    $result_masuk = $stmt_masuk->get_result();

    if ($result_masuk->num_rows > 0) {
        while ($row = $result_masuk->fetch_assoc()) {
            $sheetMasuk->setCellValue('A' . $rowNum, $row['id']);
            $sheetMasuk->setCellValue('B' . $rowNum, $row['nomor_surat']);
            $sheetMasuk->setCellValue('C' . $rowNum, $row['tanggal']);
            $sheetMasuk->setCellValue('D' . $rowNum, $row['pengirim']);
            $sheetMasuk->setCellValue('E' . $rowNum, $row['perihal']);
            // $sheetMasuk->setCellValue('F' . $rowNum, $row['file_surat']);
            $rowNum++;
        }
    } else {
        $sheetMasuk->setCellValue('A' . $rowNum, 'Tidak ada data surat masuk.');
        $sheetMasuk->mergeCells('A' . $rowNum . ':E' . $rowNum);
        $sheetMasuk->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
    $stmt_masuk->close();
} catch (Exception $e) {
    $sheetMasuk->setCellValue('A' . $rowNum, 'Terjadi kesalahan saat mengambil data surat masuk: ' . $e->getMessage());
    $sheetMasuk->mergeCells('A' . $rowNum . ':E' . $rowNum);
    $sheetMasuk->getStyle('A' . $rowNum)->getFont()->setColor(new Color(Color::COLOR_RED));
}

// Auto size kolom untuk Surat Masuk
foreach (range('A', 'E') as $column) {
    $sheetMasuk->getColumnDimension($column)->setAutoSize(true);
}

// Border untuk semua data Surat Masuk
$sheetMasuk->getStyle('A3:E' . ($rowNum - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color(Color::COLOR_BLACK));


// --- Sheet 2: Surat Keluar ---
$sheetKeluar = $spreadsheet->createSheet();
$sheetKeluar->setTitle('Surat Keluar');

// Header untuk Surat Keluar
$sheetKeluar->setCellValue('A1', 'LAPORAN DATA SURAT KELUAR');
$sheetKeluar->mergeCells('A1:E1'); // Gabungkan sel untuk judul
$sheetKeluar->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheetKeluar->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheetKeluar->setCellValue('A3', 'ID');
$sheetKeluar->setCellValue('B3', 'Nomor Surat');
$sheetKeluar->setCellValue('C3', 'Tanggal');
$sheetKeluar->setCellValue('D3', 'Penerima');
$sheetKeluar->setCellValue('E3', 'Perihal');
// $sheetKeluar->setCellValue('F3', 'File Surat'); // Kolom file surat tidak disertakan untuk laporan cetak/excel

// Styling header kolom Surat Keluar (gunakan style yang sama)
$sheetKeluar->getStyle('A3:E3')->applyFromArray($headerStyle);

// Ambil data surat keluar
$rowNum = 4; // Baris awal untuk data
try {
    $stmt_keluar = $db->prepare("SELECT id, nomor_surat, tanggal, penerima, perihal FROM surat_keluar ORDER BY tanggal DESC");
    $stmt_keluar->execute();
    $result_keluar = $stmt_keluar->get_result();

    if ($result_keluar->num_rows > 0) {
        while ($row = $result_keluar->fetch_assoc()) {
            $sheetKeluar->setCellValue('A' . $rowNum, $row['id']);
            $sheetKeluar->setCellValue('B' . $rowNum, $row['nomor_surat']);
            $sheetKeluar->setCellValue('C' . $rowNum, $row['tanggal']);
            $sheetKeluar->setCellValue('D' . $rowNum, $row['penerima']);
            $sheetKeluar->setCellValue('E' . $rowNum, $row['perihal']);
            // $sheetKeluar->setCellValue('F' . $rowNum, $row['file_surat']);
            $rowNum++;
        }
    } else {
        $sheetKeluar->setCellValue('A' . $rowNum, 'Tidak ada data surat keluar.');
        $sheetKeluar->mergeCells('A' . $rowNum . ':E' . $rowNum);
        $sheetKeluar->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
    $stmt_keluar->close();
} catch (Exception $e) {
    $sheetKeluar->setCellValue('A' . $rowNum, 'Terjadi kesalahan saat mengambil data surat keluar: ' . $e->getMessage());
    $sheetKeluar->mergeCells('A' . $rowNum . ':E' . $rowNum);
    $sheetKeluar->getStyle('A' . $rowNum)->getFont()->setColor(new Color(Color::COLOR_RED));
}

// Auto size kolom untuk Surat Keluar
foreach (range('A', 'E') as $column) {
    $sheetKeluar->getColumnDimension($column)->setAutoSize(true);
}

// Border untuk semua data Surat Keluar
$sheetKeluar->getStyle('A3:E' . ($rowNum - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color(Color::COLOR_BLACK));


// Tutup koneksi database
$db->close();

// Siapkan file untuk diunduh
$filename = "Laporan_Arsip_Surat_" . date('Ymd_His') . ".xlsx"; // Ekstensi .xlsx
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output'); // Simpan langsung ke output browser
exit;
