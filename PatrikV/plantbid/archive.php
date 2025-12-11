<?php
require_once 'includes/db.php';

$result = $conn->query("SELECT * FROM auctions WHERE end_time <= NOW() ORDER BY end_time DESC");
?>

<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8" />
  <title>Archiv aukcí – PlantBid</title>
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
          <li class="nav-item"><a class="nav-link" href="register.php">Registrovat</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link" href="archive.php">Archiv</a></li>
        <li class="nav-item"><a class="nav-link" href="new_auction.php">Přidat aukci</a></li>
		<li class="nav-item"><a class="nav-link" href="my_auctions.php">Moje aukce</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">
  <h1 class="mb-4">Archiv aukcí</h1>

  <?php if ($result->num_rows === 0): ?>
    <div class="alert alert-info">Žádné ukončené aukce.</div>
  <?php else: ?>
    <div class="row row-cols-1 row-cols-md-2 g-4">
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="col">
          <div class="card h-100 shadow-sm">
            <?php if ($row['image_path']): ?>
              <img src="uploads/<?php echo htmlspecialchars($row['image_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['title']); ?>" style="object-fit: cover; height: 200px;">
            <?php else: ?>
              <svg class="bd-placeholder-img card-img-top" width="100%" height="200" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder" preserveAspectRatio="xMidYMid slice" focusable="false"><title>Obrázek chybí</title><rect width="100%" height="100%" fill="#6c757d"></rect><text x="50%" y="50%" fill="#dee2e6" dy=".3em" text-anchor="middle">Žádný obrázek</text></svg>
            <?php endif; ?>
            <div class="card-body d-flex flex-column">
              <h5 class="card-title"><a href="auction.php?id=<?php echo $row['id']; ?>" ><?php echo htmlspecialchars($row['title']); ?></a></h5>
              <p class="card-text"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
              <small class="text-muted mt-auto">Skončila: <?php echo date('j. n. Y H:i', strtotime($row['end_time'])); ?></small>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>

  <div class="mt-4">
    <a href="index.php" class="btn btn-secondary">Zpět na hlavní stránku</a>
  </div>
</div>

<footer class="bg-success text-white text-center py-3 mt-auto">
  <small>&copy; <?php echo date("Y"); ?> PlantBid – Všechny práva vyhrazena</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
