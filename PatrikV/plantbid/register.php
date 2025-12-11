<?php
session_start();
require_once 'includes/db.php';

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $password = $_POST["password"] ?? '';
    $captcha = trim($_POST["captcha"] ?? '');

    if ($captcha !== '8') {
        $error = "Ověření selhalo. Napiš správný výsledek příkladu.";
    } elseif (!$username || !$email || !$password) {
        $error = "Prosím vyplňte všechna pole.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Zadejte platný email.";
    } else {
        // ... zbytek zůstává stejný

        // Zkontrolujeme, jestli uživatel s emailem nebo jménem už neexistuje
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Uživatel s tímto emailem nebo uživatelským jménem již existuje.";
        } else {
            // Vložíme nového uživatele
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $password_hash);
            if ($stmt->execute()) {
                header("Location: login.php");
                exit;
            } else {
                $error = "Registrace selhala, zkuste to prosím později.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8" />
  <title>PlantBid – Registrace</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/style.css" />
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-success">
  <div class="container">
    <a class="navbar-brand" href="index.php">🌿 PlantBid</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <?php if (isset($_SESSION['user_id'])): ?>
          <li class="nav-item"><a class="nav-link" href="#">Přihlášen jako <?php echo htmlspecialchars($_SESSION['username']); ?></a></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">Odhlásit se</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="login.php">Přihlásit se</a></li>
          <li class="nav-item"><a class="nav-link active" href="register.php">Registrovat</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link" href="archive.php">Archiv</a></li>
        <li class="nav-item"><a class="nav-link" href="new_auction.php">Přidat aukci</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-5">
  <h1 class="mb-4 text-center">Registrace</h1>

  <?php if ($error): ?>
    <div class="alert alert-danger text-center" role="alert">
      <?php echo htmlspecialchars($error); ?>
    </div>
  <?php endif; ?>

  <form method="post" class="mx-auto" style="max-width: 400px;">
    <div class="mb-3">
      <label for="username" class="form-label">Uživatelské jméno</label>
      <input id="username" name="username" type="text" class="form-control" required autofocus
             value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" />
    </div>

    <div class="mb-3">
      <label for="email" class="form-label">Email</label>
      <input id="email" name="email" type="email" class="form-control" required
             value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" />
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">Heslo</label>
      <input id="password" name="password" type="password" class="form-control" required />
	  <div class="mb-3">
  <label for="captcha" class="form-label">Kolik je 3 + 5?</label>
  <input id="captcha" name="captcha" type="text" class="form-control" required />
</div>

    </div>

    <button type="submit" class="btn btn-success w-100">Registrovat</button>
  </form>
  
</div>


<footer class="bg-success text-white text-center py-3 mt-auto">
  <small>&copy; <?php echo date("Y"); ?> PlantBid – Všechny práva vyhrazena</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
