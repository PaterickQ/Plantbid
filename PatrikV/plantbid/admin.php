<?php
session_start();
require_once 'includes/db.php';

$user_role = $_SESSION['role'] ?? 'user';
if (!isset($_SESSION['user_id']) || $user_role !== 'admin') {
    http_response_code(403);
    echo "Přístup pouze pro administrátory.";
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id'])) {
    $target_id = (int)$_POST['user_id'];
    if ($_POST['action'] === 'role') {
        $new_role = ($_POST['role'] ?? 'user') === 'admin' ? 'admin' : 'user';
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $new_role, $target_id);
        $stmt->execute();
        $message = "Role uživatele byla změněna.";
    } elseif ($_POST['action'] === 'block') {
        $new_blocked = ($_POST['blocked'] ?? '0') === '1' ? 1 : 0;
        if ($target_id === (int)$_SESSION['user_id'] && $new_blocked === 1) {
            $message = "Nemůžete zablokovat vlastní účet.";
        } else {
            $stmt = $conn->prepare("UPDATE users SET blocked = ? WHERE id = ?");
            $stmt->bind_param("ii", $new_blocked, $target_id);
            $stmt->execute();
            $message = $new_blocked ? "Uživatel byl zablokován." : "Uživatel byl odblokován.";
        }
    }
}

$auction_sql = "
SELECT a.id, a.title, a.end_time, u.username AS owner
FROM auctions a
JOIN users u ON a.user_id = u.id
ORDER BY a.end_time DESC
";
$auction_result = $conn->query($auction_sql);

$user_sql = "SELECT id, username, email, role, blocked, created_at FROM users ORDER BY created_at DESC";
$user_result = $conn->query($user_sql);
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Admin – PlantBid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css" />
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-success">
  <div class="container">
    <a class="navbar-brand" href="index.php">🪴 PlantBid</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="#">Přihlášen jako <?php echo htmlspecialchars($_SESSION['username']); ?> (admin)</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Odhlásit se</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php">Zpět na aukce</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-5">
  <h1 class="mb-4">Administrace</h1>
  <p class="text-muted">Správa všech aukcí a uživatelů. Admin může mazat a upravovat libovolné aukce.</p>

  <?php if ($message): ?>
    <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>

  <h2 class="h4 mt-4">Aukce</h2>
  <?php if ($auction_result && $auction_result->num_rows > 0): ?>
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>ID</th>
          <th>Název</th>
          <th>Vlastník</th>
          <th>Konec</th>
          <th>Akce</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $auction_result->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['id']); ?></td>
            <td><a href="auction.php?id=<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['title']); ?></a></td>
            <td><?php echo htmlspecialchars($row['owner']); ?></td>
            <td><?php echo date('d.m.Y H:i', strtotime($row['end_time'])); ?></td>
            <td class="d-flex gap-2">
              <a href="edit_auction.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">Upravit</a>
              <a href="delete_auction.php?id=<?php echo $row['id']; ?>&redirect=admin.php" class="btn btn-sm btn-danger" onclick="return confirm('Opravdu smazat aukci?');">Smazat</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-info">Žádné aukce k zobrazení.</div>
  <?php endif; ?>

  <h2 class="h4 mt-5">Uživatelé</h2>
  <?php if ($user_result && $user_result->num_rows > 0): ?>
    <table class="table table-bordered align-middle">
      <thead>
        <tr>
          <th>ID</th>
          <th>Uživatelské jméno</th>
          <th>Email</th>
          <th>Role</th>
          <th>Stav</th>
          <th>Vytvořen</th>
          <th>Akce</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($u = $user_result->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($u['id']); ?></td>
            <td><?php echo htmlspecialchars($u['username']); ?></td>
            <td><?php echo htmlspecialchars($u['email']); ?></td>
            <td>
              <form method="post" class="d-flex gap-2 align-items-center">
                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                <input type="hidden" name="action" value="role">
                <select name="role" class="form-select form-select-sm">
                  <option value="user" <?php echo $u['role'] === 'user' ? 'selected' : ''; ?>>user</option>
                  <option value="admin" <?php echo $u['role'] === 'admin' ? 'selected' : ''; ?>>admin</option>
                </select>
                <button type="submit" class="btn btn-sm btn-outline-primary">Uložit</button>
              </form>
            </td>
            <td>
              <?php echo $u['blocked'] ? '<span class="badge bg-danger">blokován</span>' : '<span class="badge bg-success">aktivní</span>'; ?>
            </td>
            <td><?php echo date('d.m.Y H:i', strtotime($u['created_at'])); ?></td>
            <td>
              <form method="post" class="d-inline">
                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                <input type="hidden" name="action" value="block">
                <input type="hidden" name="blocked" value="<?php echo $u['blocked'] ? 0 : 1; ?>">
                <button type="submit" class="btn btn-sm <?php echo $u['blocked'] ? 'btn-success' : 'btn-warning'; ?>" <?php echo ($u['id'] === (int)$_SESSION['user_id'] && !$u['blocked']) ? '' : ''; ?>>
                  <?php echo $u['blocked'] ? 'Aktivovat' : 'Blokovat'; ?>
                </button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-info">Žádní uživatelé k zobrazení.</div>
  <?php endif; ?>
</div>

<footer class="bg-success text-white text-center py-3 mt-auto">
  <small>&copy; <?php echo date("Y"); ?> PlantBid – Všechna práva vyhrazena</small>
</footer>
</body>
</html>
