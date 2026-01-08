<?php
use App\Support\Auth;
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container">
        <a class="navbar-brand" href="<?php echo route_url(''); ?>">PlantBid</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (Auth::check()): ?>
                    <li class="nav-item">
                        <span class="nav-link">Přihlášen jako <?php echo e(Auth::username()); ?> (<?php echo e(Auth::role()); ?>)</span>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo route_url('logout'); ?>">Odhlásit</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo route_url('login'); ?>">Přihlásit</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo route_url('register'); ?>">Registrovat</a></li>
                <?php endif; ?>

                <?php if (Auth::check() && Auth::isAdmin()): ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo route_url('admin'); ?>">Admin</a></li>
                <?php endif; ?>

                <li class="nav-item"><a class="nav-link" href="<?php echo route_url('archive'); ?>">Archiv</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo route_url('auction/new'); ?>">Přidat aukci</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo route_url('my-auctions'); ?>">Moje aukce</a></li>
            </ul>
        </div>
    </div>
</nav>
