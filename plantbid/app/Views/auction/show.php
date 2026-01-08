<?php
use App\Support\Auth;

$img = '';
if (!empty($auction['image_url'])) {
    $img = $auction['image_url'];
} elseif (!empty($auction['image_path'])) {
    $img = strpos($auction['image_path'], 'http') === 0
        ? $auction['image_path']
        : 'uploads/' . $auction['image_path'];
} else {
    $img = 'uploads/nophoto.png';
}
?>
<div class="container py-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="mb-0"><?php echo e($auction['title']); ?></h1>
        <button type="button" class="btn btn-sm btn-outline-warning favorite-toggle" data-auction-id="<?php echo (int) $auction['id']; ?>">
            &#9734;
        </button>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <img src="<?php echo e($img); ?>" class="img-fluid rounded shadow" alt="Obrazek aukce" style="max-height:400px; width:auto;">
        </div>
        <div class="col-md-6">
            <p><?php echo nl2br(e($auction['description'])); ?></p>
            <p><strong>Startovni cena:</strong> <?php echo number_format((float) $auction['starting_price'], 2, ',', ' '); ?> Kc</p>
            <p><strong>Aktualni nejvyssi prihod:</strong> <?php echo number_format((float) $currentMax, 2, ',', ' '); ?> Kc</p>
            <p><strong>Konec aukce:</strong> <?php echo date('d.m.Y H:i', strtotime($auction['end_time'])); ?></p>
            <p class="text-muted"><strong>Aukci pridal:</strong> <?php echo e($auction['username']); ?></p>

            <?php if (!empty($bidError)): ?>
                <div class="alert alert-danger"><?php echo e($bidError); ?></div>
            <?php endif; ?>

            <?php if (Auth::check()): ?>
                <?php if (strtotime($auction['end_time']) > time()): ?>
                    <form method="post" action="<?php echo route_url('auction/bid'); ?>" class="mt-3">
                        <input type="hidden" name="auction_id" value="<?php echo (int) $auction['id']; ?>">
                        <div class="mb-3">
                            <label for="bid_amount" class="form-label">Vaše nabídka (v Kc):</label>
                            <input type="number" step="1" min="<?php echo $currentMax + 10; ?>" class="form-control" id="bid_amount" name="bid_amount" required>
                            <div class="form-text">Minimální možná nabídka: <?php echo number_format($currentMax + 10, 0, ',', ' '); ?> Kc</div>
                        </div>
                        <button type="submit" class="btn btn-success">Přihodit</button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info mt-3">Tato aukce již skončila.</div>
                <?php endif; ?>
            <?php else: ?>
                <p class="mt-3">Pro přihazování se musíte <a href="<?php echo route_url('login'); ?>">přihlásit</a>.</p>
            <?php endif; ?>
        </div>
    </div>

    <h3>Historie prihodu</h3>
    <?php if (!empty($bids)): ?>
        <ul class="list-group mb-5">
            <?php foreach ($bids as $bid): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div><strong><?php echo e($bid['username']); ?></strong> prihodil <?php echo number_format((float) $bid['amount'], 2, ',', ' '); ?> Kc</div>
                    <small class="text-muted"><?php echo date('d.m.Y H:i', strtotime($bid['bid_time'])); ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Pro tuto aukci zatim nejsou zadne prihody.</p>
    <?php endif; ?>

    <h3 class="mt-5">Komentáře</h3>
    <?php if (!empty($commentError)): ?>
        <div class="alert alert-danger"><?php echo e($commentError); ?></div>
    <?php endif; ?>

    <?php if (Auth::check()): ?>
        <form method="post" action="<?php echo route_url('auction/comment'); ?>" class="mb-4">
            <input type="hidden" name="auction_id" value="<?php echo (int) $auction['id']; ?>">
            <div class="mb-3">
                <label for="comment_content" class="form-label">Přidat komentář (max 100 slov)</label>
                <textarea id="comment_content" name="comment_content" class="form-control" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-outline-success">Odeslat</button>
        </form>
    <?php else: ?>
        <p>Pro přidání komentáře se <a href="<?php echo route_url('login'); ?>">přihlašte</a>.</p>
    <?php endif; ?>

    <?php if (!empty($comments)): ?>
        <ul class="list-group">
            <?php foreach ($comments as $comment): ?>
                <li class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong><?php echo e($comment['username']); ?></strong>
                            <small class="text-muted ms-2"><?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?></small>
                            <p class="mb-1 mt-2"><?php echo nl2br(e($comment['content'])); ?></p>
                        </div>
                        <?php if (Auth::isAdmin()): ?>
                            <form method="post" action="<?php echo route_url('auction/comment-delete'); ?>" onsubmit="return confirm('Smazat komentář?');">
                                <input type="hidden" name="auction_id" value="<?php echo (int) $auction['id']; ?>">
                                <input type="hidden" name="comment_id" value="<?php echo (int) $comment['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Smazat</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Zatím žádné komentáře.</p>
    <?php endif; ?>
</div>
