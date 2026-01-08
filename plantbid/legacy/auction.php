<?php
session_start();
require_once 'includes/db.php';

// Kontrola parametru aukce
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$auction_id = (int)$_GET['id'];
$user_role = $_SESSION['role'] ?? 'user';
$bid_error = '';
$comment_error = '';

// Akce: komentare, mazani komentaru (admin), prihazovani
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Pridani komentare
    if (isset($_POST['comment_content'])) {
        if (!isset($_SESSION['user_id'])) {
            $comment_error = "Pro pridani komentare se musite prihlasit.";
        } else {
            $comment = trim($_POST['comment_content']);
            $word_count = str_word_count($comment, 0, "ÁáČčĎďÉéĚěÍíŇňÓóŘřŠšŤťÚúŮůÝýŽž");
            if ($comment === '') {
                $comment_error = "Komentar nesmi byt prazdny.";
            } elseif ($word_count > 100) {
                $comment_error = "Komentar muze mit maximalne 100 slov (nyni $word_count).";
            } else {
                $stmt = $conn->prepare("INSERT INTO comments (auction_id, user_id, content) VALUES (?, ?, ?)");
                $stmt->bind_param("iis", $auction_id, $_SESSION['user_id'], $comment);
                $stmt->execute();
                header("Location: auction.php?id=" . $auction_id);
                exit;
            }
        }
    }
    // Smazani komentare (jen admin)
    elseif (isset($_POST['delete_comment_id']) && $user_role === 'admin') {
        $delete_id = (int)$_POST['delete_comment_id'];
        $stmt = $conn->prepare("DELETE FROM comments WHERE id = ? AND auction_id = ?");
        $stmt->bind_param("ii", $delete_id, $auction_id);
        $stmt->execute();
        header("Location: auction.php?id=" . $auction_id);
        exit;
    }
    // Prihozeni
    elseif (isset($_POST['bid_amount'])) {
        if (!isset($_SESSION['user_id'])) {
            echo "Pro prihazovani musite byt přihlášeni.";
            exit;
        }
        $bid_amount = floatval($_POST['bid_amount']);
        $user_id = $_SESSION['user_id'];

        // Zjistit aktualni nejvyssi prihoz
        $stmt = $conn->prepare("SELECT MAX(amount) AS max_bid FROM bids WHERE auction_id = ?");
        $stmt->bind_param("i", $auction_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $max_bid_row = $res->fetch_assoc();
        $current_max = $max_bid_row['max_bid'];

        if ($current_max === null) {
            $stmt2 = $conn->prepare("SELECT starting_price FROM auctions WHERE id = ?");
            $stmt2->bind_param("i", $auction_id);
            $stmt2->execute();
            $res2 = $stmt2->get_result();
            $row2 = $res2->fetch_assoc();
            $current_max = $row2['starting_price'];
        }

        if ($bid_amount <= $current_max) {
            $bid_error = "Prihoz musi byt vyssi nez aktualne nejvyssi cena (" . number_format($current_max, 2, ',', ' ') . " Kc).";
        } else {
            $stmt3 = $conn->prepare("INSERT INTO bids (auction_id, user_id, amount, bid_time) VALUES (?, ?, ?, NOW())");
            $stmt3->bind_param("iid", $auction_id, $user_id, $bid_amount);
            $stmt3->execute();
            header("Location: auction.php?id=" . $auction_id);
            exit;
        }
    }
}

// Detail aukce
$stmt = $conn->prepare("
    SELECT a.*, u.username 
    FROM auctions a 
    JOIN users u ON a.user_id = u.id 
    WHERE a.id = ?
");
$stmt->bind_param("i", $auction_id);
$stmt->execute();
$auction = $stmt->get_result()->fetch_assoc();

if (!$auction) {
    echo "Aukce nenalezena.";
    exit;
}

// Aktualni nejvyssi prihoz
$stmt = $conn->prepare("SELECT MAX(amount) AS max_bid FROM bids WHERE auction_id = ?");
$stmt->bind_param("i", $auction_id);
$stmt->execute();
$res = $stmt->get_result();
$max_bid_row = $res->fetch_assoc();
$current_max = $max_bid_row['max_bid'];
if ($current_max === null) {
    $current_max = $auction['starting_price'];
}

// Historie prihozu
$stmt = $conn->prepare("
    SELECT b.amount, b.bid_time, u.username 
    FROM bids b 
    JOIN users u ON b.user_id = u.id 
    WHERE b.auction_id = ? 
    ORDER BY b.bid_time DESC
");
$stmt->bind_param("i", $auction_id);
$stmt->execute();
$bids_result = $stmt->get_result();

// Komentare
$stmt = $conn->prepare("
    SELECT c.id, c.content, c.created_at, u.username
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.auction_id = ?
    ORDER BY c.created_at DESC
");
$stmt->bind_param("i", $auction_id);
$stmt->execute();
$comments_result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8" />
  <title>PlantBid - Aukce: <?php echo htmlspecialchars($auction['title']); ?></title>
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

<div class="container py-5">
  <h1 class="mb-4"><?php echo htmlspecialchars($auction['title']); ?></h1>

  <div class="row mb-4">
    <div class="col-md-6">
      <?php
        $img = !empty($auction['image_path']) ? $auction['image_path'] : 'nophoto.png';
        if (!file_exists(__DIR__ . '/uploads/' . $img)) {
            $img = 'nophoto.png';
        }
      ?>
      <img src="uploads/<?php echo htmlspecialchars($img); ?>" class="img-fluid rounded shadow" alt="Obrazek aukce" style="max-height:400px; width:auto;">
    </div>
    <div class="col-md-6">
      <p><?php echo nl2br(htmlspecialchars($auction['description'])); ?></p>
      <p><strong>Startovní cena:</strong> <?php echo number_format($auction['starting_price'], 2, ',', ' '); ?> Kc</p>
      <p><strong>Aktuálně nejvyšší příhoz:</strong> <?php echo number_format($current_max, 2, ',', ' '); ?> Kc</p>
      <p><strong>Konec aukce:</strong> <?php echo date('d.m.Y H:i', strtotime($auction['end_time'])); ?></p>
      <p class="text-muted"><strong>Aukci přidal:</strong> <?php echo htmlspecialchars($auction['username']); ?></p>

      <?php if ($bid_error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($bid_error); ?></div>
      <?php endif; ?>

      <?php if (isset($_SESSION['user_id'])): ?>
        <?php if (strtotime($auction['end_time']) > time()): ?>
          <form method="post" class="mt-3">
            <div class="mb-3">
              <label for="bid_amount" class="form-label">Vaše nabídka (v Kč):</label>
              <input type="number" step="1" min="<?php echo $current_max + 10; ?>" class="form-control" id="bid_amount" name="bid_amount" required>
              <div class="form-text">Minimální možná nabídka: <?php echo number_format($current_max + 10, 0, ',', ' '); ?> Kc</div>
            </div>
            <button type="submit" class="btn btn-success">Přihodit</button>
          </form>
        <?php else: ?>
          <div class="alert alert-info mt-3">Tato aukce již skončila.</div>
        <?php endif; ?>
      <?php else: ?>
        <p class="mt-3">Pro přihazování se musíte <a href="login.php">přihlásit</a>.</p>
      <?php endif; ?>
    </div>
  </div>

  <h3>Historie příhozů</h3>
  <?php if ($bids_result->num_rows > 0): ?>
    <ul class="list-group mb-5">
      <?php while ($bid = $bids_result->fetch_assoc()): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <div><strong><?php echo htmlspecialchars($bid['username']); ?></strong> přihodil <?php echo number_format($bid['amount'], 2, ',', ' '); ?> Kc</div>
          <small class="text-muted"><?php echo date('d.m.Y H:i', strtotime($bid['bid_time'])); ?></small>
        </li>
      <?php endwhile; ?>
    </ul>
  <?php else: ?>
    <p>Pro tuto aukci zatím nejsou žádné příhozy.</p>
  <?php endif; ?>

  <h3 class="mt-5">Komentáře</h3>
  <?php if ($comment_error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($comment_error); ?></div>
  <?php endif; ?>

  <?php if (isset($_SESSION['user_id'])): ?>
    <form method="post" class="mb-4">
      <div class="mb-3">
        <label for="comment_content" class="form-label">Přidat komentář (max 100 slov)</label>
        <textarea id="comment_content" name="comment_content" class="form-control" rows="3" required></textarea>
      </div>
      <button type="submit" class="btn btn-outline-success">Odeslat</button>
    </form>
  <?php else: ?>
    <p>Pro přidání komentáře se <a href="login.php">přihlaste</a>.</p>
  <?php endif; ?>

  <?php if ($comments_result->num_rows > 0): ?>
    <ul class="list-group">
      <?php while ($comment = $comments_result->fetch_assoc()): ?>
        <li class="list-group-item">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
              <small class="text-muted ms-2"><?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?></small>
              <p class="mb-1 mt-2"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
            </div>
            <?php if ($user_role === 'admin'): ?>
              <form method="post" onsubmit="return confirm('Smazat komentar?');">
                <input type="hidden" name="delete_comment_id" value="<?php echo $comment['id']; ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger">Smazat</button>
              </form>
            <?php endif; ?>
          </div>
        </li>
      <?php endwhile; ?>
    </ul>
  <?php else: ?>
    <p>Zatím žádné komentáře.</p>
  <?php endif; ?>
</div>

<footer class="bg-success text-white text-center py-3 mt-auto">
  &copy; <?php echo date("Y"); ?> PlantBid
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
