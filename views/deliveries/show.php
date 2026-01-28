<h1 class="h3 mb-3">Delivery Details</h1>
<?php if (!empty($message)): ?>
    <div class="alert alert-success"><?php echo e($message); ?></div>
<?php endif; ?>
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Receipt <?php echo e($delivery['receipt_barcode']); ?></h5>
                <p class="mb-1"><strong>Customer:</strong> <?php echo e($delivery['customer_name']); ?></p>
                <p class="mb-1"><strong>Phone:</strong> <?php echo e($delivery['customer_phone']); ?></p>
                <p class="mb-1"><strong>Address:</strong> <?php echo e($delivery['customer_address']); ?></p>
                <p class="mb-1"><strong>Taxi Driver:</strong> <?php echo e($delivery['taxi_driver_name']); ?> <?php echo e($delivery['taxi_driver_phone']); ?></p>
                <p class="mb-1"><strong>Delivery Fee:</strong> <?php echo e(number_format((float) $delivery['delivery_fee_amount'], 2)); ?> <?php echo e($delivery['delivery_fee_currency']); ?></p>
                <p class="mb-1"><strong>Payment Method:</strong> <?php echo e($delivery['payment_method']); ?></p>
                <p class="mb-1"><strong>Customer Payment:</strong> <?php echo e($delivery['customer_payment_status']); ?></p>
                <p class="mb-1"><strong>Taxi Collected:</strong> <?php echo e(number_format((float) $delivery['amount_collected_by_taxi'], 2)); ?> <?php echo e($delivery['amount_collected_currency']); ?></p>
                <p class="mb-1"><strong>Status:</strong> <span class="badge bg-secondary badge-status"><?php echo e($delivery['status']); ?></span></p>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Update Status</h5>
                <form method="post" action="<?php echo e(routeUrl('delivery_view', ['id' => $delivery['id']])); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <?php foreach (['created', 'given_to_taxi', 'delivered', 'money_collected', 'settled'] as $status): ?>
                                <option value="<?php echo e($status); ?>" <?php echo $delivery['status'] === $status ? 'selected' : ''; ?>><?php echo e($status); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea name="note" class="form-control" rows="3"></textarea>
                    </div>
                    <button class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="card mt-3">
    <div class="card-body">
        <h5 class="card-title">Event Log</h5>
        <ul class="list-group list-group-flush">
            <?php foreach ($events as $event): ?>
                <li class="list-group-item">
                    <strong><?php echo e($event['event']); ?></strong> - <?php echo e($event['note']); ?><br>
                    <small class="text-muted">By <?php echo e($event['name'] ?? 'System'); ?> at <?php echo e($event['created_at']); ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
