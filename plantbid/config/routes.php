<?php

use App\Controllers\AdminController;
use App\Controllers\ApiController;
use App\Controllers\AuthController;
use App\Controllers\AuctionController;
use App\Controllers\HomeController;
use App\Controllers\ProfileController;

return [
    ['GET', '/', HomeController::class . '@index'],
    ['GET', 'auction', AuctionController::class . '@show'],
    ['POST', 'auction/bid', AuctionController::class . '@bid'],
    ['POST', 'auction/comment', AuctionController::class . '@comment'],
    ['POST', 'auction/comment-delete', AuctionController::class . '@deleteComment'],
    ['GET', 'archive', AuctionController::class . '@archive'],
    ['GET', 'auction/new', AuctionController::class . '@create'],
    ['POST', 'auction/new', AuctionController::class . '@store'],
    ['GET', 'auction/edit', AuctionController::class . '@edit'],
    ['POST', 'auction/edit', AuctionController::class . '@update'],
    ['GET', 'auction/delete', AuctionController::class . '@delete'],
    ['GET', 'my-auctions', ProfileController::class . '@auctions'],

    ['GET', 'login', AuthController::class . '@showLogin'],
    ['POST', 'login', AuthController::class . '@login'],
    ['GET', 'register', AuthController::class . '@showRegister'],
    ['POST', 'register', AuthController::class . '@register'],
    ['GET', 'logout', AuthController::class . '@logout'],

    ['GET', 'admin', AdminController::class . '@index'],
    ['POST', 'admin/role', AdminController::class . '@updateRole'],
    ['POST', 'admin/block', AdminController::class . '@toggleBlock'],

    ['GET', 'api/auctions', ApiController::class . '@auctions'],
];
