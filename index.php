<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

$totalVentes = $pdo->query('SELECT COALESCE(SUM(total),0) AS total FROM ventes')->fetch()['total'];
$totalDettes = $pdo->query('SELECT COALESCE(SUM(montant),0) AS total FROM dettes')->fetch()['total'];
$nbVentes = $pdo->query('SELECT COUNT(*) AS nb FROM ventes')->fetch()['nb'];
$nbClients = $pdo->query('SELECT COUNT(DISTINCT nom_client) AS nb FROM dettes')->fetch()['nb'];

require __DIR__ . '/includes/header.php';
?>

<div class="row g-3">
  <div class="col-md-3">
    <div class="card card-stat shadow-sm">
      <div class="card-body">
        <div class="text-muted">Total ventes</div>
        <div class="fs-4 fw-bold"><?php echo format_cfa($totalVentes); ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card card-stat shadow-sm">
      <div class="card-body">
        <div class="text-muted">Total dettes</div>
        <div class="fs-4 fw-bold"><?php echo format_cfa($totalDettes); ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card card-stat shadow-sm">
      <div class="card-body">
        <div class="text-muted">Nombre de ventes</div>
        <div class="fs-4 fw-bold"><?php echo h($nbVentes); ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card card-stat shadow-sm">
      <div class="card-body">
        <div class="text-muted">Nombre de clients</div>
        <div class="fs-4 fw-bold"><?php echo h($nbClients); ?></div>
      </div>
    </div>
  </div>
</div>

<div class="card mt-4 shadow-sm hero position-relative">
  <div class="row g-0">
    <div class="col-lg-6 position-relative">
      <div class="hero-overlay"></div>
      <img src="assets/images/profil.jpg" alt="Ventes et gestion" class="h-100">
    </div>
    <div class="col-lg-6 d-flex align-items-center">
      <div class="card-body hero-content">
        <h5 class="card-title text-white">Vendez plus, suivez tout, sans effort.</h5>
        <p class="mb-0">Centralisez vos ventes et dettes en temps reel avec un suivi simple et rapide.</p>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
