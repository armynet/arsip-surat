<?php
session_start();
session_unset();
session_destroy();

// Redirect ke halaman index (publik)
header("Location: index.php");
exit;
