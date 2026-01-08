<div class="container py-5">
    <h1 class="mb-4">Administrace</h1>
    <p class="text-muted">Sprava aukci a uzivatelu.</p>

    <?php if (!empty($message)): ?>
        <div class="alert alert-info"><?php echo e($message); ?></div>
    <?php endif; ?>

    <h2 class="h4 mt-4">Aukce</h2>
    <?php if (!empty($auctions)): ?>
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
            <?php foreach ($auctions as $row): ?>
                <tr>
                    <td><?php echo (int) $row['id']; ?></td>
                    <td><a href="<?php echo route_url('auction', ['id' => $row['id']]); ?>"><?php echo e($row['title']); ?></a></td>
                    <td><?php echo e($row['owner']); ?></td>
                    <td><?php echo date('d.m.Y H:i', strtotime($row['end_time'])); ?></td>
                    <td class="d-flex gap-2">
                        <a href="<?php echo route_url('auction/edit', ['id' => $row['id']]); ?>" class="btn btn-sm btn-primary">Upravit</a>
                        <a href="<?php echo route_url('auction/delete', ['id' => $row['id']]); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Opravdu smazat aukci?');">Smazat</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">Žádné aukce k zobrazení.</div>
    <?php endif; ?>

    <h2 class="h4 mt-5">Uzivatele</h2>
    <?php if (!empty($users)): ?>
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
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?php echo (int) $u['id']; ?></td>
                    <td><?php echo e($u['username']); ?></td>
                    <td><?php echo e($u['email']); ?></td>
                    <td>
                        <form method="post" action="<?php echo route_url('admin/role'); ?>" class="d-flex gap-2 align-items-center">
                            <input type="hidden" name="user_id" value="<?php echo (int) $u['id']; ?>">
                            <select name="role" class="form-select form-select-sm">
                                <option value="user" <?php echo $u['role'] === 'user' ? 'selected' : ''; ?>>user</option>
                                <option value="admin" <?php echo $u['role'] === 'admin' ? 'selected' : ''; ?>>admin</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-outline-primary">Uložit</button>
                        </form>
                    </td>
                    <td>
                        <?php echo $u['blocked'] ? '<span class="badge bg-danger">blokovan</span>' : '<span class="badge bg-success">aktivni</span>'; ?>
                    </td>
                    <td><?php echo date('d.m.Y H:i', strtotime($u['created_at'])); ?></td>
                    <td>
                        <form method="post" action="<?php echo route_url('admin/block'); ?>" class="d-inline">
                            <input type="hidden" name="user_id" value="<?php echo (int) $u['id']; ?>">
                            <input type="hidden" name="blocked" value="<?php echo $u['blocked'] ? 0 : 1; ?>">
                            <button type="submit" class="btn btn-sm <?php echo $u['blocked'] ? 'btn-success' : 'btn-warning'; ?>">
                                <?php echo $u['blocked'] ? 'Aktivovat' : 'Blokovat'; ?>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">Zadne uzivatele k zobrazeni.</div>
    <?php endif; ?>
</div>
