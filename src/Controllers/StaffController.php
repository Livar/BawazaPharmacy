<?php

class StaffController extends Controller
{
    public function index(): void
    {
        $this->authService->requireLogin();
        $this->authService->requireAdmin();

        $pdo = Database::getConnection();
        $message = '';
        $pharmacyId = $this->pharmacyService->currentPharmacyId();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->csrfService->verify($_POST['csrf_token'] ?? '')) {
                $message = 'Invalid session token.';
            } else {
                $name = trim($_POST['name'] ?? '');
                $username = trim($_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';
                $role = $_POST['role'] ?? 'staff';

                if ($name && $username && $password) {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $checkStmt = $pdo->prepare('SELECT id FROM users WHERE username = ? AND pharmacy_id = ?');
                    $checkStmt->execute([$username, $pharmacyId]);
                    if ($checkStmt->fetch()) {
                        $message = 'Username already exists for this pharmacy.';
                    } else {
                        $stmt = $pdo->prepare('INSERT INTO users (pharmacy_id, name, username, password_hash, role, status) VALUES (?, ?, ?, ?, ?, "active")');
                        $stmt->execute([$pharmacyId, $name, $username, $hash, $role]);
                        $message = 'Staff member added.';
                    }
                } else {
                    $message = 'Please fill in all required fields.';
                }
            }
        }

        $staffStmt = $pdo->prepare('SELECT id, name, username, role, status FROM users WHERE pharmacy_id = ? ORDER BY name');
        $staffStmt->execute([$pharmacyId]);
        $staff = $staffStmt->fetchAll();

        $this->render('staff/index', [
            'message' => $message,
            'staff' => $staff,
        ]);
    }
}
