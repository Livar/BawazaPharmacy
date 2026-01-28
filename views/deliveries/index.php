<h1 class="h3 mb-3">Deliveries</h1>
<?php if (!empty($message)): ?>
    <div class="alert alert-info"><?php echo e($message); ?></div>
<?php endif; ?>
<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">New Delivery</h5>
        <form method="post" class="row g-3" action="<?php echo e(routeUrl('deliveries')); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
            <div class="col-md-4">
                <label class="form-label">Receipt Barcode</label>
                <input type="text" name="receipt_barcode" class="form-control" placeholder="Scan barcode" required autofocus>
            </div>
            <div class="col-md-4">
                <label class="form-label">Customer Name</label>
                <input type="text" name="customer_name" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Customer Phone</label>
                <input type="text" name="customer_phone" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Customer Address</label>
                <input type="text" name="customer_address" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Taxi Driver Name</label>
                <input type="text" name="taxi_driver_name" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Taxi Driver Phone</label>
                <input type="text" name="taxi_driver_phone" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Delivery Fee</label>
                <input type="number" step="0.01" name="delivery_fee_amount" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Currency</label>
                <select name="delivery_fee_currency" class="form-select">
                    <option value="IQD">IQD</option>
                    <option value="USD">USD</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Payment Method</label>
                <select name="payment_method" class="form-select">
                    <option value="cash">Cash</option>
                    <option value="fib">FIB</option>
                    <option value="qi">QI</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Customer Payment Status</label>
                <select name="customer_payment_status" class="form-select">
                    <option value="paid">Paid</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="taxi_collects">Taxi Collects</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Amount Collected by Taxi</label>
                <input type="number" step="0.01" name="amount_collected_by_taxi" class="form-control" value="0">
            </div>
            <div class="col-md-2">
                <label class="form-label">Currency</label>
                <select name="amount_collected_currency" class="form-select">
                    <option value="IQD">IQD</option>
                    <option value="USD">USD</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Save Delivery</button>
            </div>
        </form>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Recent Deliveries</h5>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Receipt</th>
                        <th>Customer</th>
                        <th>Taxi</th>
                        <th>Fee</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($deliveries as $delivery): ?>
                        <tr>
                            <td><a href="<?php echo e(routeUrl('delivery_view', ['id' => $delivery['id']])); ?>"><?php echo e($delivery['receipt_barcode']); ?></a></td>
                            <td><?php echo e($delivery['customer_name']); ?></td>
                            <td><?php echo e($delivery['taxi_driver_name']); ?></td>
                            <td><?php echo e(number_format((float) $delivery['delivery_fee_amount'], 2)); ?> <?php echo e($delivery['delivery_fee_currency']); ?></td>
                            <td><span class="badge bg-secondary badge-status"><?php echo e($delivery['status']); ?></span></td>
                            <td><?php echo e($delivery['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
