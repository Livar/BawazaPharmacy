<?php
require_once __DIR__ . '/src/bootstrap.php';

$router = new Router();

$router->get('login', [AuthController::class, 'showLogin']);
$router->post('login', [AuthController::class, 'login']);
$router->get('logout', [AuthController::class, 'logout']);

$router->get('dashboard', [DashboardController::class, 'index']);
$router->get('deliveries', [DeliveryController::class, 'index']);
$router->post('deliveries', [DeliveryController::class, 'index']);
$router->get('delivery_view', [DeliveryController::class, 'view']);
$router->post('delivery_view', [DeliveryController::class, 'view']);

$router->get('cash_counts', [CashCountController::class, 'index']);
$router->post('cash_counts', [CashCountController::class, 'index']);

$router->get('staff', [StaffController::class, 'index']);
$router->post('staff', [StaffController::class, 'index']);

$router->get('reports', [ReportsController::class, 'index']);
$router->get('settings', [SettingsController::class, 'index']);
$router->post('settings', [SettingsController::class, 'index']);

$router->get('pharmacies', [PharmacyController::class, 'index']);
$router->post('pharmacies', [PharmacyController::class, 'index']);
$router->post('pharmacy_switch', [PharmacyController::class, 'switch']);

$router->get('clock', [ClockController::class, 'index']);
$router->post('clock', [ClockController::class, 'index']);

$router->get('notification_read', [NotificationController::class, 'read']);
$router->get('export', [ExportController::class, 'export']);

$route = $_GET['route'] ?? 'login';
$router->dispatch($route, $_SERVER['REQUEST_METHOD']);
