<h1 class="h3 mb-3">Pharmacies</h1>
<?php if (!empty($message)): ?>
    <div class="alert alert-info"><?php echo e($message); ?></div>
<?php endif; ?>
<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">Add Pharmacy</h5>
        <form method="post" class="row g-3" action="<?php echo e(routeUrl('pharmacies')); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
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
