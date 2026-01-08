<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\UserRepository;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (!isset($_SESSION['captcha_answer'])) {
            $a = random_int(1, 10);
            $b = random_int(1, 10);
            $_SESSION['captcha_question'] = $a . ' + ' . $b;
            $_SESSION['captcha_answer'] = $a + $b;
        }

        $this->render('auth/login', [
            'title' => 'Prihlaseni',
            'error' => flash_get('auth_error'),
            'email' => flash_get('auth_email'),
            'captcha' => $_SESSION['captcha_question'] ?? '',
        ]);
    }

    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $captcha = trim($_POST['captcha'] ?? '');

        if ($email === '' || $password === '') {
            flash_set('auth_error', 'Zadejte email a heslo.');
            flash_set('auth_email', $email);
            redirect_to('login');
        }

        if (!isset($_SESSION['captcha_answer']) || (int) $captcha !== (int) $_SESSION['captcha_answer']) {
            flash_set('auth_error', 'Špatná odpověď na ověrovací otázku.');
            flash_set('auth_email', $email);
            redirect_to('login');
        }

        $repo = new UserRepository();
        $user = $repo->findByEmail($email);
        if (!$user) {
            flash_set('auth_error', 'Uživatel nenalezen.');
            flash_set('auth_email', $email);
            redirect_to('login');
        }

        if (!empty($user['blocked'])) {
            flash_set('auth_error', 'Účet je zablokován.');
            redirect_to('login');
        }

        if (!password_verify($password, $user['password_hash'])) {
            flash_set('auth_error', 'Špatné heslo.');
            flash_set('auth_email', $email);
            redirect_to('login');
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'] ?? 'user';
        unset($_SESSION['captcha_answer'], $_SESSION['captcha_question']);

        redirect_to('');
    }

    public function showRegister(): void
    {
        $this->render('auth/register', [
            'title' => 'Registrace',
            'error' => flash_get('register_error'),
            'form' => [
                'username' => flash_get('register_username'),
                'email' => flash_get('register_email'),
            ],
        ]);
    }

    public function register(): void
    {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $captcha = trim($_POST['captcha'] ?? '');

        if ($captcha !== '8') {
            $this->registerFail('Špatně.', $username, $email);
        }

        if ($username === '' || $email === '' || $password === '') {
            $this->registerFail('Vyplňte všechna pole.', $username, $email);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->registerFail('Zadejte platný email.', $username, $email);
        }

        $repo = new UserRepository();
        if ($repo->existsByEmailOrUsername($email, $username)) {
            $this->registerFail('Uživatel už existuje.', $username, $email);
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        if (!$repo->create($username, $email, $passwordHash, 'user')) {
            $this->registerFail('Registrace selhala.', $username, $email);
        }

        redirect_to('login');
    }

    public function logout(): void
    {
        session_destroy();
        redirect_to('');
    }

    private function registerFail(string $message, string $username, string $email): void
    {
        flash_set('register_error', $message);
        flash_set('register_username', $username);
        flash_set('register_email', $email);
        redirect_to('register');
    }
}
