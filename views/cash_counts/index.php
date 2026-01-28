<h1 class="h3 mb-3">Cash Counts</h1>
<?php if (!empty($message)): ?>
    <div class="alert alert-success"><?php echo e($message); ?></div>
<?php endif; ?>
<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">New Cash Count</h5>
        <form method="post" action="<?php echo e(routeUrl('cash_counts')); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Count Date</label>
                    <input type="date" name="count_date" class="form-control" value="<?php echo e(date('Y-m-d')); ?>">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Notes</label>
                    <input type="text" name="notes" class="form-control">
                </div>
            </div>
            <div class="row g-4 mt-3">
                <div class="col-lg-6">
                    <h6>IQD</h6>
                    <div class="table-responsive" id="iqd-table">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Denomination</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($denominationsIQD as $denom): ?>
                                <tr data-denomination="<?php echo e((string) $denom); ?>">
                                    <td><?php echo e((string) $denom); ?></td>
                                    <td><input type="number" min="0" name="denom[IQD][<?php echo e((string) $denom); ?>]" class="form-control form-control-sm" value="0"></td>
                                    <td class="subtotal">0</td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2">Total</th>
                                    <th class="total-cell">0</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="col-lg-6">
                    <h6>USD</h6>
                    <div class="table-responsive" id="usd-table">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Denomination</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($denominationsUSD as $denom): ?>
                                <tr data-denomination="<?php echo e((string) $denom); ?>">
                                    <td><?php echo e((string) $denom); ?></td>
                                    <td><input type="number" min="0" name="denom[USD][<?php echo e((string) $denom); ?>]" class="form-control form-control-sm" value="0"></td>
                                    <td class="subtotal">0</td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2">Total</th>
                                    <th class="total-cell">0</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Save Count</button>
        </form>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Cash Count History</h5>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($counts as $count): ?>
                        <tr>
                            <td><?php echo e($count['count_date']); ?></td>
                            <td><?php echo e($count['notes']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>initCashCount('iqd-table');initCashCount('usd-table');</script>
