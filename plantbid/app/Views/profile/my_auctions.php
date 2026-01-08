<div class="container py-5">
    <h2 class="mb-4">Moje aukce</h2>

    <?php if (!empty($auctions)): ?>
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Nazev</th>
                <th>Konec</th>
                <th>Akce</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($auctions as $row): ?>
                <tr>
                    <td><a href="<?php echo route_url('auction', ['id' => $row['id']]); ?>"><?php echo e($row['title']); ?></a></td>
                    <td><?php echo date('d.m.Y H:i', strtotime($row['end_time'])); ?></td>
                    <td>
                        <?php if (strtotime($row['end_time']) > time()): ?>
                            <a href="<?php echo route_url('auction/edit', ['id' => $row['id']]); ?>" class="btn btn-sm btn-primary">Upravit</a>
                            <a href="<?php echo route_url('auction/delete', ['id' => $row['id']]); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Opravdu chcete smazat tuto aukci?')">Smazat</a>
                        <?php else: ?>
                            <span class="text-muted">Aukce skončila</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Nemáte žádné aukce.</p>
    <?php endif; ?>

    <hr class="my-5">

    <h3>Oblíbené aukce</h3>
    <p class="text-muted">Seznam je uložen v prohlížeči.</p>
    <div id="favorites-empty" class="alert alert-info">Zatím nemáte uložené oblíbené aukce.</div>
    <div class="row" id="favorites-list" data-favorites-list></div>
</div>
