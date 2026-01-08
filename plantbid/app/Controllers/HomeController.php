<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\AuctionRepository;

class HomeController extends Controller
{
    public function index(): void
    {
        $repo = new AuctionRepository();
        $auctions = $repo->getActiveAuctions();
        $this->render('home/index', [
            'title' => 'Aktuální aukce',
            'auctions' => $auctions,
        ]);
    }
}
