<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];
$data = read_json_input();
$action = $data['action'] ?? ($_GET['action'] ?? 'me');

if ($action === 'login' && $method === 'POST') {
    $username = trim((string) ($data['username'] ?? ''));
    $password = (string) ($data['password'] ?? '');

    if ($username === '' || $password === '') {
        respond(['error' => 'Username and password are required.'], 422);
    }

    $stmt = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE username = :username LIMIT 1');
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        respond(['error' => 'Invalid credentials.'], 401);
    }

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['username'] = $user['username'];

    respond(['user' => ['id' => (int) $user['id'], 'username' => $user['username']]]);
}

if ($action === 'logout' && $method === 'POST') {
    session_unset();
    session_destroy();
    respond(['ok' => true]);
}

if ($action === 'me' && $method === 'GET') {
    if (!isset($_SESSION['user_id'])) {
        respond(['user' => null]);
    }

    respond(['user' => ['id' => (int) $_SESSION['user_id'], 'username' => (string) $_SESSION['username']]]);
}

respond(['error' => 'Unsupported action.'], 405);
