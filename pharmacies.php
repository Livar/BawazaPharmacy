<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
require_admin();

$pdo = get_db_connection();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid session token.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $status = $_POST['status'] ?? 'active';
        if ($name !== '') {
            $stmt = $pdo->prepare('INSERT INTO pharmacies (name, status) VALUES (?, ?)');
            $stmt->execute([$name, $status]);
            $newId = $pdo->lastInsertId();
            $settingsStmt = $pdo->prepare('INSERT INTO settings (pharmacy_id, setting_key, setting_value) VALUES (?, ?, ?)');
            $settingsStmt->execute([$newId, 'exchange_rate', '1500']);
            $settingsStmt->execute([$newId, 'app_name', $name]);
            $message = 'Pharmacy added.';
        } else {
            $message = 'Pharmacy name is required.';
        }
    }
}

$pharmacies = get_pharmacies();

include __DIR__ . '/includes/header.php';
?>
<h1 class="h3 mb-3">Pharmacies</h1>
<?php if ($message): ?>
    <div class="alert alert-info"><?php echo e($message); ?></div>
<?php endif; ?>
<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">Add Pharmacy</h5>
        <form method="post" class="row g-3">
            <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
            <div class="col-md-6">
                <label class="form-label">Pharmacy Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Create Pharmacy</button>
            </div>
        </form>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Active Pharmacies</h5>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pharmacies as $pharmacy): ?>
                        <tr>
                            <td><?php echo e($pharmacy['name']); ?></td>
                            <td><?php echo e($pharmacy['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
