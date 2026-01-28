<h1 class="h3 mb-3">Staff</h1>
<?php if (!empty($message)): ?>
    <div class="alert alert-info"><?php echo e($message); ?></div>
<?php endif; ?>
<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">Add Staff Member</h5>
        <form method="post" class="row g-3" action="<?php echo e(routeUrl('staff')); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
            <div class="col-md-4">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Role</label>
                <select name="role" class="form-select">
                    <option value="staff">Staff</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Create Staff</button>
            </div>
        </form>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Current Staff</h5>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($staff as $member): ?>
                        <tr>
                            <td><?php echo e($member['name']); ?></td>
                            <td><?php echo e($member['username']); ?></td>
                            <td><?php echo e($member['role']); ?></td>
                            <td><?php echo e($member['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
