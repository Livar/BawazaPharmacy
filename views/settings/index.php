<h1 class="h3 mb-3">Settings</h1>
<?php if (!empty($message)): ?>
    <div class="alert alert-success"><?php echo e($message); ?></div>
<?php endif; ?>
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Pharmacy Settings</h5>
        <form method="post" class="row g-3" action="<?php echo e(routeUrl('settings')); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
            <div class="col-md-6">
                <label class="form-label">Pharmacy Name</label>
                <input type="text" name="app_name" class="form-control" value="<?php echo e($appNameSetting); ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">1 USD = IQD</label>
                <input type="number" name="exchange_rate" class="form-control" value="<?php echo e($exchangeRate); ?>" step="0.01" required>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
