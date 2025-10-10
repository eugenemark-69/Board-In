<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
// require landlords (previously bh_manager) or admin
require_role(['landlord','admin']);
require_once __DIR__ . '/../includes/header.php';

$manager_id = $_SESSION['user']['id'];
// keep using manager_id column for compatibility with existing data
$stmt = $conn->prepare('SELECT * FROM boarding_houses WHERE manager_id = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $manager_id);
$stmt->execute();
$res = $stmt->get_result();
?>

<h2>My Listings</h2>
<?php while ($row = $res->fetch_assoc()): ?>
  <div class="card mb-3">
    <div class="card-body">
      <h5 class="card-title"><?php echo esc_attr($row['title']); ?></h5>
      <p class="card-text"><?php echo htmlspecialchars($row['description']); ?></p>
      <a href="/board-in/pages/listing.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">View</a>
    </div>
  </div>
<?php endwhile; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
