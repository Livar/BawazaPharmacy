<?php

class AuthService
{
    public function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function currentUser(): ?array
    {
        $this->startSession();
        return $_SESSION['user'] ?? null;
    }

    public function login(array $user): void
    {
        $this->startSession();
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'username' => $user['username'],
            'role' => $user['role'],
        ];
    }

    public function logout(): void
    {
        $this->startSession();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'] ?? false, $params['httponly'] ?? true);
        }
        session_destroy();
    }

    public function requireLogin(): void
    {
        if (!$this->currentUser()) {
            header('Location: ' . routeUrl('login'));
            exit;
        }
    }

    public function requireAdmin(): void
    {
        $user = $this->currentUser();
        if (!$user || $user['role'] !== 'admin') {
            header('Location: ' . routeUrl('clock'));
            exit;
        }
    }
}
