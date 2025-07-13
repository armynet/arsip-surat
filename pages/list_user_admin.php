<?php
// Memastikan file konfigurasi database di-include
require_once __DIR__ . '/../config.php';

// session_start(); // BARIS INI DIHAPUS KARENA SUDAH DIMULAI DI admin.php

// Cek apakah user adalah superadmin. Jika tidak, alihkan ke dashboard dengan pesan peringatan.
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'superadmin') {
    echo "<script>alert('Akses ditolak. Hanya Superadmin yang dapat mengakses halaman ini.'); window.location.href='admin.php?page=dashboard';</script>";
    exit;
}

$users = []; // Array untuk menyimpan data user
$error = ""; // Variabel untuk pesan error

// Mengambil semua data user admin dari database
try {
    $stmt = $db->prepare("SELECT id, username, role, nama_lengkap FROM admin");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    } else {
        $error = "Tidak ada user admin yang ditemukan.";
    }
    $stmt->close();
} catch (Exception $e) {
    $error = "Terjadi kesalahan saat mengambil data user: " . $e->getMessage();
}

// Proses penghapusan user jika ada request POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id_to_delete = $_POST['user_id'];

    // Pastikan user tidak menghapus dirinya sendiri
    if ($user_id_to_delete == $_SESSION['user_id']) {
        $error = "Anda tidak bisa menghapus akun Anda sendiri.";
    } else {
        try {
            $stmt_delete = $db->prepare("DELETE FROM admin WHERE id = ?");
            $stmt_delete->bind_param("i", $user_id_to_delete);
            if ($stmt_delete->execute()) {
                // Redirect untuk merefresh halaman dan menampilkan daftar terbaru
                echo "<script>alert('User berhasil dihapus.'); window.location.href='admin.php?page=list_user_admin';</script>";
                exit;
            } else {
                $error = "Gagal menghapus user: " . $stmt_delete->error;
            }
            $stmt_delete->close();
        } catch (Exception $e) {
            $error = "Terjadi kesalahan saat menghapus user: " . $e->getMessage();
        }
    }
}

?>

<!-- HTML untuk tampilan daftar user admin -->
<div class="max-w-4xl mx-auto bg-white shadow-lg rounded-xl p-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
        <span class="iconify text-3xl" data-icon="mdi:account-group-outline"></span>
        Daftar User Admin
    </h2>

    <!-- Menampilkan pesan error -->
    <?php if ($error) : ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md" role="alert">
            <p class="font-bold">Gagal</p>
            <p><?= $error ?></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($users)) : ?>
    <div class="overflow-x-auto rounded-lg shadow-sm border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Lengkap</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($users as $user) : ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($user['id']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($user['username']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($user['nama_lengkap'] ?? '-') ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars(ucfirst($user['role'])) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex justify-center gap-2">
                        <a href="admin.php?page=edit_user_admin&id=<?= htmlspecialchars($user['id']) ?>" class="text-indigo-600 hover:text-indigo-900 flex items-center gap-1">
                            <span class="iconify" data-icon="mdi:pencil-outline"></span> Edit
                        </a>
                        <!-- Tombol hapus, menggunakan form untuk POST request -->
                        <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?');" class="inline-block">
                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                            <button type="submit" name="delete_user" class="text-red-600 hover:text-red-900 flex items-center gap-1 ml-2">
                                <span class="iconify" data-icon="mdi:trash-can-outline"></span> Hapus
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else : ?>
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded-md" role="alert">
            <p>Belum ada user admin yang terdaftar.</p>
        </div>
    <?php endif; ?>
    
    <div class="flex justify-end mt-6">
        <a href="admin.php?page=tambah_user_admin" class="bg-blue-600 text-white px-6 py-2 rounded-lg shadow-md hover:bg-blue-700 transition-colors flex items-center gap-2">
            <span class="iconify" data-icon="mdi:account-plus-outline"></span>
            Tambah User Baru
        </a>
    </div>
</div>
