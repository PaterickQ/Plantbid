<?php
session_start();
require_once 'includes/db.php';
$user_role = $_SESSION['role'] ?? 'user';

$error = '';

// Generuj jednoduchý příklad při načtení stránky
if (!isset($_SESSION['captcha_answer'])) {
    $a = rand(1, 10);
    $b = rand(1, 10);
    $_SESSION['captcha_question'] = "$a + $b";
    $_SESSION['captcha_answer'] = $a + $b;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"] ?? '';
    $password = $_POST["password"] ?? '';
    $captcha = $_POST["captcha"] ?? '';

    if (!$email || !$password) {
        $error = "Prosím vyplňte email i heslo.";
    } elseif ((int)$captcha !== $_SESSION['captcha_answer']) {
        $error = "Špatná odpověď na ověřovací otázku.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($user = $result->fetch_assoc()) {
            if (!empty($user['blocked'])) {
                $error = "Účet je zablokovaný. Kontaktujte administrátora.";
            } elseif (password_verify($password, $user["password_hash"])) {
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"] = $user["role"] ?? 'user';
                unset($_SESSION['captcha_answer'], $_SESSION['captcha_question']); // smaž captcha po přihlášení
                header("Location: index.php");
                exit;
            } else {
                $error = "Špatné heslo.";
            }
        } else {
            $error = "Uživatel nenalezen.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8" />
  <title>PlantBid – Přihlášení</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/style.css" />
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-success">
  <div class="container">
    <a class="navbar-brand" href="index.php">🪴 PlantBid</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <?php if (isset($_SESSION['user_id'])): ?>
          <li class="nav-item"><a class="nav-link" href="#">Přihlášen jako <?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo htmlspecialchars($user_role); ?>)</a></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">Odhlásit se</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link active" href="login.php">Přihlásit se</a></li>
          <li class="nav-item"><a class="nav-link" href="register.php">Registrovat</a></li>
        <?php endif; ?>
        <?php if (isset($_SESSION['user_id']) && $user_role === 'admin'): ?>
          <li class="nav-item"><a class="nav-link" href="admin.php">Admin</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link" href="archive.php">Archiv</a></li>
        <li class="nav-item"><a class="nav-link" href="new_auction.php">Přidat aukci</a></li>
        <li class="nav-item"><a class="nav-link" href="my_auctions.php">Moje aukce</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-5">
  <h1 class="mb-4 text-center">Přihlášení</h1>

  <?php if ($error): ?>
    <div class="alert alert-danger text-center" role="alert">
      <?php echo htmlspecialchars($error); ?>
    </div>
  <?php endif; ?>

  <form method="post" class="mx-auto" style="max-width: 400px;">
    <div class="mb-3">
      <label for="email" class="form-label">Email</label>
      <input id="email" name="email" type="email" class="form-control" required autofocus 
             value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" />
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">Heslo</label>
      <input id="password" name="password" type="password" class="form-control" required />
    </div>

    <div class="mb-3">
      <label for="captcha" class="form-label">
        Ověřovací otázka: Kolik je <?php echo $_SESSION['captcha_question']; ?>?
      </label>
      <input id="captcha" name="captcha" type="number" class="form-control" required />
    </div>

    <button type="submit" class="btn btn-success w-100">Přihlásit se</button>
  </form>

</div>

<footer class="bg-success text-white text-center py-3 mt-auto">
  <small>&copy; <?php echo date("Y"); ?> PlantBid – Všechna práva vyhrazena</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
