<?php
require_once __DIR__ . '/../config.php';

// Cek login dari session admin.php, TIDAK PERLU panggil session_start() lagi
if (!isset($_SESSION['admin'])) {
  echo "<script>alert('Akses ditolak.');window.location.href='../login.php';</script>";
  exit;
}

// Validasi ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "<p>ID tidak valid.</p>";
  exit;
}

$id = intval($_GET['id']);

// Ambil data file
$stmt = $db->prepare("SELECT file_surat FROM surat_masuk WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  echo "<p>Data tidak ditemukan.</p>";
  exit;
}

$row = $result->fetch_assoc();
$file_path = __DIR__ . '/../uploads/' . $row['file_surat'];

// Hapus file jika ada
if (file_exists($file_path)) {
  unlink($file_path);
}

// Hapus data dari database
$stmt = $db->prepare("DELETE FROM surat_masuk WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// Pakai JavaScript redirect agar tidak kena 'headers already sent'
echo "<script>window.location.href='/surat/admin.php?page=surat_masuk';</script>";
exit;
