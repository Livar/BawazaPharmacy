<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h1 class="h4 mb-2 text-center">Login</h1>
                <p class="text-muted text-center mb-4">Choose your pharmacy and sign in.</p>
                <?php if (!empty($dbError)): ?>
                    <div class="alert alert-warning"><?php echo e($dbError); ?></div>
                <?php endif; ?>
                <?php if (!empty($message)): ?>
                    <div class="alert alert-danger"><?php echo e($message); ?></div>
                <?php endif; ?>
                <form method="post" action="<?php echo e(routeUrl('login')); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
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
