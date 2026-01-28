<?php

class NotificationController extends Controller
{
    public function read(): void
    {
        $this->authService->requireLogin();

        $notificationId = (int) ($_GET['id'] ?? 0);
        $redirect = $_GET['redirect'] ?? routeUrl('dashboard');
        $pharmacyId = $this->pharmacyService->currentPharmacyId();

        if ($notificationId && $pharmacyId) {
            $this->notificationsService->markRead($notificationId, $pharmacyId);
        }

        header('Location: ' . $redirect);
        exit;
    }
}
