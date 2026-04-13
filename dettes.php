<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

$action = $_GET['action'] ?? null;
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_client = trim($_POST['nom_client'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $lieu = trim($_POST['lieu'] ?? '');
    $montant = (float)($_POST['montant'] ?? 0);
    $date_dette = $_POST['date_dette'] ?? current_date();

    if ($nom_client === '' || $telephone === '' || $lieu === '' || $montant <= 0) {
        set_flash('Veuillez remplir tous les champs correctement.', 'danger');
        redirect('dettes.php');
    }

    if (!empty($_POST['id'])) {
        $idUpdate = (int)$_POST['id'];
        $stmt = $pdo->prepare('UPDATE dettes SET nom_client = ?, telephone = ?, lieu = ?, montant = ?, date_dette = ? WHERE id = ?');
        $stmt->execute([$nom_client, $telephone, $lieu, $montant, $date_dette, $idUpdate]);
        set_flash('Dette modifiee avec succes.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO dettes (nom_client, telephone, lieu, montant, date_dette) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$nom_client, $telephone, $lieu, $montant, $date_dette]);
        set_flash('Dette ajoutee avec succes.');
    }
    redirect('dettes.php');
}

if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare('DELETE FROM dettes WHERE id = ?');
    $stmt->execute([$id]);
    set_flash('Dette supprimee.');
    redirect('dettes.php');
}

$editDette = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare('SELECT * FROM dettes WHERE id = ?');
    $stmt->execute([$id]);
    $editDette = $stmt->fetch();
}

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
$dettes = $stmt->fetchAll();

$exportQuery = http_build_query([
    'type' => 'dettes',
    'q' => $q,
    'lieu' => $lieuFilter,
    'date_from' => $date_from,
    'date_to' => $date_to,
    'min_montant' => $min_montant,
    'max_montant' => $max_montant,
]);

require __DIR__ . '/includes/header.php';
?>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title"><?php echo $editDette ? 'Modifier une dette' : 'Ajouter une dette'; ?></h5>
        <form method="post">
          <?php if ($editDette): ?>
            <input type="hidden" name="id" value="<?php echo h($editDette['id']); ?>">
          <?php endif; ?>
          <div class="mb-3">
            <label class="form-label">Nom du client</label>
            <input type="text" name="nom_client" class="form-control" value="<?php echo h($editDette['nom_client'] ?? ''); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Telephone</label>
            <input type="text" name="telephone" class="form-control" value="<?php echo h($editDette['telephone'] ?? ''); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Lieu</label>
            <input type="text" name="lieu" class="form-control" value="<?php echo h($editDette['lieu'] ?? ''); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Montant</label>
            <input type="number" step="0.01" name="montant" class="form-control" value="<?php echo h($editDette['montant'] ?? ''); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="date" name="date_dette" class="form-control" value="<?php echo h($editDette['date_dette'] ?? current_date()); ?>" required>
          </div>
          <button class="btn btn-success w-100" type="submit"><?php echo $editDette ? 'Mettre a jour' : 'Ajouter'; ?></button>
          <?php if ($editDette): ?>
            <a class="btn btn-link w-100 mt-2" href="dettes.php">Annuler</a>
          <?php endif; ?>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
          <h5 class="card-title mb-0">Liste des dettes</h5>
          <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary btn-sm" href="export.php?format=csv&<?php echo h($exportQuery); ?>">Exporter Excel</a>
            <a class="btn btn-outline-secondary btn-sm" href="report.php?<?php echo h($exportQuery); ?>" target="_blank">Exporter PDF</a>
          </div>
        </div>

        <form class="row g-2 mt-2" method="get">
          <div class="col-md-4">
            <input type="text" name="q" class="form-control" placeholder="Client ou telephone" value="<?php echo h($q); ?>">
          </div>
          <div class="col-md-3">
            <input type="text" name="lieu" class="form-control" placeholder="Lieu" value="<?php echo h($lieuFilter); ?>">
          </div>
          <div class="col-md-2">
            <input type="date" name="date_from" class="form-control" value="<?php echo h($date_from); ?>">
          </div>
          <div class="col-md-2">
            <input type="date" name="date_to" class="form-control" value="<?php echo h($date_to); ?>">
          </div>
          <div class="col-md-1">
            <input type="number" step="0.01" name="min_montant" class="form-control" placeholder="Min" value="<?php echo h($min_montant); ?>">
          </div>
          <div class="col-md-1">
            <input type="number" step="0.01" name="max_montant" class="form-control" placeholder="Max" value="<?php echo h($max_montant); ?>">
          </div>
          <div class="col-12 d-flex gap-2">
            <button class="btn btn-primary btn-sm" type="submit">Filtrer</button>
            <a class="btn btn-outline-secondary btn-sm" href="dettes.php">Reinitialiser</a>
          </div>
        </form>

        <div class="table-responsive mt-3">
          <table class="table table-striped align-middle">
            <thead>
              <tr>
                <th>ID</th>
                <th>Client</th>
                <th>Telephone</th>
                <th>Lieu</th>
                <th>Montant</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($dettes)): ?>
                <tr><td colspan="7" class="text-center text-muted">Aucune dette.</td></tr>
              <?php endif; ?>
              <?php foreach ($dettes as $dette): ?>
                <tr>
                  <td><?php echo h($dette['id']); ?></td>
                  <td><?php echo h($dette['nom_client']); ?></td>
                  <td><?php echo h($dette['telephone']); ?></td>
                  <td><?php echo h($dette['lieu']); ?></td>
                  <td><?php echo format_cfa($dette['montant']); ?></td>
                  <td><?php echo h($dette['date_dette']); ?></td>
                  <td>
                    <div class="d-grid gap-2">
                      <a class="btn btn-sm btn-outline-primary" href="dettes.php?action=edit&id=<?php echo h($dette['id']); ?>">Modifier</a>
                      <a class="btn btn-sm btn-outline-danger" href="dettes.php?action=delete&id=<?php echo h($dette['id']); ?>" onclick="return confirm('Supprimer cette dette ?');">Supprimer</a>
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
