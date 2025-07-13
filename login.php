<?php
session_start();
require 'config.php'; // Pastikan file config.php ada dan berisi koneksi ke database ($db)

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: admin.php?page=dashboard");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['username']) || empty($_POST['password'])) {
        $error = "Username dan Password tidak boleh kosong!";
    } else {
        $username_input = $_POST['username']; // Ambil username dari input form
        $password_input = $_POST['password']; // Ambil password dari input form (plain text)

        // Gunakan prepared statement untuk mencegah SQL Injection
        // Ambil hash password dari database berdasarkan username
        $stmt = $db->prepare("SELECT id, username, password, role FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username_input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $stored_password_hash = $user['password']; // Ambil hash password dari database

            // VERIFIKASI PENTING: Gunakan password_verify() untuk membandingkan password
            // Ini akan bekerja baik untuk hash SHA256 lama maupun bcrypt baru
            // Jika hash di database adalah SHA256, password_verify() akan mencoba membandingkan
            // string plain text dengan hash SHA256. Jika hash di database adalah bcrypt,
            // password_verify() akan bekerja dengan benar.
            // Namun, untuk konsistensi dan keamanan terbaik, disarankan semua password di-hash dengan bcrypt.
            // Jika Anda yakin semua password lama adalah SHA256, Anda bisa melakukan pengecekan ganda.
            // Untuk skenario ini, kita akan mencoba memverifikasi dengan bcrypt terlebih dahulu,
            // dan jika gagal, coba dengan SHA256 jika hash tidak diawali '$2y$'.

            $password_verified = false;

            // Coba verifikasi dengan password_verify (untuk bcrypt)
            if (str_starts_with($stored_password_hash, '$2y$')) { // Cek apakah ini hash bcrypt
                if (password_verify($password_input, $stored_password_hash)) {
                    $password_verified = true;
                }
            } else {
                // Jika bukan hash bcrypt, asumsikan itu hash SHA256 lama
                // Hati-hati: Ini kurang aman dibandingkan bcrypt.
                // Disarankan untuk memperbarui semua password ke bcrypt.
                if (hash('sha256', $password_input) === $stored_password_hash) {
                    $password_verified = true;
                    // OPSIONAL: Jika berhasil login dengan SHA256 lama,
                    // Anda bisa langsung memperbarui hash password ke bcrypt
                    // untuk user tersebut di sini agar lebih aman di masa depan.
                    // $new_bcrypt_hash = password_hash($password_input, PASSWORD_DEFAULT);
                    // $update_stmt = $db->prepare("UPDATE admin SET password = ? WHERE id = ?");
                    // $update_stmt->bind_param("si", $new_bcrypt_hash, $user['id']);
                    // $update_stmt->execute();
                    // $update_stmt->close();
                }
            }

            if ($password_verified) {
                // Password benar, simpan data pengguna ke dalam session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];

                // Redirect ke halaman dashboard
                header("Location: admin.php?page=dashboard");
                exit; // Penting untuk menghentikan eksekusi skrip setelah redirect
            } else {
                // Password salah
                $error = "Username atau Password salah!";
            }
        } else {
            // Username tidak ditemukan
            $error = "Username atau Password salah!";
        }
        $stmt->close();
    }
}
$db->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login Admin ArsipSurat</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-image: radial-gradient(circle at 1px 1px, #cbd5e1 1px, transparent 1px);
      background-size: 16px 16px;
    }
    .login-card {
      background: linear-gradient(135deg, #ffffff 0%, #f0f4f8 100%);
    }
  </style>
</head>
<body class="bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center min-h-screen p-4">
  <div class="login-card w-full max-w-md p-8 rounded-3xl shadow-2xl text-center border border-blue-200 transform transition-all duration-300 hover:scale-[1.01]">
    <div class="flex flex-col items-center mb-8">
      <span class="iconify text-blue-600 text-6xl mb-4" data-icon="mdi:folder-file-outline"></span>
      <h2 class="text-3xl font-extrabold text-gray-800">Login Admin ArsipSurat</h2>
      <p class="text-gray-600 mt-2">Masuk untuk mengelola arsip surat Anda.</p>
    </div>

    <form method="post" action="login.php" class="space-y-6">
      <div>
        <label for="username" class="sr-only">Username</label>
        <div class="relative">
          <span class="iconify absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" data-icon="mdi:account-outline"></span>
          <input type="text" name="username" id="username" placeholder="Username" required
                 class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 text-gray-800 placeholder-gray-500 transition-all duration-200 shadow-sm">
        </div>
      </div>
      <div>
        <label for="password" class="sr-only">Password</label>
        <div class="relative">
          <span class="iconify absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" data-icon="mdi:lock-outline"></span>
          <input type="password" name="password" id="password" placeholder="Password" required
                 class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 text-gray-800 placeholder-gray-500 transition-all duration-200 shadow-sm">
        </div>
      </div>
      
      <button type="submit"
              class="w-full bg-gradient-to-r from-blue-600 to-indigo-700 text-white font-bold py-3 rounded-xl shadow-lg shadow-blue-500/30 hover:from-blue-700 hover:to-indigo-800 transition-all duration-300 transform hover:-translate-y-1">
        Login
      </button>

      <?php if (!empty($error)) : ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mt-4 flex items-center gap-2" role="alert">
          <span class="iconify text-xl" data-icon="mdi:alert-circle-outline"></span>
          <span class="block sm:inline"><?php echo $error; ?></span>
        </div>
      <?php endif; ?>
    </form>
  </div>
</body>
</html>
