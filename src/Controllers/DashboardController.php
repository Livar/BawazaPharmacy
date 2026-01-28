<?php

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->authService->requireLogin();
        $this->authService->requireAdmin();

        $pharmacyId = $this->pharmacyService->currentPharmacyId();
        $pdo = Database::getConnection();

        $unsettledStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM deliveries WHERE status != 'settled' AND is_active = 1 AND pharmacy_id = ?");
        $unsettledStmt->execute([$pharmacyId]);
        $unsettled = $unsettledStmt->fetch()['total'] ?? 0;

        $recentDeliveriesStmt = $pdo->prepare("SELECT id, receipt_barcode, customer_name, taxi_driver_name, status, created_at FROM deliveries WHERE is_active = 1 AND pharmacy_id = ? ORDER BY created_at DESC LIMIT 5");
        $recentDeliveriesStmt->execute([$pharmacyId]);
        $recentDeliveries = $recentDeliveriesStmt->fetchAll();

        $cashCountStmt = $pdo->prepare("SELECT count_date, notes FROM cash_counts WHERE pharmacy_id = ? ORDER BY count_date DESC LIMIT 5");
        $cashCountStmt->execute([$pharmacyId]);
        $cashCounts = $cashCountStmt->fetchAll();

        $this->render('dashboard/index', [
            'unsettled' => $unsettled,
            'recentDeliveries' => $recentDeliveries,
            'cashCounts' => $cashCounts,
        ]);
    }
}
