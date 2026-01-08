<?php
$endValue = '';
if (!empty($auction['end_time'])) {
    $endValue = date('Y-m-d\TH:i', strtotime($auction['end_time']));
}

$currentImage = '';
if (!empty($auction['image_url'])) {
    $currentImage = $auction['image_url'];
} elseif (!empty($auction['image_path'])) {
    $currentImage = strpos($auction['image_path'], 'http') === 0
        ? $auction['image_path']
        : 'uploads/' . $auction['image_path'];
}
?>
<div class="container py-5">
    <h1><?php echo e($title ?? 'Aukce'); ?></h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="post" action="<?php echo e($formAction); ?>" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="title" class="form-label">Název aukce</label>
            <input type="text" class="form-control" id="title" name="title" required value="<?php echo e($auction['title']); ?>">
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Popis</label>
            <textarea class="form-control" id="description" name="description" rows="4" required><?php echo e($auction['description']); ?></textarea>
        </div>

        <?php if (empty($auction['id'])): ?>
            <div class="mb-3">
                <label for="end_time" class="form-label">Konec aukce</label>
                <input type="datetime-local" class="form-control" id="end_time" name="end_time" required value="<?php echo e($endValue); ?>">
            </div>

            <div class="mb-3">
                <label for="starting_price" class="form-label">Základní cena (Kc)</label>
                <input type="number" step="0.01" min="0" class="form-control" id="starting_price" name="starting_price" required value="<?php echo e((string) $auction['starting_price']); ?>">
            </div>
        <?php endif; ?>

        <?php if ($currentImage !== ''): ?>
            <div class="mb-3">
                <label class="form-label">Aktuální obrázek</label><br>
                <img src="<?php echo e($currentImage); ?>" alt="Obrazek aukce" class="img-fluid mb-2" style="max-height: 200px;">
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <label for="image" class="form-label">Nový obrázek (volitelné)</label>
            <input type="file" class="form-control" id="image" name="image" accept="image/*">
        </div>

        <div class="mb-3">
            <label for="image_url" class="form-label">Externí URL obrazku (volitelné)</label>
            <input type="url" class="form-control" id="image_url" name="image_url" placeholder="https://example.com/image.jpg" value="<?php echo e($auction['image_url'] ?? ''); ?>">
        </div>

        <?php if (empty($auction['id'])): ?>
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
        <?php endif; ?>

        <button type="submit" class="btn btn-success">Ulozit</button>
        <a href="<?php echo route_url('my-auctions'); ?>" class="btn btn-link">Zpět</a>
    </form>
</div>
