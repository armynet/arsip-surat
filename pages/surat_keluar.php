<?php
// Diasumsikan file config.php sudah di-include dari admin.php
// require_once 'config.php';

// Pastikan session sudah dimulai dan user telah login (dicek di admin.php)
if (!isset($_SESSION['user_id'])) {
    exit('Akses ditolak.');
}

// Ambil data surat keluar dari database
$data_surat_keluar = $db->query("SELECT * FROM surat_keluar ORDER BY tanggal DESC");

// Logika untuk hapus data
if (isset($_GET['action']) && $_GET['action'] == 'hapus' && isset($_GET['id'])) {
    $id_hapus = $_GET['id'];
     // Hapus file terkait jika ada
    $file_stmt = $db->prepare("SELECT file_surat FROM surat_keluar WHERE id = ?");
    $file_stmt->bind_param("i", $id_hapus);
    $file_stmt->execute();
    $file_res = $file_stmt->get_result();
    if($file_row = $file_res->fetch_assoc()){
        $filepath = __DIR__ . '/../uploads/' . $file_row['file_surat'];
        if(file_exists($filepath)){
            unlink($filepath);
        }
    }
    $file_stmt->close();
    
    // Hapus data dari database
    $stmt = $db->prepare("DELETE FROM surat_keluar WHERE id = ?");
    $stmt->bind_param("i", $id_hapus);
    if ($stmt->execute()) {
        // Menggunakan parameter URL untuk pesan sukses, akan ditangani oleh JavaScript
        header('Location: admin.php?page=surat_keluar&status=success_delete');
        exit;
    } else {
        // Menggunakan parameter URL untuk pesan error
        header('Location: admin.php?page=surat_keluar&status=error_delete');
        exit;
    }
    $stmt->close();
}
?>

<div class="bg-white rounded-2xl shadow-xl p-8 border border-green-100">
    <div class="flex flex-col sm:flex-row items-center justify-between mb-8">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3 mb-4 sm:mb-0">
            <span class="iconify text-green-600 text-4xl" data-icon="mdi:send-outline"></span>
            Data Surat Keluar
        </h1>
        <a href="admin.php?page=tambah_surat_keluar" class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-3 rounded-full shadow-lg hover:from-green-600 hover:to-emerald-700 transition-all duration-300 flex items-center gap-2 text-lg font-semibold">
            <span class="iconify text-xl" data-icon="mdi:plus-circle-outline"></span>
            Tambah Surat
        </a>
    </div>

    <!-- Message Display Area -->
    <div id="message-box" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
        <strong class="font-bold">Sukses!</strong>
        <span class="block sm:inline" id="message-text"></span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" onclick="document.getElementById('message-box').classList.add('hidden')">
            <span class="iconify" data-icon="mdi:close"></span>
        </span>
    </div>
    <div id="error-box" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
        <strong class="font-bold">Error!</strong>
        <span class="block sm:inline" id="error-text"></span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" onclick="document.getElementById('error-box').classList.add('hidden')">
            <span class="iconify" data-icon="mdi:close"></span>
        </span>
    </div>

    <div class="overflow-x-auto rounded-lg shadow-md">
        <table class="w-full text-base text-left text-gray-700">
            <thead class="bg-green-100 text-green-800 uppercase text-sm">
                <tr>
                    <th class="py-4 px-6 rounded-tl-lg">No</th>
                    <th class="py-4 px-6">Nomor Surat</th>
                    <th class="py-4 px-6">Tanggal</th>
                    <th class="py-4 px-6">Penerima</th>
                    <th class="py-4 px-6">Perihal</th>
                    <th class="py-4 px-6 text-center">File</th>
                    <th class="py-4 px-6 text-center rounded-tr-lg">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-800 divide-y divide-gray-200">
                <?php if ($data_surat_keluar && $data_surat_keluar->num_rows > 0) : ?>
                    <?php $no = 1; ?>
                    <?php while ($row = $data_surat_keluar->fetch_assoc()) : ?>
                        <tr class="hover:bg-green-50/50 transition-colors duration-200">
                            <td class="py-3 px-6"><?= $no++; ?></td>
                            <td class="py-3 px-6 font-medium whitespace-nowrap"><?= htmlspecialchars($row['nomor_surat']); ?></td>
                            <td class="py-3 px-6 whitespace-nowrap"><?= date('d F Y', strtotime($row['tanggal'])); ?></td>
                            <td class="py-3 px-6"><?= htmlspecialchars($row['penerima']); ?></td>
                            <td class="py-3 px-6"><?= htmlspecialchars($row['perihal']); ?></td>
                            <td class="py-3 px-6 text-center">
                                <a href="uploads/<?= htmlspecialchars($row['file_surat']); ?>" target="_blank" class="bg-gradient-to-r from-green-500 to-emerald-500 text-white px-4 py-2 rounded-full text-sm font-semibold shadow-md hover:from-green-600 hover:to-emerald-600 transition-all transform hover:scale-105">
                                    Lihat
                                </a>
                            </td>
                            <td class="py-3 px-6 text-center flex items-center justify-center gap-3">
                                <a href="admin.php?page=edit_surat_keluar&id=<?= $row['id']; ?>" class="text-yellow-500 hover:text-yellow-700 transition-colors duration-200" title="Edit">
                                    <span class="iconify text-5xl" data-icon="mdi:pencil-circle-outline"></span>
                                </a>
                                <button onclick="confirmDelete(<?= $row['id']; ?>, '<?= htmlspecialchars($row['nomor_surat']); ?>')" class="text-red-500 hover:text-red-700 transition-colors duration-200" title="Hapus">
                                    <span class="iconify text-5xl" data-icon="mdi:trash-can-circle-outline"></span>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7" class="text-center text-gray-500 py-10 text-lg">Tidak ada data surat keluar.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Custom Confirmation Modal -->
<div id="confirmation-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-8 rounded-lg shadow-xl max-w-sm w-full mx-4">
        <h3 class="text-xl font-bold text-gray-800 mb-4" id="modal-title">Konfirmasi Hapus</h3>
        <p class="text-gray-700 mb-6" id="modal-message">Apakah Anda yakin ingin menghapus data ini?</p>
        <div class="flex justify-end gap-3">
            <button id="cancel-button" class="bg-gray-300 text-gray-800 px-5 py-2 rounded-lg hover:bg-gray-400 transition-colors">Batal</button>
            <button id="confirm-button" class="bg-red-600 text-white px-5 py-2 rounded-lg hover:bg-red-700 transition-colors">Hapus</button>
        </div>
    </div>
</div>

<script>
    // Function to display messages from URL parameters
    window.onload = function() {
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        const messageBox = document.getElementById('message-box');
        const messageText = document.getElementById('message-text');
        const errorBox = document.getElementById('error-box');
        const errorText = document.getElementById('error-text');

        if (status === 'success_delete') {
            messageText.textContent = 'Data berhasil dihapus.';
            messageBox.classList.remove('hidden');
        } else if (status === 'error_delete') {
            errorText.textContent = 'Gagal menghapus data.';
            errorBox.classList.remove('hidden');
        } else if (status === 'success_add') {
            messageText.textContent = 'Data berhasil ditambahkan.';
            messageBox.classList.remove('hidden');
        } else if (status === 'error_add') {
            errorText.textContent = 'Gagal menambahkan data.';
            errorBox.classList.remove('hidden');
        } else if (status === 'success_edit') {
            messageText.textContent = 'Data berhasil diperbarui.';
            messageBox.classList.remove('hidden');
        } else if (status === 'error_edit') {
            errorText.textContent = 'Gagal memperbarui data.';
            errorBox.classList.remove('hidden');
        }

        // Clear URL parameters after displaying message
        if (status) {
            const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + window.location.hash;
            window.history.replaceState({path: newUrl}, '', newUrl);
        }
    };

    // Custom confirmation modal logic
    let currentDeleteId = null;
    const modal = document.getElementById('confirmation-modal');
    const modalTitle = document.getElementById('modal-title');
    const modalMessage = document.getElementById('modal-message');
    const confirmBtn = document.getElementById('confirm-button');
    const cancelBtn = document.getElementById('cancel-button');

    function confirmDelete(id, nomorSurat) {
        currentDeleteId = id;
        modalTitle.textContent = 'Konfirmasi Hapus Surat';
        modalMessage.innerHTML = `Apakah Anda yakin ingin menghapus surat dengan nomor <strong>${nomorSurat}</strong>?`;
        modal.classList.remove('hidden');
    }

    confirmBtn.addEventListener('click', () => {
        if (currentDeleteId) {
            window.location.href = `admin.php?page=surat_keluar&action=hapus&id=${currentDeleteId}`;
        }
    });

    cancelBtn.addEventListener('click', () => {
        modal.classList.add('hidden');
        currentDeleteId = null;
    });

    // Close modal if clicked outside
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.add('hidden');
            currentDeleteId = null;
        }
    });
</script>
