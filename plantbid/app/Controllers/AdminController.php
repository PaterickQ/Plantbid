<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\AuctionRepository;
use App\Repositories\UserRepository;
use App\Support\Auth;

class AdminController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();

        $auctions = new AuctionRepository();
        $users = new UserRepository();

        $this->render('admin/index', [
            'title' => 'Administrace',
            'auctions' => $auctions->getAllAuctions(),
            'users' => $users->getAll(),
            'message' => flash_get('admin_message'),
        ]);
    }

    public function updateRole(): void
    {
        Auth::requireAdmin();

        $userId = (int) ($_POST['user_id'] ?? 0);
        $role = ($_POST['role'] ?? 'user') === 'admin' ? 'admin' : 'user';
        if ($userId <= 0) {
            redirect_to('admin');
        }

        $repo = new UserRepository();
        $repo->updateRole($userId, $role);
        flash_set('admin_message', 'Role uživatele byla zmenena.');
        redirect_to('admin');
    }

    public function toggleBlock(): void
    {
        Auth::requireAdmin();

        $userId = (int) ($_POST['user_id'] ?? 0);
        $blocked = (int) ($_POST['blocked'] ?? 0);
        if ($userId <= 0) {
            redirect_to('admin');
        }

        if ($userId === Auth::id() && $blocked === 1) {
            flash_set('admin_message', 'Nemůžete zablokovat vlastní účet.');
            redirect_to('admin');
        }

        $repo = new UserRepository();
        $repo->updateBlocked($userId, $blocked);
        flash_set('admin_message', $blocked ? 'Uživatel byl zablokován.' : 'Uživatel byl odblokován.');
        redirect_to('admin');
    }
}
