<?php
use App\Support\Auth;
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8" />
    <title><?php echo e($title ?? 'PlantBid'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>" />
</head>
<body class="d-flex flex-column min-vh-100 bg-light" data-base-url="<?php echo e(base_url()); ?>" data-use-query-routes="<?php echo APP_CONFIG['use_query_routes'] ? '1' : '0'; ?>">

<?php require __DIR__ . '/../partials/nav.php'; ?>

<main class="flex-grow-1">
    <?php echo $content; ?>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo asset('js/countdown.js'); ?>"></script>
<script src="<?php echo asset('js/favorites.js'); ?>"></script>
</body>
</html>
