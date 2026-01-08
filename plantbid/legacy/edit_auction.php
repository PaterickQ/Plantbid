<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$id = (int)($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'user';
$is_admin = $user_role === 'admin';

// Získání aukce (admin může upravit libovolnou)
if ($is_admin) {
  $stmt = $conn->prepare("SELECT * FROM auctions WHERE id = ?");
  $stmt->bind_param("i", $id);
} else {
  $stmt = $conn->prepare("SELECT * FROM auctions WHERE id = ? AND user_id = ?");
  $stmt->bind_param("ii", $id, $user_id);
}
$stmt->execute();
$result = $stmt->get_result();
$auction = $result->fetch_assoc();

if (!$auction) {
  echo "Aukce nenalezena nebo nemáte oprávnění.";
  exit;
}

// Uložení změn
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = $_POST['title'] ?? '';
  $description = $_POST['description'] ?? '';

  $image_path = $auction['image_path']; // Původní obrázek
  $upload_dir = "uploads/";

  // Zpracování nového obrázku
  if (!empty($_FILES['image']['name'])) {
    $uploaded_name = basename($_FILES['image']['name']);
    $target_path = $upload_dir . $uploaded_name;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
      $image_path = $uploaded_name;
    }
  }

  // Zpracování externí URL
  if (!empty($_POST['image_url'])) {
    $image_path = $_POST['image_url'];
  }

  if ($is_admin) {
    $stmt = $conn->prepare("UPDATE auctions SET title = ?, description = ?, image_path = ? WHERE id = ?");
    $stmt->bind_param("sssi", $title, $description, $image_path, $id);
  } else {
    $stmt = $conn->prepare("UPDATE auctions SET title = ?, description = ?, image_path = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sssii", $title, $description, $image_path, $id, $user_id);
  }
  $stmt->execute();

  header("Location: my_auctions.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <title>Upravit aukci</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/style.css" />
</head>

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

<body class="bg-light">
<div class="container py-5">
  <h2>Upravit aukci</h2>
  <form method="post" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label">Název aukce</label>
      <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($auction['title']); ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Popis</label>
      <textarea name="description" class="form-control" rows="5"><?php echo htmlspecialchars($auction['description']); ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Aktuální obrázek</label><br>
      <?php if (!empty($auction['image_path'])): ?>
        <img src="<?php echo htmlspecialchars((str_starts_with($auction['image_path'], 'http') ? $auction['image_path'] : 'uploads/' . $auction['image_path'])); ?>" alt="Obrázek aukce" class="img-fluid mb-2" style="max-height: 200px;">
      <?php else: ?>
        <p>Žádný obrázek</p>
      <?php endif; ?>
    </div>

    <div class="mb-3">
      <label class="form-label">Nový obrázek (volitelné)</label>
      <input type="file" class="form-control" name="image" accept="image/*">
    </div>

    <div class="mb-3">
      <label class="form-label">Externí URL obrázku (volitelné)</label>
      <input type="url" class="form-control" name="image_url" placeholder="https://example.com/image.jpg">
    </div>

    <button type="submit" class="btn btn-primary">Uložit změny</button>
    <a href="my_auctions.php" class="btn btn-secondary">Zpět</a>
  </form>
</div>

<footer class="bg-success text-white text-center py-3 mt-auto">
  <small>&copy; <?php echo date("Y"); ?> PlantBid – Všechna práva vyhrazena</small>
</footer>
</body>
</html>
