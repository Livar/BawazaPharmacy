<?php

class Controller
{
    protected AuthService $authService;
    protected PharmacyService $pharmacyService;
    protected SettingsService $settingsService;
    protected CsrfService $csrfService;
    protected Notifications $notificationsService;

    public function __construct()
    {
        $pdo = Database::getConnection();
        $this->authService = new AuthService();
        $this->pharmacyService = new PharmacyService($pdo);
        $this->settingsService = new SettingsService($pdo, $this->pharmacyService);
        $this->csrfService = new CsrfService();
        $this->notificationsService = new Notifications($pdo);
    }

    protected function render(string $view, array $data = []): void
    {
        $shared = $this->sharedData();
        extract(array_merge($shared, $data), EXTR_SKIP);
        include __DIR__ . '/../../views/partials/header.php';
        include __DIR__ . '/../../views/' . $view . '.php';
        include __DIR__ . '/../../views/partials/footer.php';
    }

    protected function sharedData(): array
    {
        $user = $this->authService->currentUser();
        $pharmacyId = $this->pharmacyService->currentPharmacyId();
        $appName = $this->settingsService->appName();
        $exchangeRate = $this->settingsService->exchangeRate();
        $pharmacies = $user && $user['role'] === 'admin' ? $this->pharmacyService->activePharmacies() : [];
        $notifications = $pharmacyId ? $this->notificationsService->latest($pharmacyId, 5) : [];
        $notificationCount = $pharmacyId ? $this->notificationsService->unreadCount($pharmacyId) : 0;

        return [
            'currentUser' => $user,
            'currentPharmacyId' => $pharmacyId,
            'appName' => $appName,
            'exchangeRate' => $exchangeRate,
            'pharmacies' => $pharmacies,
            'csrfToken' => $this->csrfService->token(),
            'notifications' => $notifications,
            'notificationCount' => $notificationCount,
        ];
    }
}
