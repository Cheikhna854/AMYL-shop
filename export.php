<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

$type = $_GET['type'] ?? '';
$format = $_GET['format'] ?? 'csv';

if (!in_array($type, ['ventes', 'dettes'], true)) {
    http_response_code(400);
    echo 'Type invalide.';
    exit;
}

if ($format !== 'csv') {
    http_response_code(400);
    echo 'Format invalide.';
    exit;
}

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

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="ventes.csv"');
    echo "\xEF\xBB\xBF"; // UTF-8 BOM for Excel

    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Produit', 'Prix unitaire', 'Quantite', 'Total', 'Date', 'Heure'], ';');
    foreach ($rows as $row) {
        fputcsv($out, [$row['id'], $row['produit'], $row['prix_unitaire'], $row['quantite'], $row['total'], $row['date_vente'], $row['heure_vente']], ';');
    }
    fclose($out);
    exit;
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

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="dettes.csv"');
    echo "\xEF\xBB\xBF"; // UTF-8 BOM for Excel

    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Client', 'Telephone', 'Lieu', 'Montant', 'Date'], ';');
    foreach ($rows as $row) {
        fputcsv($out, [$row['id'], $row['nom_client'], $row['telephone'], $row['lieu'], $row['montant'], $row['date_dette']], ';');
    }
    fclose($out);
    exit;
}