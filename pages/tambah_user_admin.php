<?php
require_once __DIR__ . '/../config.php';

// Cek apakah user adalah superadmin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'superadmin') {
    echo "<script>alert('Akses ditolak. Hanya Superadmin yang dapat mengakses halaman ini.'); window.location.href='admin.php?page=dashboard';</script>";
    exit;
}

$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $nama_lengkap = trim($_POST['nama_lengkap']); // Ambil nama_lengkap dari POST

    if (empty($username) || empty($password) || empty($role)) {
        $error = "Semua field wajib diisi.";
    } elseif (strlen($username) < 4) {
        $error = "Username minimal 4 karakter.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } else {
        // Gunakan hash yang lebih aman (default-nya BCRYPT)
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt_check = $db->prepare("SELECT id FROM admin WHERE username = ?");
        $stmt_check->bind_param("s", $username);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if($result_check->num_rows > 0) {
            $error = "Username sudah digunakan. Silakan pilih username lain.";
        } else {
            // Ubah query INSERT untuk menyertakan nama_lengkap
            // Pastikan urutan parameter sesuai dengan urutan placeholder
            $stmt = $db->prepare("INSERT INTO admin (username, password, role, nama_lengkap) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $hash, $role, $nama_lengkap); // Tambahkan 's' untuk nama_lengkap

            if ($stmt->execute()) {
                $success = "User admin baru ('" . htmlspecialchars($username) . "') berhasil ditambahkan.";
            } else {
                $error = "Gagal menambahkan user ke database.";
            }
            $stmt->close();
        }
        $stmt_check->close();
    }
}
?>

<div class="max-w-2xl mx-auto bg-white shadow-lg rounded-xl p-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
        <span class="iconify text-3xl" data-icon="mdi:account-plus-outline"></span>
        Tambah User Admin
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

    <form method="post" class="space-y-5">
        <div>
            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <input type="text" id="username" name="username" placeholder="Minimal 4 karakter" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500" required>
        </div>
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500" required>
        </div>
        <div>
            <label for="nama_lengkap" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
            <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Masukkan Nama Lengkap" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500">
            </div>
        <div>
            <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
            <select id="role" name="role" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500" required>
                <option value="admin">Admin</option>
                <option value="superadmin">Super Admin</option>
            </select>
        </div>
        <div class="flex justify-end pt-4 gap-4">
             <a href="admin.php?page=dashboard" class="px-6 py-2 rounded-lg text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">Kembali</a>
            <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-lg shadow-md hover:bg-purple-700 transition-colors flex items-center gap-2">
                <span class="iconify" data-icon="mdi:account-check-outline"></span>
                Tambah User
            </button>
        </div>
    </form>
</div>