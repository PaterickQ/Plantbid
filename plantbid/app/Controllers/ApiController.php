<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\AuctionRepository;

class ApiController extends Controller
{
    public function auctions(): void
    {
        $ids = $_GET['ids'] ?? '';
        $list = array_filter(array_map('trim', explode(',', $ids)));
        $repo = new AuctionRepository();
        $auctions = $repo->getByIds($list);

        $this->json([
            'data' => $auctions,
        ]);
    }
}
