<?php
require_once __DIR__ . '/../config/session.php';
require_role(['admin']);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/header.php';

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="transactions.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['id','booking_id','transaction_type','amount','payment_method','payment_reference','status','processed_at','created_at']);
    $res = $conn->query('SELECT id, booking_id, transaction_type, amount, payment_method, payment_reference, status, processed_at, created_at FROM transactions ORDER BY created_at DESC');
    while ($row = $res->fetch_assoc()) fputcsv($out, $row);
    fclose($out);
    exit;
}

$res = $conn->query('SELECT t.*, b.booking_reference FROM transactions t LEFT JOIN bookings b ON b.id = t.booking_id ORDER BY t.created_at DESC');

?>

<h2>Transactions</h2>
<p><a href="/board-in/admin/transactions.php?export=csv" class="btn btn-sm btn-secondary">Export CSV</a></p>
<?php while ($r = $res->fetch_assoc()): ?>
  <div class="card mb-2">
    <div class="card-body">
      <h5><?php echo esc_attr($r['transaction_type']); ?> — ₱<?php echo number_format($r['amount'],2); ?></h5>
      <p>Booking: <?php echo esc_attr($r['booking_reference']); ?> | Status: <?php echo esc_attr($r['status']); ?></p>
      <p>Ref: <?php echo esc_attr($r['payment_reference']); ?> | Method: <?php echo esc_attr($r['payment_method']); ?></p>
    </div>
  </div>
<?php endwhile; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
