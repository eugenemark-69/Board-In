<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin']);
require_once __DIR__ . '/../includes/header.php';

// Get all transactions
$stmt = $conn->query('
    SELECT t.*, b.booking_reference, u.full_name as student_name, bh.title as property_name
    FROM transactions t
    LEFT JOIN bookings b ON b.id = t.booking_id
    LEFT JOIN users u ON u.id = b.user_id
    LEFT JOIN boarding_houses bh ON bh.id = b.bh_id
    ORDER BY t.created_at DESC
    LIMIT 100
');
$transactions = $stmt->fetch_all(MYSQLI_ASSOC);

// Calculate statistics
$stats = $conn->query('
    SELECT 
        COUNT(*) as total_transactions,
        SUM(CASE WHEN status = "completed" THEN amount ELSE 0 END) as total_revenue,
        SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as pending_amount,
        SUM(CASE WHEN status = "completed" THEN amount * ' . PLATFORM_COMMISSION_RATE . ' ELSE 0 END) as total_commission
    FROM transactions
    WHERE transaction_type = "booking_payment"
')->fetch_assoc();
?>

<div class="container-fluid mt-4">
    <h2 class="mb-4"><i class="bi bi-currency-exchange me-2"></i>Payment Management</h2>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6><i class="bi bi-cash-stack me-2"></i>Total Revenue</h6>
                    <h3>₱<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                    <small>From completed bookings</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6><i class="bi bi-piggy-bank me-2"></i>Platform Commission</h6>
                    <h3>₱<?php echo number_format($stats['total_commission'], 2); ?></h3>
                    <small><?php echo (PLATFORM_COMMISSION_RATE * 100); ?>% commission earned</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6><i class="bi bi-hourglass me-2"></i>Pending</h6>
                    <h3>₱<?php echo number_format($stats['pending_amount'], 2); ?></h3>
                    <small>Awaiting confirmation</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6><i class="bi bi-receipt me-2"></i>Transactions</h6>
                    <h3><?php echo number_format($stats['total_transactions']); ?></h3>
                    <small>Total processed</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card shadow">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Recent Transactions</h5>
            <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Print Report
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Student</th>
                            <th>Property</th>
                            <th>Booking Ref</th>
                            <th>Method</th>
                            <th>Amount</th>
                            <th>Commission</th>
                            <th>Status</th>
                            <th>Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $tx): 
                            $commission = $tx['amount'] * PLATFORM_COMMISSION_RATE;
                        ?>
                            <tr>
                                <td><?php echo $tx['id']; ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($tx['created_at'])); ?></td>
                                <td><?php echo esc_attr($tx['student_name']); ?></td>
                                <td>
                                    <small><?php echo esc_attr(substr($tx['property_name'], 0, 30)); ?></small>
                                </td>
                                <td><strong><?php echo esc_attr($tx['booking_reference']); ?></strong></td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo strtoupper($tx['payment_method']); ?>
                                    </span>
                                </td>
                                <td><strong>₱<?php echo number_format($tx['amount'], 2); ?></strong></td>
                                <td>
                                    <small class="text-success">
                                        ₱<?php echo number_format($commission, 2); ?>
                                    </small>
                                </td>
                                <td>
                                    <?php if ($tx['status'] === 'completed'): ?>
                                        <span class="badge bg-success">Completed</span>
                                    <?php elseif ($tx['status'] === 'pending'): ?>
                                        <span class="badge bg-warning">Pending</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Failed</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo esc_attr(substr($tx['payment_reference'] ?? 'N/A', 0, 20)); ?>
                                    </small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>