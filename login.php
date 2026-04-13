<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    if ($email === '' || $mot_de_passe === '') {
        set_flash('Veuillez saisir votre email et mot de passe.', 'danger');
        redirect('login.php');
    }

    $stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
        login_user($user);
        set_flash('Connexion reussie.');
        redirect('index.php');
    }

    set_flash('Identifiants invalides.', 'danger');
    redirect('login.php');
}

require __DIR__ . '/includes/header.php';
?>

<div class="auth-center">
  <div class="col-md-6 col-lg-4">
    <div class="card shadow-lg border-0">
      <div class="card-body p-4">
        <h4 class="card-title mb-3">Connexion</h4>
        <form method="post">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Mot de passe</label>
            <input type="password" name="mot_de_passe" class="form-control" required>
          </div>
          <button class="btn btn-primary w-100" type="submit">Se connecter</button>
          <a class="btn btn-link w-100 mt-2" href="register.php">Creer un compte</a>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
