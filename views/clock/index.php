<h1 class="h3 mb-3">Clock In / Out</h1>
<?php if (!empty($message)): ?>
    <div class="alert alert-info"><?php echo e($message); ?></div>
<?php endif; ?>
<div class="card clock-card shadow-sm">
    <div class="card-body text-center">
        <p class="mb-3">Status: <?php echo $activeShift ? '<span class="text-success">Clocked In</span>' : '<span class="text-danger">Clocked Out</span>'; ?></p>
        <form method="post" class="d-grid gap-2" action="<?php echo e(routeUrl('clock')); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
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
