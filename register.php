<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $mot_de_passe_confirm = $_POST['mot_de_passe_confirm'] ?? '';

    if ($nom === '' || $email === '' || $mot_de_passe === '' || $mot_de_passe_confirm === '') {
        set_flash('Veuillez remplir tous les champs.', 'danger');
        redirect('register.php');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash('Adresse email invalide.', 'danger');
        redirect('register.php');
    }

    if ($mot_de_passe !== $mot_de_passe_confirm) {
        set_flash('Les mots de passe ne correspondent pas.', 'danger');
        redirect('register.php');
    }

    if (strlen($mot_de_passe) < 6) {
        set_flash('Le mot de passe doit contenir au moins 6 caracteres.', 'danger');
        redirect('register.php');
    }

    $stmt = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        set_flash('Un compte existe deja avec cet email.', 'danger');
        redirect('register.php');
    }

    $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO utilisateurs (nom, email, mot_de_passe) VALUES (?, ?, ?)');
    $stmt->execute([$nom, $email, $hash]);

    set_flash('Compte cree avec succes. Vous pouvez vous connecter.');
    redirect('login.php');
}

require __DIR__ . '/includes/header.php';
?>

<div class="auth-center">
  <div class="col-md-6 col-lg-4">
    <div class="card shadow-lg border-0">
      <div class="card-body p-4">
        <h4 class="card-title mb-3">Inscription</h4>
        <form method="post">
          <div class="mb-3">
            <label class="form-label">Nom</label>
            <input type="text" name="nom" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Mot de passe</label>
            <input type="password" name="mot_de_passe" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Confirmer le mot de passe</label>
            <input type="password" name="mot_de_passe_confirm" class="form-control" required>
          </div>
          <button class="btn btn-success w-100" type="submit">Creer un compte</button>
          <a class="btn btn-link w-100 mt-2" href="login.php">Deja un compte ? Se connecter</a>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
