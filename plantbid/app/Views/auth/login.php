<div class="container py-5">
    <h1 class="mb-4 text-center">Přihlášení</h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger text-center" role="alert">
            <?php echo e($error); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo route_url('login'); ?>" class="mx-auto" style="max-width: 400px;">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" name="email" type="email" class="form-control" required autofocus
                   value="<?php echo e($email ?? ''); ?>" />
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Heslo</label>
            <input id="password" name="password" type="password" class="form-control" required />
        </div>

        <div class="mb-3">
            <label for="captcha" class="form-label">
                Ověřovací otázka: Kolik je <?php echo e($captcha); ?>?
            </label>
            <input id="captcha" name="captcha" type="number" class="form-control" required />
        </div>

        <button type="submit" class="btn btn-success w-100">Přihlásit se</button>
    </form>
</div>
