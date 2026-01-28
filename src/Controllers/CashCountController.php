<?php

class CashCountController extends Controller
{
    public function index(): void
    {
        $this->authService->requireLogin();
        $this->authService->requireAdmin();

        $pdo = Database::getConnection();
        $message = '';
        $pharmacyId = $this->pharmacyService->currentPharmacyId();

        $denominationsIQD = [50000, 25000, 10000, 5000, 1000, 500, 250];
        $denominationsUSD = [100, 50, 20, 10, 5, 1];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->csrfService->verify($_POST['csrf_token'] ?? '')) {
                $message = 'Invalid session token.';
            } else {
                $countDate = $_POST['count_date'] ?? date('Y-m-d');
                $notes = trim($_POST['notes'] ?? '');
                $stmt = $pdo->prepare('INSERT INTO cash_counts (pharmacy_id, count_date, notes, created_by) VALUES (?, ?, ?, ?)');
                $stmt->execute([$pharmacyId, $countDate, $notes, $this->authService->currentUser()['id']]);
                $countId = $pdo->lastInsertId();

                $itemStmt = $pdo->prepare('INSERT INTO cash_count_items (cash_count_id, currency, denomination, quantity) VALUES (?, ?, ?, ?)');
                foreach (['IQD' => $denominationsIQD, 'USD' => $denominationsUSD] as $currency => $denoms) {
                    foreach ($denoms as $denom) {
                        $qty = (int) ($_POST['denom'][$currency][$denom] ?? 0);
                        $itemStmt->execute([$countId, $currency, $denom, $qty]);
                    }
                }
                $this->notificationsService->create($pharmacyId, $this->authService->currentUser()['id'], 'New cash count saved for ' . $countDate . '.', routeUrl('cash_counts'));
                $message = 'Cash count saved.';
            }
        }

        $countsStmt = $pdo->prepare('SELECT id, count_date, notes FROM cash_counts WHERE pharmacy_id = ? ORDER BY count_date DESC LIMIT 30');
        $countsStmt->execute([$pharmacyId]);
        $counts = $countsStmt->fetchAll();

        $this->render('cash_counts/index', [
            'message' => $message,
            'denominationsIQD' => $denominationsIQD,
            'denominationsUSD' => $denominationsUSD,
            'counts' => $counts,
        ]);
    }
}
