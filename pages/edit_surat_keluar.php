<?php
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id'])) {
    exit('Akses ditolak.');
}

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    exit('ID tidak valid.');
}

$stmt = $db->prepare("SELECT * FROM surat_keluar WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    exit('Data tidak ditemukan.');
}
$data = $result->fetch_assoc();
$stmt->close();

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor_surat = $_POST['nomor_surat'];
    $tanggal = $_POST['tanggal'];
    $penerima = $_POST['penerima'];
    $perihal = $_POST['perihal'];
    $file_sql = "";
    $params = [$nomor_surat, $tanggal, $penerima, $perihal];
    $types = "ssss";

    if (!empty($_FILES['file_surat']['name'])) {
        $file_name = $_FILES['file_surat']['name'];
        $file_tmp = $_FILES['file_surat']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

        if (in_array($file_ext, $allowed)) {
            $new_name = 'keluar_' . uniqid() . '.' . $file_ext;
            $upload_dir = __DIR__ . "/../uploads/" . $new_name;

            if (move_uploaded_file($file_tmp, $upload_dir)) {
                $old_file_path = __DIR__ . "/../uploads/" . $data['file_surat'];
                if (file_exists($old_file_path)) {
                    unlink($old_file_path);
                }
                $file_sql = ", file_surat=?";
                $params[] = $new_name;
                $types .= "s";
            } else {
                $error = "Gagal mengupload file baru.";
            }
        } else {
            $error = "Format file tidak didukung.";
        }
    }

    if (empty($error)) {
        $params[] = $id;
        $types .= "i";
        $stmt = $db->prepare("UPDATE surat_keluar SET nomor_surat=?, tanggal=?, penerima=?, perihal=? $file_sql WHERE id=?");
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            echo "<script>alert('Data berhasil diupdate.'); window.location.href='admin.php?page=surat_keluar';</script>";
            exit;
        } else {
            $error = "Gagal mengupdate data.";
        }
        $stmt->close();
    }
}
?>

<div class="max-w-2xl mx-auto bg-white shadow-lg rounded-xl p-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
        <span class="iconify text-3xl" data-icon="mdi:file-document-edit-outline"></span>
        Edit Surat Keluar
    </h2>

    <?php if ($error) : ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p class="font-bold">Gagal</p>
            <p><?= $error ?></p>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="space-y-5">
        <div>
            <label for="nomor_surat" class="block text-sm font-medium text-gray-700 mb-1">Nomor Surat</label>
            <input type="text" id="nomor_surat" name="nomor_surat" value="<?= htmlspecialchars($data['nomor_surat']) ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring-green-500" required>
        </div>
        <div>
            <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Surat</label>
            <input type="date" id="tanggal" name="tanggal" value="<?= htmlspecialchars($data['tanggal']) ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring-green-500" required>
        </div>
        <div>
            <label for="penerima" class="block text-sm font-medium text-gray-700 mb-1">Penerima</label>
            <input type="text" id="penerima" name="penerima" value="<?= htmlspecialchars($data['penerima']) ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring-green-500" required>
        </div>
        <div>
            <label for="perihal" class="block text-sm font-medium text-gray-700 mb-1">Perihal</label>
            <textarea id="perihal" name="perihal" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring-green-500" required><?= htmlspecialchars($data['perihal']) ?></textarea>
        </div>
        <div>
            <label for="file_surat" class="block text-sm font-medium text-gray-700 mb-1">Ganti File Surat (Opsional)</label>
            <input type="file" id="file_surat" name="file_surat" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
            <p class="text-xs text-gray-500 mt-1">File saat ini: <a href="uploads/<?= htmlspecialchars($data['file_surat']) ?>" target="_blank" class="text-blue-600"><?= htmlspecialchars($data['file_surat']) ?></a>. Biarkan kosong jika tidak ingin mengganti.</p>
        </div>
        <div class="flex justify-end pt-4 gap-4">
            <a href="admin.php?page=surat_keluar" class="px-6 py-2 rounded-lg text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">Batal</a>
            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg shadow-md hover:bg-green-700 transition-colors flex items-center gap-2">
                <span class="iconify" data-icon="mdi:content-save-edit"></span>
                Update
            </button>
        </div>
    </form>
</div>
