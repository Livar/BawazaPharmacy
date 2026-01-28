<h1 class="h3 mb-3">Admin Dashboard</h1>
<div class="row g-3">
    <div class="col-md-4">
        <div class="card border-primary">
            <div class="card-body">
                <h5 class="card-title">Unsettled Deliveries</h5>
                <p class="display-6 mb-0"><?php echo e((string) $unsettled); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Recent Deliveries</h5>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Receipt</th>
                                <th>Customer</th>
                                <th>Taxi Driver</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recentDeliveries as $delivery): ?>
                            <tr>
                                <td><a href="<?php echo e(routeUrl('delivery_view', ['id' => $delivery['id']])); ?>"><?php echo e($delivery['receipt_barcode']); ?></a></td>
                                <td><?php echo e($delivery['customer_name']); ?></td>
                                <td><?php echo e($delivery['taxi_driver_name']); ?></td>
                                <td><span class="badge bg-secondary badge-status"><?php echo e($delivery['status']); ?></span></td>
                                <td><?php echo e($delivery['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row g-3 mt-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Recent Cash Counts</h5>
                <ul class="list-group list-group-flush">
                    <?php foreach ($cashCounts as $count): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><?php echo e($count['count_date']); ?></span>
                            <span class="text-muted small"><?php echo e($count['notes']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
