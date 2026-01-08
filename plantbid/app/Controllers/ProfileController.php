<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\AuctionRepository;
use App\Support\Auth;

class ProfileController extends Controller
{
    public function auctions(): void
    {
        Auth::requireLogin();

        $repo = new AuctionRepository();
        $this->render('profile/my_auctions', [
            'title' => 'Moje aukce',
            'auctions' => $repo->getUserAuctions(Auth::id()),
        ]);
    }
}
