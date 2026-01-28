<?php

class SettingsController extends Controller
{
    public function index(): void
    {
        $this->authService->requireLogin();
        $this->authService->requireAdmin();

        $message = '';
        $exchangeRate = $this->settingsService->exchangeRate();
        $appName = $this->settingsService->appName();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->csrfService->verify($_POST['csrf_token'] ?? '')) {
                $message = 'Invalid session token.';
            } else {
                $exchangeRate = trim($_POST['exchange_rate'] ?? '');
                $appName = trim($_POST['app_name'] ?? '');
                if ($exchangeRate !== '' && $appName !== '') {
                    $this->settingsService->upsert('exchange_rate', $exchangeRate);
                    $this->settingsService->upsert('app_name', $appName);
                    $message = 'Settings updated.';
                } else {
                    $message = 'Please fill in all required fields.';
                }
            }
        }

        $this->render('settings/index', [
            'message' => $message,
            'exchangeRate' => $exchangeRate,
            'appNameSetting' => $appName,
        ]);
    }
}
