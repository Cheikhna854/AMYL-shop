<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
$flash = get_flash();
$user = $_SESSION['user'] ?? null;
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AMYL-shop - Gestion des ventes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="index.php">
      <img src="assets/images/Capture d'écran 2025-05-20 104132.png" alt="MbayeNov" style="height:40px;width:auto;">
      <span class="text-white">AMYL-shop</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <?php if (is_logged_in()): ?>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="ventes.php">Ventes</a></li>
        <li class="nav-item"><a class="nav-link" href="dettes.php">Dettes</a></li>
        <li class="nav-item"><a class="nav-link" href="stats.php">Statistiques</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Deconnexion</a></li>
      </ul>
      <?php endif; ?>
    </div>
  </div>
</nav>

<main class="container my-4">
<?php if ($flash): ?>
  <div class="alert alert-<?php echo h($flash['type']); ?>">
    <?php echo h($flash['message']); ?>
  </div>
<?php endif; ?>
