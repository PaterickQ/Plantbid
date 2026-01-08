<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'user';

$sql = "SELECT * FROM auctions WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <title>Moje aukce</title>
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
  <h2 class="mb-4">Moje aukce</h2>

  <?php if ($result->num_rows > 0): ?>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Název</th>
          <th>Konec</th>
          <th>Akce</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><a href="auction.php?id=<?php echo $row['id']; ?>" ><?php echo htmlspecialchars($row['title']); ?></a></td>
            <td><?php echo date('d.m.Y H:i', strtotime($row['end_time'])); ?></td>
            <td>
              <?php if (strtotime($row['end_time']) > time()): ?>
                <a href="edit_auction.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">Upravit</a>
                <a href="delete_auction.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Opravdu chcete smazat tuto aukci?')">Smazat</a>
              <?php else: ?>
                <span class="text-muted">Aukce skončila</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>Nemáte žádné aukce.</p>
  <?php endif; ?>
</div>
<footer class="bg-success text-white text-center py-3 mt-auto">
  <small>&copy; <?php echo date("Y"); ?> PlantBid – Všechna práva vyhrazena</small>
</footer>
</body>
</html>
