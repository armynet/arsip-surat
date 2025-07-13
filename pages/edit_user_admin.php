<?php
require_once __DIR__ . '/../config.php';

// Pastikan hanya superadmin yang bisa mengakses halaman ini
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'superadmin') {
    echo "<script>alert('Akses ditolak. Hanya Superadmin yang dapat mengakses halaman ini.'); window.location.href='admin.php?page=dashboard';</script>";
    exit;
}

$success = $error = "";
$user_data = null; // Untuk menyimpan data user yang akan diedit

// Ambil ID user dari parameter URL
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Ambil data user dari database
    $stmt = $db->prepare("SELECT id, username, role, nama_lengkap FROM admin WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
    } else {
        $error = "User tidak ditemukan.";
    }
    $stmt->close();
} else {
    $error = "ID user tidak disertakan.";
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $username = trim($_POST['username']);
    $role = $_POST['role'];
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $new_password = $_POST['password']; // Password baru (bisa kosong)

    if (empty($username) || empty($role)) {
        $error = "Username dan Role wajib diisi.";
    } elseif (strlen($username) < 4) {
        $error = "Username minimal 4 karakter.";
    } else {
        // Cek apakah username sudah ada dan bukan username user yang sedang diedit
        $stmt_check = $db->prepare("SELECT id FROM admin WHERE username = ? AND id != ?");
        $stmt_check->bind_param("si", $username, $user_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if($result_check->num_rows > 0) {
            $error = "Username sudah digunakan oleh user lain. Silakan pilih username lain.";
        } else {
            // Siapkan query update
            $query_parts = ["username = ?", "role = ?", "nama_lengkap = ?"];
            $params = [$username, $role, $nama_lengkap];
            $types = "sss"; // Untuk username, role, nama_lengkap

            if (!empty($new_password)) {
                if (strlen($new_password) < 6) {
                    $error = "Password baru minimal 6 karakter.";
                } else {
                    $hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $query_parts[] = "password = ?";
                    $params[] = $hash;
                    $types .= "s"; // Tambahkan 's' untuk password hash
                }
            }

            if (empty($error)) { // Lanjutkan hanya jika tidak ada error password
                $query = "UPDATE admin SET " . implode(", ", $query_parts) . " WHERE id = ?";
                $params[] = $user_id; // ID user selalu parameter terakhir
                $types .= "i"; // Tambahkan 'i' untuk user_id

                $stmt = $db->prepare($query);
                $stmt->bind_param($types, ...$params);

                if ($stmt->execute()) {
                    $success = "User '" . htmlspecialchars($username) . "' berhasil diperbarui.";
                    // Perbarui data user_data agar form menampilkan data terbaru
                    $user_data['username'] = $username;
                    $user_data['role'] = $role;
                    $user_data['nama_lengkap'] = $nama_lengkap;
                } else {
                    $error = "Gagal memperbarui user: " . $stmt->error;
                }
                $stmt->close();
            }
        }
        $stmt_check->close();
    }
}
?>

<div class="max-w-2xl mx-auto bg-white shadow-lg rounded-xl p-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
        <span class="iconify text-3xl" data-icon="mdi:account-edit-outline"></span>
        Edit User Admin
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

    <?php if ($user_data) : ?>
    <form method="post" class="space-y-5">
        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_data['id']) ?>">
        <div>
            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($user_data['username']) ?>" placeholder="Minimal 4 karakter" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500" required>
        </div>
        <div>
            <label for="nama_lengkap" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
            <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?= htmlspecialchars($user_data['nama_lengkap']) ?>" placeholder="Masukkan Nama Lengkap" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500">
        </div>
        <div>
            <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
            <select id="role" name="role" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500" required>
                <option value="admin" <?= ($user_data['role'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                <option value="superadmin" <?= ($user_data['role'] == 'superadmin') ? 'selected' : '' ?>>Super Admin</option>
            </select>
        </div>
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password Baru (kosongkan jika tidak ingin mengubah)</label>
            <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500">
        </div>
        <div class="flex justify-end pt-4 gap-4">
             <a href="admin.php?page=tambah_user_admin" class="px-6 py-2 rounded-lg text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">Batal</a>
            <button type="submit" name="update_user" class="bg-purple-600 text-white px-6 py-2 rounded-lg shadow-md hover:bg-purple-700 transition-colors flex items-center gap-2">
                <span class="iconify" data-icon="mdi:content-save-outline"></span>
                Simpan Perubahan
            </button>
        </div>
    </form>
    <?php elseif (!$error) : ?>
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4" role="alert">
            <p>Silakan pilih user yang akan diedit. Anda bisa menambahkan link 'Edit' di halaman daftar user admin.</p>
        </div>
    <?php endif; ?>
</div>er