<?php

class PharmacyController extends Controller
{
    public function index(): void
    {
        $this->authService->requireLogin();
        $this->authService->requireAdmin();

        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->csrfService->verify($_POST['csrf_token'] ?? '')) {
                $message = 'Invalid session token.';
            } else {
                $name = trim($_POST['name'] ?? '');
                $status = $_POST['status'] ?? 'active';
                if ($name !== '') {
                    $newId = $this->pharmacyService->create($name, $status);
                    $this->settingsService->upsert('exchange_rate', '1500', $newId);
                    $this->settingsService->upsert('app_name', $name, $newId);
                    $message = 'Pharmacy added.';
                } else {
                    $message = 'Pharmacy name is required.';
                }
            }
        }

        $pharmacies = $this->pharmacyService->activePharmacies();

        $this->render('pharmacies/index', [
            'message' => $message,
            'pharmacies' => $pharmacies,
        ]);
    }

    public function switch(): void
    {
        $this->authService->requireLogin();
        $this->authService->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . routeUrl('dashboard'));
            exit;
        }

        if (!$this->csrfService->verify($_POST['csrf_token'] ?? '')) {
            header('Location: ' . routeUrl('dashboard'));
            exit;
        }

        $pharmacyId = (int) ($_POST['pharmacy_id'] ?? 0);
        $pharmacy = $this->pharmacyService->getActivePharmacy($pharmacyId);
        if ($pharmacy) {
            $this->pharmacyService->setCurrentPharmacy($pharmacyId);
        }

        header('Location: ' . routeUrl('dashboard'));
        exit;
    }
}
