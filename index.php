<?php
require_once __DIR__ . '/includes/functions.php';
start_secure_session();

if (current_user()) {
    header('Location: dashboard.php');
    exit;
}

$message = '';
$dbError = '';
$pharmacies = [];

try {
    $pdo = get_db_connection();
    $pharmacies = get_pharmacies();
} catch (PDOException $exception) {
    $dbError = 'Database connection is not configured yet.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $pharmacyId = (int) ($_POST['pharmacy_id'] ?? 0);

    if ($dbError) {
        $message = $dbError;
    } else {
        $stmt = $pdo->prepare('SELECT id, name, username, password_hash, role, status, pharmacy_id FROM users WHERE username = ? AND pharmacy_id = ? AND status = "active"');
        $stmt->execute([$username, $pharmacyId]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            regenerate_session();
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'username' => $user['username'],
                'role' => $user['role'],
            ];
            $_SESSION['pharmacy_id'] = (int) $user['pharmacy_id'];
            header('Location: dashboard.php');
            exit;
        }

        $message = 'Invalid username, pharmacy, or password.';
    }
}

include __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h1 class="h4 mb-2 text-center">Login</h1>
                <p class="text-muted text-center mb-4">Choose your pharmacy and sign in.</p>
                <?php if ($dbError): ?>
                    <div class="alert alert-warning"><?php echo e($dbError); ?></div>
                <?php endif; ?>
                <?php if ($message): ?>
                    <div class="alert alert-danger"><?php echo e($message); ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Pharmacy</label>
                        <select name="pharmacy_id" class="form-select" required>
                            <option value="">Select pharmacy</option>
                            <?php foreach ($pharmacies as $pharmacy): ?>
                                <option value="<?php echo e((string) $pharmacy['id']); ?>"><?php echo e($pharmacy['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Sign In</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
