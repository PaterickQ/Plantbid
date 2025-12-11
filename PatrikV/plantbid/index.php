<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'includes/db.php';

$user_role = $_SESSION['role'] ?? 'user';

$sql = "
SELECT a.*, 
       u.username, 
       COALESCE(MAX(b.amount), a.starting_price) AS current_price
FROM auctions a
JOIN users u ON a.user_id = u.id
LEFT JOIN bids b ON a.id = b.auction_id
WHERE a.end_time > NOW()
GROUP BY a.id
ORDER BY a.end_time ASC
";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8" />
  <title>PlantBid – Aukce rostlin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/style.css" />
  <style>
    .auction-image {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-top-left-radius: .5rem;
      border-top-right-radius: .5rem;
    }
  </style>
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

<div class="container py-5">
  <h1 class="mb-4 text-center">Aktuální aukce rostlin</h1>

  <?php if ($result->num_rows > 0): ?>
    <div class="row">
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="col-md-4 mb-4">
          <div class="card h-100 shadow-sm">
            <?php 
              if (!empty($row['image_path'])) {
                // Pokud je nahraný obrázek, zobraz z uploads/
                $imgSrc = "uploads/" . htmlspecialchars($row['image_path']);
              } elseif (!empty($row['image_url'])) {
                // Pokud je externí URL, zobraz ji
                $imgSrc = htmlspecialchars($row['image_url']);
              } else {
                // Jinak zobraz výchozí obrázek
                $imgSrc = "nophoto.png";
              }
            ?>
            <img src="<?php echo $imgSrc; ?>" class="auction-image" alt="Obrázek rostliny" />

            <div class="card-body d-flex flex-column">
              <h5 class="card-title">
                <a href="auction.php?id=<?php echo $row['id']; ?>">
                  <?php echo htmlspecialchars($row['title']); ?>
                </a>
              </h5>
              <p class="card-text truncate-description" data-fulltext="<?php echo htmlspecialchars($row['description']); ?>">
                <?php echo nl2br(htmlspecialchars($row['description'])); ?>
              </p>
              <p class="mt-auto fw-bold">
                Aktuální cena: <?php echo number_format($row['current_price'], 2, ',', ' '); ?> Kč
              </p>
              <p class="countdown mt-auto text-danger fw-bold" data-endtime="<?php echo $row['end_time']; ?>">
                Načítání odpočtu...
              </p>
              <p class="text-muted">Přidal: <?php echo htmlspecialchars($row['username']); ?></p>
            </div>
            <div class="card-footer text-muted d-flex justify-content-between align-items-center">
              <small>Končí: <?php echo date('d.m.Y H:i', strtotime($row['end_time'])); ?></small>
              <a href="auction.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success">Zobrazit aukci</a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <p class="text-muted text-center">Žádné aktivní aukce zatím nejsou.</p>
  <?php endif; ?>
</div>

<footer class="bg-success text-white text-center py-3 mt-auto">
  <small>&copy; <?php echo date("Y"); ?> PlantBid – Všechny práva vyhrazena</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function updateCountdowns() {
    const countdowns = document.querySelectorAll('.countdown');
    const now = new Date();

    countdowns.forEach(elem => {
      const endTime = new Date(elem.getAttribute('data-endtime'));
      const diff = endTime - now;

      if (diff <= 0) {
        elem.textContent = 'Aukce skončila';
        elem.classList.remove('text-danger');
        elem.classList.add('text-secondary');
        return;
      }

      const hours = Math.floor(diff / 1000 / 60 / 60);
      const minutes = Math.floor((diff / 1000 / 60) % 60);
      const seconds = Math.floor((diff / 1000) % 60);

      elem.textContent = `Končí za ${hours}h ${minutes}m ${seconds}s`;
    });
  }

  setInterval(updateCountdowns, 1000);
  updateCountdowns();
</script>
</body>
</html>
