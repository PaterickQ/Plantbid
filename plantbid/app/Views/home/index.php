<div class="container py-5">
    <h1 class="mb-4 text-center">Aktuální aukce rostlin</h1>

    <?php if (!empty($auctions)): ?>
        <div class="row">
            <?php foreach ($auctions as $row): ?>
                <?php
                $imgSrc = '';
                if (!empty($row['image_path'])) {
                    $imgSrc = strpos($row['image_path'], 'http') === 0
                        ? $row['image_path']
                        : 'uploads/' . $row['image_path'];
                } elseif (!empty($row['image_url'])) {
                    $imgSrc = $row['image_url'];
                } else {
                    $imgSrc = 'uploads/nophoto.png';
                }
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm" data-auction-card data-auction-id="<?php echo (int) $row['id']; ?>">
                        <img src="<?php echo e($imgSrc); ?>" class="card-img-top" alt="Obrazek rostliny" style="height:200px; object-fit:cover;">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="card-title">
                                    <a href="<?php echo route_url('auction', ['id' => $row['id']]); ?>">
                                        <?php echo e($row['title']); ?>
                                    </a>
                                </h5>
                                <button type="button" class="btn btn-sm btn-outline-warning favorite-toggle" data-auction-id="<?php echo (int) $row['id']; ?>">
                                    &#9734;
                                </button>
                            </div>
                            <p class="card-text">
                                <?php echo nl2br(e($row['description'])); ?>
                            </p>
                            <p class="mt-auto fw-bold">
                                Aktuální cena: <?php echo number_format((float) $row['current_price'], 2, ',', ' '); ?> Kc
                            </p>
                            <p class="countdown mt-auto text-danger fw-bold" data-endtime="<?php echo e($row['end_time']); ?>">
                                Načítám odpočet...
                            </p>
                            <p class="text-muted">Přidal: <?php echo e($row['username']); ?></p>
                        </div>
                        <div class="card-footer text-muted d-flex justify-content-between align-items-center">
                            <small>Konec: <?php echo date('d.m.Y H:i', strtotime($row['end_time'])); ?></small>
                            <a href="<?php echo route_url('auction', ['id' => $row['id']]); ?>" class="btn btn-sm btn-success">Zobrazit aukci</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-muted text-center">Žádné aktivní aukce zatím nejsou.</p>
    <?php endif; ?>
</div>
