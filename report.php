<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

$type = $_GET['type'] ?? '';
if (!in_array($type, ['ventes', 'dettes'], true)) {
    http_response_code(400);
    echo 'Type invalide.';
    exit;
}

$title = $type === 'ventes' ? 'Rapport des ventes' : 'Rapport des dettes';

if ($type === 'ventes') {
    $q = trim($_GET['q'] ?? '');
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    $min_total = $_GET['min_total'] ?? '';
    $max_total = $_GET['max_total'] ?? '';

    $conditions = [];
    $params = [];

    if ($q !== '') {
        $conditions[] = 'produit LIKE ?';
        $params[] = '%' . $q . '%';
    }
    if ($date_from !== '') {
        $conditions[] = 'date_vente >= ?';
        $params[] = $date_from;
    }
    if ($date_to !== '') {
        $conditions[] = 'date_vente <= ?';
        $params[] = $date_to;
    }
    if ($min_total !== '' && is_numeric($min_total)) {
        $conditions[] = 'total >= ?';
        $params[] = (float)$min_total;
    }
    if ($max_total !== '' && is_numeric($max_total)) {
        $conditions[] = 'total <= ?';
        $params[] = (float)$max_total;
    }

    $sql = 'SELECT * FROM ventes';
    if ($conditions) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }
    $sql .= ' ORDER BY id DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
}

if ($type === 'dettes') {
    $q = trim($_GET['q'] ?? '');
    $lieuFilter = trim($_GET['lieu'] ?? '');
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    $min_montant = $_GET['min_montant'] ?? '';
    $max_montant = $_GET['max_montant'] ?? '';

    $conditions = [];
    $params = [];

    if ($q !== '') {
        $conditions[] = '(nom_client LIKE ? OR telephone LIKE ?)';
        $params[] = '%' . $q . '%';
        $params[] = '%' . $q . '%';
    }
    if ($lieuFilter !== '') {
        $conditions[] = 'lieu LIKE ?';
        $params[] = '%' . $lieuFilter . '%';
    }
    if ($date_from !== '') {
        $conditions[] = 'date_dette >= ?';
        $params[] = $date_from;
    }
    if ($date_to !== '') {
        $conditions[] = 'date_dette <= ?';
        $params[] = $date_to;
    }
    if ($min_montant !== '' && is_numeric($min_montant)) {
        $conditions[] = 'montant >= ?';
        $params[] = (float)$min_montant;
    }
    if ($max_montant !== '' && is_numeric($max_montant)) {
        $conditions[] = 'montant <= ?';
        $params[] = (float)$max_montant;
    }

    $sql = 'SELECT * FROM dettes';
    if ($conditions) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }
    $sql .= ' ORDER BY id DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo h($title); ?></title>
  <style>
    body { font-family: Arial, sans-serif; margin: 24px; color: #111827; }
    h1 { margin: 0 0 8px; }
    .meta { color: #6b7280; margin-bottom: 16px; }
    table { width: 100%; border-collapse: collapse; font-size: 12px; }
    th, td { border: 1px solid #e5e7eb; padding: 6px 8px; text-align: left; }
    th { background: #f3f4f6; }
    .actions { margin: 16px 0; }
    @media print { .actions { display: none; } body { margin: 0; } }
  </style>
</head>
<body>
  <div class="actions">
    <button onclick="window.print()">Imprimer / Enregistrer en PDF</button>
  </div>
  <h1><?php echo h($title); ?></h1>
  <div class="meta">Genere le <?php echo date('d/m/Y H:i'); ?></div>

  <?php if ($type === 'ventes'): ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Produit</th>
          <th>Prix unitaire</th>
          <th>Quantite</th>
          <th>Total</th>
          <th>Date</th>
          <th>Heure</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
        <tr>
          <td><?php echo h($row['id']); ?></td>
          <td><?php echo h($row['produit']); ?></td>
          <td><?php echo format_cfa($row['prix_unitaire']); ?></td>
          <td><?php echo h($row['quantite']); ?></td>
          <td><?php echo format_cfa($row['total']); ?></td>
          <td><?php echo h($row['date_vente']); ?></td>
          <td><?php echo h($row['heure_vente']); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Client</th>
          <th>Telephone</th>
          <th>Lieu</th>
          <th>Montant</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
        <tr>
          <td><?php echo h($row['id']); ?></td>
          <td><?php echo h($row['nom_client']); ?></td>
          <td><?php echo h($row['telephone']); ?></td>
          <td><?php echo h($row['lieu']); ?></td>
          <td><?php echo format_cfa($row['montant']); ?></td>
          <td><?php echo h($row['date_dette']); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</body>
</html>
