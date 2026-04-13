<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

$year = (int)date('Y');

$ventesData = array_fill(1, 12, 0.0);
$dettesData = array_fill(1, 12, 0.0);

$stmt = $pdo->prepare('SELECT MONTH(date_vente) AS m, SUM(total) AS total FROM ventes WHERE YEAR(date_vente) = ? GROUP BY MONTH(date_vente)');
$stmt->execute([$year]);
foreach ($stmt->fetchAll() as $row) {
    $ventesData[(int)$row['m']] = (float)$row['total'];
}

$stmt = $pdo->prepare('SELECT MONTH(date_dette) AS m, SUM(montant) AS total FROM dettes WHERE YEAR(date_dette) = ? GROUP BY MONTH(date_dette)');
$stmt->execute([$year]);
foreach ($stmt->fetchAll() as $row) {
    $dettesData[(int)$row['m']] = (float)$row['total'];
}

$totalVentesAnnee = array_sum($ventesData);
$pourcentageMensuel = [];
for ($i = 1; $i <= 12; $i++) {
    $pourcentageMensuel[] = $totalVentesAnnee > 0 ? round(($ventesData[$i] / $totalVentesAnnee) * 100, 2) : 0;
}

$labels = ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aou', 'Sep', 'Oct', 'Nov', 'Dec'];

require __DIR__ . '/includes/header.php';
?>

<div class="row g-4">
  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title">Ventes par mois (<?php echo h($year); ?>)</h5>
        <canvas id="chartVentes" height="120"></canvas>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title">Dettes par mois (<?php echo h($year); ?>)</h5>
        <canvas id="chartDettes" height="120"></canvas>
      </div>
    </div>
  </div>
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title">Comparaison ventes vs dettes</h5>
        <canvas id="chartComparaison" height="90"></canvas>
      </div>
    </div>
  </div>
</div>

<div class="card mt-4 shadow-sm">
  <div class="card-body">
    <h5 class="card-title">Pourcentage mensuel des ventes</h5>
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Mois</th>
            <th>Ventes</th>
            <th>Pourcentage</th>
          </tr>
        </thead>
        <tbody>
          <?php for ($i = 1; $i <= 12; $i++): ?>
          <tr>
            <td><?php echo h($labels[$i - 1]); ?></td>
            <td><?php echo format_cfa($ventesData[$i]); ?></td>
            <td><?php echo h($pourcentageMensuel[$i - 1]); ?>%</td>
          </tr>
          <?php endfor; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
const labels = <?php echo json_encode($labels); ?>;
const ventesData = <?php echo json_encode(array_values($ventesData)); ?>;
const dettesData = <?php echo json_encode(array_values($dettesData)); ?>;

if (typeof renderStatsCharts === 'function') {
  renderStatsCharts(labels, ventesData, dettesData);
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
