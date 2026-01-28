<?php

class ReportsController extends Controller
{
    public function index(): void
    {
        $this->authService->requireLogin();
        $this->authService->requireAdmin();

        $pdo = Database::getConnection();
        $pharmacyId = $this->pharmacyService->currentPharmacyId();

        $staffHoursStmt = $pdo->prepare("SELECT u.name, SUM(TIMESTAMPDIFF(MINUTE, s.clock_in, COALESCE(s.clock_out, NOW())))/60 AS hours FROM staff_shifts s JOIN users u ON s.user_id = u.id WHERE u.pharmacy_id = ? GROUP BY u.name ORDER BY u.name");
        $staffHoursStmt->execute([$pharmacyId]);
        $staffHours = $staffHoursStmt->fetchAll();

        $taxiBalancesStmt = $pdo->prepare("SELECT name, phone, balance_iqd, balance_usd FROM taxi_drivers WHERE pharmacy_id = ? AND status = 'active' ORDER BY name");
        $taxiBalancesStmt->execute([$pharmacyId]);
        $taxiBalances = $taxiBalancesStmt->fetchAll();

        $deliverySummaryStmt = $pdo->prepare("SELECT status, COUNT(*) AS total FROM deliveries WHERE is_active = 1 AND pharmacy_id = ? GROUP BY status ORDER BY status");
        $deliverySummaryStmt->execute([$pharmacyId]);
        $deliverySummary = $deliverySummaryStmt->fetchAll();

        $cashHistoryStmt = $pdo->prepare("SELECT c.id, c.count_date, c.notes, SUM(i.denomination * i.quantity) AS total_amount, i.currency FROM cash_counts c JOIN cash_count_items i ON c.id = i.cash_count_id WHERE c.pharmacy_id = ? GROUP BY c.id, i.currency ORDER BY c.count_date DESC");
        $cashHistoryStmt->execute([$pharmacyId]);
        $cashHistory = $cashHistoryStmt->fetchAll();

        $this->render('reports/index', [
            'staffHours' => $staffHours,
            'taxiBalances' => $taxiBalances,
            'deliverySummary' => $deliverySummary,
            'cashHistory' => $cashHistory,
        ]);
    }
}
