<?php
// Memastikan file konfigurasi database di-include
require_once 'config.php';
// Memulai sesi
session_start();

// Aktifkan debugging (opsional saat pengembangan)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cek apakah pengguna sudah login atau belum
if (!isset($_SESSION['user_id'])) {
    // Jika belum, redirect ke halaman login
    header('Location: login.php');
    exit; // Pastikan untuk keluar setelah redirect
}

// Ambil parameter halaman dari URL, default-nya adalah 'dashboard'
$halaman = $_GET['page'] ?? 'dashboard';

// DITAMBAHKAN: Halaman untuk tambah dan edit surat, serta edit user
// Daftar halaman yang valid untuk mencegah include file sembarangan
// Perhatikan: download_surat_excel dan download_surat_pdf TIDAK lagi di sini
// karena mereka akan diakses langsung, bukan di-include.
$halaman_valid = [
    'dashboard',
    'surat_masuk',
    'surat_keluar',
    'tambah_user_admin',
    'tambah_surat_masuk',
    'tambah_surat_keluar',
    'edit_surat_masuk',
    'edit_surat_keluar',
    'edit_user_admin',
    'list_user_admin'
];

// Jika halaman yang diminta tidak ada dalam daftar, alihkan ke dashboard
if (!in_array($halaman, $halaman_valid)) {
    $halaman = 'dashboard';
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Panel Admin Arsip Surat</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      /* Memastikan box-sizing konsisten di seluruh elemen */
      box-sizing: border-box;
    }
    html {
        box-sizing: border-box;
    }
    *, *::before, *::after {
        box-sizing: inherit;
    }

    /* Gaya untuk sidebar di mobile */
    .sidebar-mobile-hidden {
      transform: translateX(-100%);
    }
    .sidebar-mobile-show {
      transform: translateX(0);
    }
    /* Transisi untuk sidebar */
    .sidebar-transition {
      transition: transform 0.3s ease-in-out;
    }

    /* Gaya khusus untuk header dan konten di layar kecil (mobile) */
    @media (max-width: 767px) {
        /* Header yang akan fixed di bagian atas */
        .fixed-mobile-header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%; /* Memastikan header membentang penuh lebar */
            z-index: 1000; /* Memastikan header berada di atas elemen lain */
            height: 4rem; /* Tinggi eksplisit untuk header (setara dengan h-16 Tailwind) */
            /* Properti flexbox sudah ada di HTML, ini hanya untuk memastikan */
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem; /* Set padding agar konsisten dengan p-4 */
        }

        /* Wrapper untuk konten utama yang akan memiliki padding atas */
        .mobile-main-content-wrapper {
            flex-grow: 1; /* Memastikan wrapper mengambil sisa ruang */
            padding-top: 4rem; /* Memberikan ruang di atas untuk header yang fixed */
            /* Jika konten di dalam wrapper ini sangat panjang dan melebihi viewport,
               wrapper ini akan memungkinkan scroll di dalamnya. */
            overflow-y: auto;
        }

        /* Sidebar mobile yang fixed dan dimulai di bawah header */
        .fixed-mobile-sidebar {
            position: fixed;
            top: 4rem; /* Dimulai tepat di bawah header (4rem) */
            left: 0;
            height: calc(100vh - 4rem); /* Tinggi penuh viewport dikurangi tinggi header */
            z-index: 999; /* Sedikit di bawah header tapi masih di atas konten */
            overflow-y: auto; /* Memungkinkan konten sidebar untuk di-scroll jika overflow */
        }
    }
  </style>
</head>
<body class="flex flex-col md:flex-row min-h-screen bg-gray-100 font-sans">

  <!-- Mobile Header (Visible on small screens) -->
  <header class="md:hidden bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-4 flex justify-between items-center shadow-lg fixed-mobile-header">
    <div class="text-xl font-bold">📁 ArsipSurat Admin</div>
    <button id="sidebarToggle" class="text-white focus:outline-none">
      <span class="iconify text-3xl" data-icon="mdi:menu"></span>
    </button>
  </header>

  <!-- Sidebar -->
  <aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-gradient-to-b from-blue-700 to-indigo-800 text-white shadow-lg flex-shrink-0 z-50
                              md:relative md:translate-x-0 sidebar-transition sidebar-mobile-hidden fixed-mobile-sidebar">
    <div class="p-6 text-2xl font-bold border-b border-white/20 flex items-center gap-2">
      <span class="iconify text-3xl" data-icon="mdi:folder-file-outline"></span> ArsipSurat
    </div>
    <nav class="flex flex-col gap-2 p-4">
      <a href="admin.php?page=dashboard" class="flex items-center gap-3 py-3 px-4 rounded-lg transition-all duration-200 hover:bg-white/20 <?= $halaman == 'dashboard' ? 'bg-white/30 shadow-md' : '' ?>">
        <span class="iconify text-xl" data-icon="mdi:view-dashboard-outline"></span> Dashboard
      </a>
      <a href="admin.php?page=surat_masuk" class="flex items-center gap-3 py-3 px-4 rounded-lg transition-all duration-200 hover:bg-white/20 <?= $halaman == 'surat_masuk' ? 'bg-white/30 shadow-md' : '' ?>">
        <span class="iconify text-xl" data-icon="mdi:inbox-arrow-down-outline"></span> Surat Masuk
      </a>
      <a href="admin.php?page=surat_keluar" class="flex items-center gap-3 py-3 px-4 rounded-lg transition-all duration-200 hover:bg-white/20 <?= $halaman == 'surat_keluar' ? 'bg-white/30 shadow-md' : '' ?>">
        <span class="iconify text-xl" data-icon="mdi:send-outline"></span> Surat Keluar
      </a>

      <!-- Menu khusus Superadmin -->
      <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'superadmin') : ?>
        <h3 class="text-xs uppercase font-semibold text-white/50 mt-4 mb-2 px-4">Manajemen User</h3>
        <a href="admin.php?page=tambah_user_admin" class="flex items-center gap-3 py-3 px-4 rounded-lg transition-all duration-200 hover:bg-white/20 <?= $halaman == 'tambah_user_admin' ? 'bg-white/30 shadow-md' : '' ?>">
          <span class="iconify text-xl" data-icon="mdi:account-plus-outline"></span> Tambah User Admin
        </a>
        <a href="admin.php?page=list_user_admin" class="flex items-center gap-3 py-3 px-4 rounded-lg transition-all duration-200 hover:bg-white/20 <?= $halaman == 'list_user_admin' || $halaman == 'edit_user_admin' ? 'bg-white/30 shadow-md' : '' ?>">
          <span class="iconify text-xl" data-icon="mdi:account-group-outline"></span> Kelola User Admin
        </a>
      <?php endif; ?>

      <!-- Bagian Unduh Data (bisa diakses oleh admin dan superadmin) -->
      <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'superadmin')) : ?>
        <h3 class="text-xs uppercase font-semibold text-white/50 mt-4 mb-2 px-4">Unduh Data</h3>
        <!-- BARU: Link langsung ke file PHP unduhan -->
        <a href="pages/download_surat_excel.php" class="flex items-center gap-3 py-3 px-4 rounded-lg transition-all duration-200 hover:bg-white/20">
          <span class="iconify text-xl" data-icon="mdi:file-excel-outline"></span> Unduh Excel
        </a>
        <!-- BARU: Link langsung ke file PHP unduhan -->
        <a href="pages/download_surat_pdf.php" class="flex items-center gap-3 py-3 px-4 rounded-lg transition-all duration-200 hover:bg-white/20">
          <span class="iconify text-xl" data-icon="mdi:file-pdf-box"></span> Unduh PDF
        </a>
      <?php endif; ?>

      <!-- Logout diletakkan di bagian bawah sidebar -->
      <a href="logout.php" class="flex items-center gap-3 py-3 px-4 rounded-lg transition-all duration-200 hover:bg-red-600/80 mt-auto bg-red-500/60">
        <span class="iconify text-xl" data-icon="mdi:logout"></span> Logout
      </a>
    </nav>
  </aside>

  <!-- Overlay untuk mobile sidebar -->
  <div id="sidebarOverlay" class="fixed inset-0 bg-black opacity-0 z-40 md:hidden pointer-events-none transition-opacity duration-300"></div>

  <!-- Konten Utama Wrapper untuk mobile padding -->
  <div class="flex-1 mobile-main-content-wrapper">
    <main class="p-6 md:p-8 bg-gray-100">
      <?php
      // Include file halaman sesuai dengan parameter 'page'
      // Pastikan file-file ini berada di dalam folder /pages/
      $file_halaman = __DIR__ . "/pages/$halaman.php";
      if (file_exists($file_halaman)) {
        include $file_halaman;
      } else {
        // Tampilkan pesan error jika file tidak ditemukan
        echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative shadow-md' role='alert'>";
        echo "<strong class='font-bold'>Error!</strong>";
        echo "<span class='block sm:inline'> Halaman tidak ditemukan: " . htmlspecialchars($halaman) . ". Pastikan file ada di dalam folder 'pages'.</span>";
        echo "</div>";
      }
      ?>
    </main>
  </div>

  <script>
    // JavaScript untuk toggle sidebar di mobile
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    sidebarToggle.addEventListener('click', () => {
      sidebar.classList.toggle('sidebar-mobile-hidden');
      sidebar.classList.toggle('sidebar-mobile-show');
      sidebarOverlay.classList.toggle('opacity-0');
      sidebarOverlay.classList.toggle('opacity-50');
      sidebarOverlay.classList.toggle('pointer-events-none');
    });

    sidebarOverlay.addEventListener('click', () => {
      sidebar.classList.add('sidebar-mobile-hidden');
      sidebar.classList.remove('sidebar-mobile-show');
      sidebarOverlay.classList.add('opacity-0');
      sidebarOverlay.classList.remove('opacity-50');
      sidebarOverlay.classList.add('pointer-events-none');
    });
  </script>
</body>
</html>
