<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

$action = $_GET['action'] ?? null;
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produit = trim($_POST['produit'] ?? '');
    $prix_unitaire = (float)($_POST['prix_unitaire'] ?? 0);
    $quantite = (int)($_POST['quantite'] ?? 0);
    $total = $prix_unitaire * $quantite;

    if ($produit === '' || $prix_unitaire <= 0 || $quantite <= 0) {
        set_flash('Veuillez remplir tous les champs correctement.', 'danger');
        redirect('ventes.php');
    }

    if (!empty($_POST['id'])) {
        $idUpdate = (int)$_POST['id'];
        $stmt = $pdo->prepare('UPDATE ventes SET produit = ?, prix_unitaire = ?, quantite = ?, total = ? WHERE id = ?');
        $stmt->execute([$produit, $prix_unitaire, $quantite, $total, $idUpdate]);
        set_flash('Vente modifiee avec succes.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO ventes (produit, prix_unitaire, quantite, total, date_vente, heure_vente) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$produit, $prix_unitaire, $quantite, $total, current_date(), current_time()]);
        set_flash('Vente ajoutee avec succes.');
    }
    redirect('ventes.php');
}

if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare('DELETE FROM ventes WHERE id = ?');
    $stmt->execute([$id]);
    set_flash('Vente supprimee.');
    redirect('ventes.php');
}

$editVente = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare('SELECT * FROM ventes WHERE id = ?');
    $stmt->execute([$id]);
    $editVente = $stmt->fetch();
}

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
$ventes = $stmt->fetchAll();

$exportQuery = http_build_query([
    'type' => 'ventes',
    'q' => $q,
    'date_from' => $date_from,
    'date_to' => $date_to,
    'min_total' => $min_total,
    'max_total' => $max_total,
]);

require __DIR__ . '/includes/header.php';
?>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title"><?php echo $editVente ? 'Modifier une vente' : 'Ajouter une vente'; ?></h5>
        <form method="post">
          <?php if ($editVente): ?>
            <input type="hidden" name="id" value="<?php echo h($editVente['id']); ?>">
          <?php endif; ?>
          <div class="mb-3">
            <label class="form-label">Produit</label>
            <input type="text" name="produit" class="form-control" value="<?php echo h($editVente['produit'] ?? ''); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Prix unitaire</label>
            <input type="number" step="0.01" name="prix_unitaire" class="form-control" value="<?php echo h($editVente['prix_unitaire'] ?? ''); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Quantite</label>
            <input type="number" name="quantite" class="form-control" value="<?php echo h($editVente['quantite'] ?? ''); ?>" required>
          </div>
          <button class="btn btn-success w-100" type="submit"><?php echo $editVente ? 'Mettre a jour' : 'Ajouter'; ?></button>
          <?php if ($editVente): ?>
            <a class="btn btn-link w-100 mt-2" href="ventes.php">Annuler</a>
          <?php endif; ?>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
          <h5 class="card-title mb-0">Liste des ventes</h5>
          <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary btn-sm" href="export.php?format=csv&<?php echo h($exportQuery); ?>">Exporter Excel</a>
            <a class="btn btn-outline-secondary btn-sm" href="report.php?<?php echo h($exportQuery); ?>" target="_blank">Exporter PDF</a>
          </div>
        </div>

        <form class="row g-2 mt-2" method="get">
          <div class="col-md-4">
            <input type="text" name="q" class="form-control" placeholder="Produit" value="<?php echo h($q); ?>">
          </div>
          <div class="col-md-3">
            <input type="date" name="date_from" class="form-control" value="<?php echo h($date_from); ?>">
          </div>
          <div class="col-md-3">
            <input type="date" name="date_to" class="form-control" value="<?php echo h($date_to); ?>">
          </div>
          <div class="col-md-1">
            <input type="number" step="0.01" name="min_total" class="form-control" placeholder="Min" value="<?php echo h($min_total); ?>">
          </div>
          <div class="col-md-1">
            <input type="number" step="0.01" name="max_total" class="form-control" placeholder="Max" value="<?php echo h($max_total); ?>">
          </div>
          <div class="col-12 d-flex gap-2">
            <button class="btn btn-primary btn-sm" type="submit">Filtrer</button>
            <a class="btn btn-outline-secondary btn-sm" href="ventes.php">Reinitialiser</a>
          </div>
        </form>

        <div class="table-responsive mt-3">
          <table class="table table-striped align-middle">
            <thead>
              <tr>
                <th>ID</th>
                <th>Produit</th>
                <th>Prix unitaire</th>
                <th>Quantite</th>
                <th>Total</th>
                <th>Date</th>
                <th>Heure</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($ventes)): ?>
                <tr><td colspan="8" class="text-center text-muted">Aucune vente.</td></tr>
              <?php endif; ?>
              <?php foreach ($ventes as $vente): ?>
                <tr>
                  <td><?php echo h($vente['id']); ?></td>
                  <td><?php echo h($vente['produit']); ?></td>
                  <td><?php echo format_cfa($vente['prix_unitaire']); ?></td>
                  <td><?php echo h($vente['quantite']); ?></td>
                  <td><?php echo format_cfa($vente['total']); ?></td>
                  <td><?php echo h($vente['date_vente']); ?></td>
                  <td><?php echo h($vente['heure_vente']); ?></td>
                  <td>
                    <div class="d-grid gap-2">
                      <a class="btn btn-sm btn-outline-primary" href="ventes.php?action=edit&id=<?php echo h($vente['id']); ?>">Modifier</a>
                      <a class="btn btn-sm btn-outline-danger" href="ventes.php?action=delete&id=<?php echo h($vente['id']); ?>" onclick="return confirm('Supprimer cette vente ?');">Supprimer</a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
