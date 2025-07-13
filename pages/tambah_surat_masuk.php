<?php
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id'])) {
    exit('Akses ditolak.');
}

$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor_surat = $_POST['nomor_surat'];
    $tanggal = $_POST['tanggal'];
    $pengirim = $_POST['pengirim'];
    $perihal = $_POST['perihal'];

    if (!empty($_FILES['file_surat']['name'])) {
        $file_name = $_FILES['file_surat']['name'];
        $file_tmp = $_FILES['file_surat']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

        if (in_array($file_ext, $allowed)) {
            $new_name = 'masuk_' . uniqid() . '.' . $file_ext;
            $upload_dir = __DIR__ . "/../uploads/" . $new_name;

            if (move_uploaded_file($file_tmp, $upload_dir)) {
                $stmt = $db->prepare("INSERT INTO surat_masuk (nomor_surat, tanggal, pengirim, perihal, file_surat) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $nomor_surat, $tanggal, $pengirim, $perihal, $new_name);
                if ($stmt->execute()) {
                    $success = "Surat masuk berhasil ditambahkan.";
                } else {
                    $error = "Gagal menyimpan ke database.";
                }
                $stmt->close();
            } else {
                $error = "Gagal mengupload file.";
            }
        } else {
            $error = "Format file tidak didukung (hanya .pdf, .docx, .jpg, .png).";
        }
    } else {
        $error = "File surat wajib diunggah.";
    }
}
?>

<div class="max-w-2xl mx-auto bg-white shadow-lg rounded-xl p-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
        <span class="iconify text-3xl" data-icon="mdi:email-plus-outline"></span>
        Tambah Surat Masuk
    </h2>

    <?php if ($success) : ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p class="font-bold">Berhasil</p>
            <p><?= $success ?></p>
        </div>
    <?php endif; ?>
    <?php if ($error) : ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p class="font-bold">Gagal</p>
            <p><?= $error ?></p>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="space-y-5">
        <div>
            <label for="nomor_surat" class="block text-sm font-medium text-gray-700 mb-1">Nomor Surat</label>
            <input type="text" id="nomor_surat" name="nomor_surat" placeholder="Contoh: 123/ABC/XX/2023" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
        </div>
        <div>
            <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Surat</label>
            <input type="date" id="tanggal" name="tanggal" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
        </div>
        <div>
            <label for="pengirim" class="block text-sm font-medium text-gray-700 mb-1">Pengirim</label>
            <input type="text" id="pengirim" name="pengirim" placeholder="Nama instansi atau perorangan" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
        </div>
        <div>
            <label for="perihal" class="block text-sm font-medium text-gray-700 mb-1">Perihal</label>
            <textarea id="perihal" name="perihal" rows="3" placeholder="Ringkasan isi surat" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required></textarea>
        </div>
        <div>
            <label for="file_surat" class="block text-sm font-medium text-gray-700 mb-1">File Surat (PDF, DOCX, JPG, PNG)</label>
            <input type="file" id="file_surat" name="file_surat" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
        </div>
        <div class="flex justify-end pt-4 gap-4">
            <a href="admin.php?page=surat_masuk" class="px-6 py-2 rounded-lg text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">Batal</a>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg shadow-md hover:bg-blue-700 transition-colors flex items-center gap-2">
                <span class="iconify" data-icon="mdi:content-save"></span>
                Simpan
            </button>
        </div>
    </form>
</div>
