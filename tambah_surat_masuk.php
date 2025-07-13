<?php
session_start();
require_once 'config.php';

// Pastikan hanya admin
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor_surat = $_POST['nomor_surat'];
    $tanggal = $_POST['tanggal'];
    $pengirim = $_POST['pengirim'];
    $perihal = $_POST['perihal'];

    // Upload file
    $file_name = $_FILES['file_surat']['name'];
    $file_tmp = $_FILES['file_surat']['tmp_name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

    if (!in_array($file_ext, $allowed)) {
        $error = "Format file tidak didukung.";
    } else {
        $new_name = uniqid('surat_') . '.' . $file_ext;
        $upload_dir = "uploads/" . $new_name;

        if (move_uploaded_file($file_tmp, $upload_dir)) {
            $stmt = $db->prepare("INSERT INTO surat_masuk (nomor_surat, tanggal, pengirim, perihal, file_surat) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nomor_surat, $tanggal, $pengirim, $perihal, $new_name);

            if ($stmt->execute()) {
                $success = "Surat berhasil ditambahkan.";
            } else {
                $error = "Gagal menyimpan ke database.";
            }
        } else {
            $error = "Gagal mengupload file.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah Surat Masuk</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <div class="max-w-2xl mx-auto mt-10 bg-white shadow-md rounded-xl p-8">
    <h2 class="text-2xl font-bold mb-6">📥 Tambah Surat Masuk</h2>

    <?php if ($success): ?>
      <div class="bg-green-100 text-green-700 p-3 rounded mb-4"><?= $success ?></div>
    <?php elseif ($error): ?>
      <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= $error ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="space-y-4">
      <input type="text" name="nomor_surat" placeholder="Nomor Surat" class="w-full border p-2 rounded" required>
      <input type="date" name="tanggal" class="w-full border p-2 rounded" required>
      <input type="text" name="pengirim" placeholder="Pengirim" class="w-full border p-2 rounded" required>
      <input type="text" name="perihal" placeholder="Perihal" class="w-full border p-2 rounded" required>
      
      <input type="file" name="file_surat" class="w-full border p-2 rounded bg-white" required>
      
      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Simpan Surat</button>
      <a href="surat_masuk.php" class="ml-3 text-blue-600 hover:underline">← Kembali</a>
    </form>
  </div>
</body>
</html>
