<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'includes/db.php';
$user_role = $_SESSION['role'] ?? 'user';

// Kontrola přihlášení
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST["title"] ?? "";
    $description = $_POST["description"] ?? "";
    $end_time = $_POST["end_time"] ?? "";
    $starting_price = $_POST["starting_price"] ?? 0;
    $image_url = trim($_POST["image_url"] ?? "");

    // Úprava formátu end_time
    if ($end_time) {
        $end_time = str_replace("T", " ", $end_time) . ":00";
    }

    if (!$title || !$end_time || !is_numeric($starting_price) || $starting_price < 0) {
        $error = "Prosím vyplňte všechny údaje správně.";
    } else {
        $image_path = "";

        if (!empty($_FILES['image']['name'])) {
            $upload_dir = "uploads/";
            $image_path = basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_path);
            $image_url = ""; // ignoruj URL, pokud je nahrán obrázek
        } elseif (!empty($image_url)) {
            $image_path = ""; // nebude se používat
        }

        $user_id = $_SESSION['user_id'];

        // Vkládání do databáze s ohledem na oba typy obrázku
        $stmt = $conn->prepare("INSERT INTO auctions (user_id, title, description, end_time, image_path, image_url, starting_price) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssd", $user_id, $title, $description, $end_time, $image_path, $image_url, $starting_price);
        $stmt->execute();

        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8" />
  <title>Přidat novou aukci</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/style.css" />
</head>
<body class="bg-light p-3">
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
          <li class="nav-item"><a class="nav-link" href="login.php">Přihlásit se</a></li>
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

<div class="container">
  <h1>Přidat novou aukci</h1>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data">
    <div class="mb-3">
      <label for="title" class="form-label">Název aukce</label>
      <input type="text" class="form-control" id="title" name="title" required>
    </div>

    <div class="mb-3">
      <label for="description" class="form-label">Popis</label>
      <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
    </div>

    <div class="mb-3">
      <label for="end_time" class="form-label">Konec aukce</label>
      <input type="datetime-local" class="form-control" id="end_time" name="end_time" required>
    </div>

    <div class="mb-3">
      <label for="starting_price" class="form-label">Základní cena (Kč)</label>
      <input type="number" step="0.01" min="0" class="form-control" id="starting_price" name="starting_price" required>
    </div>

    <div class="mb-3">
      <label for="image" class="form-label">Obrázek (volitelné)</label>
      <input type="file" class="form-control" id="image" name="image" accept="image/*">
    </div>

    <div class="mb-3">
      <label for="image_url" class="form-label">URL obrázku (externí, volitelné)</label>
      <input type="url" class="form-control" id="image_url" name="image_url" placeholder="https://example.com/image.jpg">
    </div>

    <script>
      const fileInput = document.getElementById('image');
      const urlInput = document.getElementById('image_url');

      fileInput.addEventListener('change', () => {
        urlInput.disabled = fileInput.files.length > 0;
      });

      urlInput.addEventListener('input', () => {
        fileInput.disabled = urlInput.value.trim() !== '';
      });
    </script>

    <button type="submit" class="btn btn-success">Přidat aukci</button>
  </form>

  <a href="index.php" class="btn btn-link mt-3">Zpět na hlavní stránku</a>
</div>
</body>
</html>
