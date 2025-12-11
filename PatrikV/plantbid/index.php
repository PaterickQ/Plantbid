<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auctions = getActiveAuctions();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>PlantBid - Aukce rostlin</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header>
        <h1>🌿 PlantBid</h1>
        <nav>
            <a href="index.php">Aukce</a>
            <a href="shop.php">Obchod</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profile.php">Můj profil</a>
                <a href="logout.php">Odhlásit se</a>
            <?php else: ?>
                <a href="login.php">Přihlášení</a>
                <a href="register.php">Registrace</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
        <h2>Aktivní aukce</h2>
        <div class="auctions">
            <?php foreach ($auctions as $auction): ?>
                <div class="card">
                    <img src="<?= htmlspecialchars($auction['image']) ?>" alt="<?= htmlspecialchars($auction['title']) ?>">
                    <h3><?= htmlspecialchars($auction['title']) ?></h3>
                    <p><strong><?= $auction['current_price'] ?> Kč</strong></p>
                    <p>Konec aukce za: <?= timeRemaining($auction['end_time']) ?></p>
                    <a href="auction.php?id=<?= $auction['id'] ?>" class="btn">Přihodit</a>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>