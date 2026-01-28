<?php

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if ($this->authService->currentUser()) {
            header('Location: ' . routeUrl('dashboard'));
            exit;
        }

        $message = '';
        $dbError = '';
        $pharmacies = [];

        try {
            $pharmacies = $this->pharmacyService->activePharmacies();
        } catch (PDOException $exception) {
            $dbError = 'Database connection is not configured yet.';
        }

        $this->render('auth/login', [
            'message' => $message,
            'dbError' => $dbError,
            'pharmacies' => $pharmacies,
        ]);
    }

    public function login(): void
    {
        $message = '';
        $dbError = '';
        $pharmacies = [];

        try {
            $pharmacies = $this->pharmacyService->activePharmacies();
            if (!$this->csrfService->verify($_POST['csrf_token'] ?? '')) {
                $message = 'Invalid session token.';
                $this->render('auth/login', [
                    'message' => $message,
                    'dbError' => $dbError,
                    'pharmacies' => $pharmacies,
                ]);
                return;
            }
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $pharmacyId = (int) ($_POST['pharmacy_id'] ?? 0);

            $stmt = Database::getConnection()->prepare('SELECT id, name, username, password_hash, role, status, pharmacy_id FROM users WHERE username = ? AND pharmacy_id = ? AND status = "active"');
            $stmt->execute([$username, $pharmacyId]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $this->authService->login($user);
                $this->pharmacyService->setCurrentPharmacy((int) $user['pharmacy_id']);
                header('Location: ' . routeUrl('dashboard'));
                exit;
            }

            $message = 'Invalid username, pharmacy, or password.';
        } catch (PDOException $exception) {
            $dbError = 'Database connection is not configured yet.';
        }

        $this->render('auth/login', [
            'message' => $message,
            'dbError' => $dbError,
            'pharmacies' => $pharmacies,
        ]);
    }

    public function logout(): void
    {
        $this->authService->logout();
        header('Location: ' . routeUrl('login'));
        exit;
    }
}
