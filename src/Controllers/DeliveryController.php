<?php

class DeliveryController extends Controller
{
    public function index(): void
    {
        $this->authService->requireLogin();
        $this->authService->requireAdmin();

        $pdo = Database::getConnection();
        $message = '';
        $pharmacyId = $this->pharmacyService->currentPharmacyId();
        $taxiService = new TaxiService($pdo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->csrfService->verify($_POST['csrf_token'] ?? '')) {
                $message = 'Invalid session token.';
            } else {
                $driverName = trim($_POST['taxi_driver_name'] ?? '');
                $driverPhone = trim($_POST['taxi_driver_phone'] ?? '');
                $driverId = $taxiService->getOrCreateDriver($pharmacyId, $driverName, $driverPhone);

                $stmt = $pdo->prepare('INSERT INTO deliveries (pharmacy_id, taxi_driver_id, receipt_barcode, customer_name, customer_phone, customer_address, taxi_driver_name, taxi_driver_phone, delivery_fee_amount, delivery_fee_currency, payment_method, customer_payment_status, amount_collected_by_taxi, amount_collected_currency, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([
                    $pharmacyId,
                    $driverId,
                    trim($_POST['receipt_barcode'] ?? ''),
                    trim($_POST['customer_name'] ?? ''),
                    trim($_POST['customer_phone'] ?? ''),
                    trim($_POST['customer_address'] ?? ''),
                    $driverName,
                    $driverPhone,
                    (float) ($_POST['delivery_fee_amount'] ?? 0),
                    $_POST['delivery_fee_currency'] ?? 'IQD',
                    $_POST['payment_method'] ?? 'cash',
                    $_POST['customer_payment_status'] ?? 'paid',
                    (float) ($_POST['amount_collected_by_taxi'] ?? 0),
                    $_POST['amount_collected_currency'] ?? 'IQD',
                    'created',
                    $this->authService->currentUser()['id'],
                ]);
                $deliveryId = (int) $pdo->lastInsertId();
                $eventStmt = $pdo->prepare('INSERT INTO delivery_events (delivery_id, event, note, created_by) VALUES (?, ?, ?, ?)');
                $eventStmt->execute([$deliveryId, 'created', 'Delivery created.', $this->authService->currentUser()['id']]);
                $this->notificationsService->create($pharmacyId, $this->authService->currentUser()['id'], 'New delivery created for ' . $driverName . '.', 'index.php?route=delivery_view&id=' . $deliveryId);
                $message = 'Delivery created successfully.';
            }
        }

        $deliveriesStmt = $pdo->prepare("SELECT id, receipt_barcode, customer_name, taxi_driver_name, delivery_fee_amount, delivery_fee_currency, status, created_at FROM deliveries WHERE is_active = 1 AND pharmacy_id = ? ORDER BY created_at DESC LIMIT 50");
        $deliveriesStmt->execute([$pharmacyId]);
        $deliveries = $deliveriesStmt->fetchAll();

        $this->render('deliveries/index', [
            'message' => $message,
            'deliveries' => $deliveries,
        ]);
    }

    public function view(): void
    {
        $this->authService->requireLogin();
        $this->authService->requireAdmin();

        $pdo = Database::getConnection();
        $deliveryId = (int) ($_GET['id'] ?? 0);
        $pharmacyId = $this->pharmacyService->currentPharmacyId();
        $taxiService = new TaxiService($pdo);

        $stmt = $pdo->prepare('SELECT * FROM deliveries WHERE id = ? AND is_active = 1 AND pharmacy_id = ?');
        $stmt->execute([$deliveryId, $pharmacyId]);
        $delivery = $stmt->fetch();

        if (!$delivery) {
            header('Location: ' . routeUrl('deliveries'));
            exit;
        }

        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->csrfService->verify($_POST['csrf_token'] ?? '')) {
                $message = 'Invalid session token.';
            } else {
                $newStatus = $_POST['status'] ?? $delivery['status'];
                $note = trim($_POST['note'] ?? '');
                $previousStatus = $delivery['status'];
                if ($newStatus !== $previousStatus) {
                    $updateStmt = $pdo->prepare('UPDATE deliveries SET status = ?, updated_at = NOW() WHERE id = ? AND pharmacy_id = ?');
                    $updateStmt->execute([$newStatus, $deliveryId, $pharmacyId]);
                    $eventStmt = $pdo->prepare('INSERT INTO delivery_events (delivery_id, event, note, created_by) VALUES (?, ?, ?, ?)');
                    $eventStmt->execute([$deliveryId, $newStatus, $note ?: 'Status updated.', $this->authService->currentUser()['id']]);
                    if ($newStatus === 'money_collected' && $previousStatus !== 'money_collected') {
                        if (!empty($delivery['taxi_driver_id'])) {
                            $taxiService->updateBalance((int) $delivery['taxi_driver_id'], (float) $delivery['amount_collected_by_taxi'], $delivery['amount_collected_currency'], 'add');
                        }
                    }
                    if ($newStatus === 'settled' && $previousStatus === 'money_collected') {
                        if (!empty($delivery['taxi_driver_id'])) {
                            $taxiService->updateBalance((int) $delivery['taxi_driver_id'], (float) $delivery['amount_collected_by_taxi'], $delivery['amount_collected_currency'], 'subtract');
                        }
                    }
                    $this->notificationsService->create($pharmacyId, $this->authService->currentUser()['id'], 'Delivery ' . $delivery['receipt_barcode'] . ' updated to ' . $newStatus . '.', 'index.php?route=delivery_view&id=' . $deliveryId);
                    $message = 'Delivery updated.';
                    $stmt->execute([$deliveryId, $pharmacyId]);
                    $delivery = $stmt->fetch();
                } else {
                    $message = 'No status change made.';
                }
            }
        }

        $eventsStmt = $pdo->prepare('SELECT e.event, e.note, e.created_at, u.name FROM delivery_events e LEFT JOIN users u ON e.created_by = u.id WHERE e.delivery_id = ? ORDER BY e.created_at DESC');
        $eventsStmt->execute([$deliveryId]);
        $events = $eventsStmt->fetchAll();

        $this->render('deliveries/show', [
            'delivery' => $delivery,
            'events' => $events,
            'message' => $message,
        ]);
    }
}
