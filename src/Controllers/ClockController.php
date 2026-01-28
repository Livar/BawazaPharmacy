<?php

class ClockController extends Controller
{
    public function index(): void
    {
        $this->authService->requireLogin();

        $pdo = Database::getConnection();
        $user = $this->authService->currentUser();
        $message = '';

        $activeShiftStmt = $pdo->prepare('SELECT * FROM staff_shifts WHERE user_id = ? AND clock_out IS NULL ORDER BY clock_in DESC LIMIT 1');
        $activeShiftStmt->execute([$user['id']]);
        $activeShift = $activeShiftStmt->fetch();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->csrfService->verify($_POST['csrf_token'] ?? '')) {
                $message = 'Invalid session token.';
            } else {
                if (isset($_POST['clock_in'])) {
                    if ($activeShift) {
                        $message = 'You are already clocked in.';
                    } else {
                        $stmt = $pdo->prepare('INSERT INTO staff_shifts (user_id, clock_in, created_by) VALUES (?, NOW(), ?)');
                        $stmt->execute([$user['id'], $user['id']]);
                        $message = 'Clock in recorded.';
                    }
                }
                if (isset($_POST['clock_out'])) {
                    if (!$activeShift) {
                        $message = 'No active shift found.';
                    } else {
                        $stmt = $pdo->prepare('UPDATE staff_shifts SET clock_out = NOW(), updated_by = ? WHERE id = ?');
                        $stmt->execute([$user['id'], $activeShift['id']]);
                        $message = 'Clock out recorded.';
                    }
                }
            }
            $activeShiftStmt->execute([$user['id']]);
            $activeShift = $activeShiftStmt->fetch();
        }

        $recentShiftsStmt = $pdo->prepare('SELECT clock_in, clock_out FROM staff_shifts WHERE user_id = ? ORDER BY clock_in DESC LIMIT 10');
        $recentShiftsStmt->execute([$user['id']]);
        $recentShifts = $recentShiftsStmt->fetchAll();

        $this->render('clock/index', [
            'message' => $message,
            'activeShift' => $activeShift,
            'recentShifts' => $recentShifts,
        ]);
    }
}
