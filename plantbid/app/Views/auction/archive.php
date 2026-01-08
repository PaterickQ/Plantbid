<div class="container py-5">
    <h1 class="mb-4">Archiv aukci</h1>

    <?php if (empty($auctions)): ?>
        <div class="alert alert-info">Zadne ukoncene aukce.</div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 g-4">
            <?php foreach ($auctions as $row): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <?php if (!empty($row['image_path'])): ?>
                            <?php
                            $imgSrc = strpos($row['image_path'], 'http') === 0
                                ? $row['image_path']
                                : 'uploads/' . $row['image_path'];
                            ?>
                            <img src="<?php echo e($imgSrc); ?>" class="card-img-top" alt="<?php echo e($row['title']); ?>" style="object-fit: cover; height: 200px;">
                        <?php elseif (!empty($row['image_url'])): ?>
                            <img src="<?php echo e($row['image_url']); ?>" class="card-img-top" alt="<?php echo e($row['title']); ?>" style="object-fit: cover; height: 200px;">
                        <?php else: ?>
                            <img src="<?php echo e('uploads/nophoto.png'); ?>" class="card-img-top" alt="Zadny obrazek" style="object-fit: cover; height: 200px;">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">
                                <a href="<?php echo route_url('auction', ['id' => $row['id']]); ?>"><?php echo e($row['title']); ?></a>
                            </h5>
                            <p class="card-text"><?php echo nl2br(e($row['description'])); ?></p>
                            <small class="text-muted mt-auto">Skoncila: <?php echo date('j. n. Y H:i', strtotime($row['end_time'])); ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="<?php echo route_url(''); ?>" class="btn btn-secondary">Zpět na hlavní stránku</a>
    </div>
</div>
