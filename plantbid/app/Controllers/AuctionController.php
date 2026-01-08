<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\AuctionRepository;
use App\Repositories\BidRepository;
use App\Repositories\CommentRepository;
use App\Support\Auth;

class AuctionController extends Controller
{
    public function show(): void
    {
        $auctionId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($auctionId <= 0) {
            redirect_to('');
        }

        $auctions = new AuctionRepository();
        $bids = new BidRepository();
        $comments = new CommentRepository();

        $auction = $auctions->getByIdWithUser($auctionId);
        if (!$auction) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Aukce nenalezena']);
            return;
        }

        $maxBid = $bids->getMaxBid($auctionId);
        $currentMax = $maxBid ?? (float) $auction['starting_price'];

        $this->render('auction/show', [
            'title' => $auction['title'],
            'auction' => $auction,
            'currentMax' => $currentMax,
            'bids' => $bids->getBidsForAuction($auctionId),
            'comments' => $comments->getForAuction($auctionId),
            'bidError' => flash_get('bid_error'),
            'commentError' => flash_get('comment_error'),
        ]);
    }

    public function archive(): void
    {
        $repo = new AuctionRepository();
        $this->render('auction/archive', [
            'title' => 'Archiv aukcí',
            'auctions' => $repo->getArchivedAuctions(),
        ]);
    }

    public function create(): void
    {
        Auth::requireLogin();
        $this->render('auction/form', [
            'title' => 'Nová aukce',
            'formAction' => route_url('auction/new'),
            'auction' => [
                'title' => '',
                'description' => '',
                'end_time' => '',
                'starting_price' => '',
                'image_path' => '',
                'image_url' => '',
            ],
            'error' => flash_get('auction_error'),
        ]);
    }

    public function store(): void
    {
        Auth::requireLogin();

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $endTime = trim($_POST['end_time'] ?? '');
        $startingPrice = (float) ($_POST['starting_price'] ?? 0);
        $imageUrl = trim($_POST['image_url'] ?? '');

        if ($endTime) {
            $endTime = str_replace('T', ' ', $endTime) . ':00';
        }

        if ($title === '' || $description === '' || $endTime === '' || $startingPrice < 0) {
            flash_set('auction_error', 'Vyplňte všechna povinná pole.');
            redirect_to('auction/new');
        }

        $imagePath = $this->handleUpload($_FILES['image'] ?? []);
        if ($imagePath && $imageUrl !== '') {
            $imageUrl = '';
        }
        if ($imageUrl !== '' && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            $imageUrl = '';
        }

        $repo = new AuctionRepository();
        $repo->createAuction(Auth::id(), $title, $description, $endTime, $startingPrice, $imagePath, $imageUrl);

        redirect_to('');
    }

    public function edit(): void
    {
        Auth::requireLogin();

        $auctionId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($auctionId <= 0) {
            redirect_to('my-auctions');
        }

        $repo = new AuctionRepository();
        $auction = $repo->getById($auctionId);
        if (!$auction) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Aukce nenalezena']);
            return;
        }

        if (!Auth::isAdmin() && (int) $auction['user_id'] !== Auth::id()) {
            http_response_code(403);
            $this->render('errors/404', ['title' => 'Přístup zamítnut']);
            return;
        }

        $this->render('auction/form', [
            'title' => 'Uprava aukce',
            'formAction' => route_url('auction/edit', ['id' => $auctionId]),
            'auction' => $auction,
            'error' => flash_get('auction_error'),
        ]);
    }

    public function update(): void
    {
        Auth::requireLogin();

        $auctionId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($auctionId <= 0) {
            redirect_to('my-auctions');
        }

        $repo = new AuctionRepository();
        $auction = $repo->getById($auctionId);
        if (!$auction) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Aukce nenalezena']);
            return;
        }

        if (!Auth::isAdmin() && (int) $auction['user_id'] !== Auth::id()) {
            http_response_code(403);
            $this->render('errors/404', ['title' => 'Přístup zamítnut']);
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $imageUrl = trim($_POST['image_url'] ?? '');

        if ($title === '' || $description === '') {
            flash_set('auction_error', 'Vyplňte název a popis.');
            redirect_to('auction/edit', ['id' => $auctionId]);
        }

        $imagePath = $auction['image_path'];
        $existingUrl = $auction['image_url'] ?? '';

        $newUpload = $this->handleUpload($_FILES['image'] ?? []);
        if ($newUpload) {
            $imagePath = $newUpload;
            $imageUrl = '';
        } elseif ($imageUrl !== '') {
            $imagePath = '';
            $imageUrl = filter_var($imageUrl, FILTER_VALIDATE_URL) ? $imageUrl : $existingUrl;
        } else {
            $imageUrl = $existingUrl;
        }

        if (Auth::isAdmin()) {
            $repo->updateAuction($auctionId, $title, $description, $imagePath, $imageUrl);
        } else {
            $repo->updateAuctionForUser($auctionId, Auth::id(), $title, $description, $imagePath, $imageUrl);
        }

        redirect_to('my-auctions');
    }

    public function delete(): void
    {
        Auth::requireLogin();

        $auctionId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($auctionId <= 0) {
            redirect_to('my-auctions');
        }

        $repo = new AuctionRepository();
        if (Auth::isAdmin()) {
            $repo->deleteAuction($auctionId);
            redirect_to('admin');
        }

        $repo->deleteAuctionForUser($auctionId, Auth::id());
        redirect_to('my-auctions');
    }

    public function bid(): void
    {
        Auth::requireLogin();

        $auctionId = (int) ($_POST['auction_id'] ?? 0);
        $bidAmount = (float) ($_POST['bid_amount'] ?? 0);
        if ($auctionId <= 0 || $bidAmount <= 0) {
            flash_set('bid_error', 'Neplatny prihaz.');
            redirect_to('auction', ['id' => $auctionId]);
        }

        $auctions = new AuctionRepository();
        $bids = new BidRepository();

        $auction = $auctions->getById($auctionId);
        if (!$auction) {
            flash_set('bid_error', 'Aukce nenalezena.');
            redirect_to('');
        }

        $currentMax = $bids->getMaxBid($auctionId);
        $currentMax = $currentMax ?? (float) $auction['starting_price'];
        if ($bidAmount <= $currentMax) {
            flash_set(
                'bid_error',
                'Příhoz musí být vyšší než aktuální cena (' . number_format($currentMax, 2, ',', ' ') . ' Kc).'
            );
            redirect_to('auction', ['id' => $auctionId]);
        }

        $bids->createBid($auctionId, Auth::id(), $bidAmount);
        redirect_to('auction', ['id' => $auctionId]);
    }

    public function comment(): void
    {
        Auth::requireLogin();

        $auctionId = (int) ($_POST['auction_id'] ?? 0);
        $content = trim($_POST['comment_content'] ?? '');
        if ($auctionId <= 0) {
            redirect_to('');
        }

        $wordCount = str_word_count($content);
        if ($content === '') {
            flash_set('comment_error', 'Komentář nesmí být prázdný.');
            redirect_to('auction', ['id' => $auctionId]);
        }
        if ($wordCount > 100) {
            flash_set('comment_error', 'Komentář může mít max 100 slov.');
            redirect_to('auction', ['id' => $auctionId]);
        }

        $comments = new CommentRepository();
        $comments->create($auctionId, Auth::id(), $content);
        redirect_to('auction', ['id' => $auctionId]);
    }

    public function deleteComment(): void
    {
        Auth::requireAdmin();

        $auctionId = (int) ($_POST['auction_id'] ?? 0);
        $commentId = (int) ($_POST['comment_id'] ?? 0);
        if ($auctionId <= 0 || $commentId <= 0) {
            redirect_to('auction', ['id' => $auctionId]);
        }

        $comments = new CommentRepository();
        $comments->delete($commentId, $auctionId);
        redirect_to('auction', ['id' => $auctionId]);
    }

    private function handleUpload(array $file): ?string
    {
        if (empty($file['name']) || empty($file['tmp_name'])) {
            return null;
        }

        $uploadDir = __DIR__ . '/../../public/uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $name = pathinfo($file['name'], PATHINFO_FILENAME);
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '', $name);
        $safeName = $safeName !== '' ? $safeName : 'upload';
        $filename = $safeName . '-' . uniqid('', true);
        if ($ext !== '') {
            $filename .= '.' . $ext;
        }

        $target = $uploadDir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $target)) {
            return null;
        }

        return $filename;
    }
}
