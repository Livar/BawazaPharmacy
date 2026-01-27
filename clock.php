<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$pdo = get_db_connection();
$user = current_user();
$message = '';

$activeShiftStmt = $pdo->prepare('SELECT * FROM staff_shifts WHERE user_id = ? AND clock_out IS NULL ORDER BY clock_in DESC LIMIT 1');
$activeShiftStmt->execute([$user['id']]);
$activeShift = $activeShiftStmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid session token.';
    } else {
        if (isset($_POST['clock_in'])) {
            if ($activeShift) {
                $message = 'You are already clocked in.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO staff_shifts (user_id, clock_in, created_by) VALUES (?, NOW(), ?)');
                $stmt->execute([$user['id'], $user['id']]);
                $message = 'Clock in recorded.';
            }
        }
        if (isset($_POST['clock_out'])) {
            if (!$activeShift) {
                $message = 'No active shift found.';
            } else {
                $stmt = $pdo->prepare('UPDATE staff_shifts SET clock_out = NOW(), updated_by = ? WHERE id = ?');
                $stmt->execute([$user['id'], $activeShift['id']]);
                $message = 'Clock out recorded.';
            }
        }
    }
    $activeShiftStmt->execute([$user['id']]);
    $activeShift = $activeShiftStmt->fetch();
}

$recentShiftsStmt = $pdo->prepare('SELECT clock_in, clock_out FROM staff_shifts WHERE user_id = ? ORDER BY clock_in DESC LIMIT 10');
$recentShiftsStmt->execute([$user['id']]);
$recentShifts = $recentShiftsStmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<h1 class="h3 mb-3">Clock In / Out</h1>
<?php if ($message): ?>
    <div class="alert alert-info"><?php echo e($message); ?></div>
<?php endif; ?>
<div class="card clock-card shadow-sm">
    <div class="card-body text-center">
        <p class="mb-3">Status: <?php echo $activeShift ? '<span class="text-success">Clocked In</span>' : '<span class="text-danger">Clocked Out</span>'; ?></p>
        <form method="post" class="d-grid gap-2">
            <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
            <button type="submit" name="clock_in" class="btn btn-success clock-button" <?php echo $activeShift ? 'disabled' : ''; ?>>Clock In</button>
            <button type="submit" name="clock_out" class="btn btn-danger clock-button" <?php echo $activeShift ? '' : 'disabled'; ?>>Clock Out</button>
        </form>
    </div>
</div>
<div class="card mt-4">
    <div class="card-body">
        <h5 class="card-title">Recent Shifts</h5>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($recentShifts as $shift): ?>
                    <tr>
                        <td><?php echo e($shift['clock_in']); ?></td>
                        <td><?php echo e($shift['clock_out'] ?? 'Active'); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
