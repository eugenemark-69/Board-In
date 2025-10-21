<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/header.php';

$conn = getDB();

$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 6;
$offset = ($page - 1) * $perPage;

$sort = ($_GET['sort'] ?? 'newest') === 'oldest' ? 'oldest' : 'newest';

// Checkbox filters
$wifiFilter = isset($_GET['wifi']);
$laundryFilter = isset($_GET['laundry']);
$kitchenFilter = isset($_GET['kitchen']);
$bipsuFilter = isset($_GET['bipsu']);

// ✅ Helper to check if a column exists (fixed for PDO)
function column_exists($conn, $table, $column) {
    $stmt = $conn->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
    $stmt->execute([$column]);
    return $stmt->rowCount() > 0;
}

// ✅ Correct table name and alias
$baseSelect = 'SELECT * FROM listings';
$where = [];
$bind_values = [];

// 🔒 CRITICAL: Only show approved listings to students
$where[] = "status = 'approved'";

if ($q !== '') {
    $where[] = '(title LIKE ? OR description LIKE ?)';
    $like = '%' . $q . '%';
    $bind_values[] = $like;
    $bind_values[] = $like;
}

// ✅ Make sure filters match the right table and columns
if ($wifiFilter && column_exists($conn, 'listings', 'wifi')) {
    $where[] = 'wifi = 1';
}
if ($laundryFilter && column_exists($conn, 'listings', 'laundry')) {
    $where[] = 'laundry = 1';
}
if ($kitchenFilter && column_exists($conn, 'listings', 'kitchen')) {
    $where[] = 'kitchen = 1';
}
if ($bipsuFilter && column_exists($conn, 'listings', 'close_to_bipsu')) {
    $where[] = 'close_to_bipsu = 1';
}

$sql = $baseSelect;
$countSql = 'SELECT COUNT(*) AS total FROM listings';

if (!empty($where)) {
    $clause = ' WHERE ' . implode(' AND ', $where);
    $sql .= $clause;
    $countSql .= $clause;
}

$sql .= $sort === 'oldest' ? ' ORDER BY id ASC' : ' ORDER BY id DESC';

// ✅ Count total
$stmtc = $conn->prepare($countSql);
$stmtc->execute($bind_values);
$total = (int)$stmtc->fetchColumn();

// ✅ Fetch listings
$sql .= ' LIMIT ?, ?';
$stmt = $conn->prepare($sql);
$bind_values_with_limits = array_merge($bind_values, [$offset, $perPage]);
$stmt->execute($bind_values_with_limits);
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pages = max(1, ceil($total / $perPage));
?>

<div class="container mt-4">
    <h2><i class="bi bi-search"></i> Search Listings</h2>
    <form class="mb-4" method="get">
        <div class="row g-2 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <input name="q" value="<?php echo htmlspecialchars($q); ?>" class="form-control" placeholder="Search by title or description">
                    <button class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
                </div>
            </div>
            <div class="col-md-3">
                <select name="sort" class="form-select">
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                    <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2 flex-wrap">
                    <label class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" name="wifi" <?php echo $wifiFilter ? 'checked' : ''; ?>>
                        <span class="form-check-label">WiFi</span>
                    </label>
                    <label class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" name="laundry" <?php echo $laundryFilter ? 'checked' : ''; ?>>
                        <span class="form-check-label">Laundry</span>
                    </label>
                    <label class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" name="kitchen" <?php echo $kitchenFilter ? 'checked' : ''; ?>>
                        <span class="form-check-label">Kitchen</span>
                    </label>
                    <label class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" name="bipsu" <?php echo $bipsuFilter ? 'checked' : ''; ?>>
                        <span class="form-check-label">Close to BIPSU</span>
                    </label>
                </div>
            </div>
        </div>
    </form>

    <?php if (count($res) === 0): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> No approved listings found matching your search criteria.
        </div>
    <?php else: ?>
        <p class="text-muted mb-3">Found <?php echo $total; ?> approved listing(s)</p>
        <div class="row">
            <?php foreach ($res as $row): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <?php
                        $image = !empty($row['image']) ? '/board-in/' . $row['image'] : '/board-in/assets/images/boardinghouse.jpg';
                        ?>
                        <img src="<?php echo htmlspecialchars($image); ?>" class="card-img-top listing-thumb" style="height: 220px; object-fit: cover;" alt="Boarding House">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                            <p class="mb-2">
                                <strong class="text-primary fs-5">₱<?php echo number_format($row['price'], 2); ?></strong>
                                <small class="text-muted">/ month</small>
                            </p>
                            <p class="text-muted mb-2">
                                <i class="bi bi-geo-alt text-danger"></i> <?php echo htmlspecialchars($row['location']); ?>
                            </p>
                            <p class="card-text small text-muted"><?php echo htmlspecialchars(substr($row['description'], 0, 100)); ?>...</p>
                            <div class="mb-3 d-flex gap-2 flex-wrap">
                                <?php if (!empty($row['wifi'])): ?><span class="badge bg-light text-dark border"><i class="bi bi-wifi"></i> WiFi</span><?php endif; ?>
                                <?php if (!empty($row['laundry'])): ?><span class="badge bg-light text-dark border"><i class="bi bi-droplet"></i> Laundry</span><?php endif; ?>
                                <?php if (!empty($row['kitchen'])): ?><span class="badge bg-light text-dark border"><i class="bi bi-cup-hot"></i> Kitchen</span><?php endif; ?>
                                <?php if (!empty($row['close_to_bipsu'])): ?><span class="badge bg-light text-dark border"><i class="bi bi-building"></i> BIPSU</span><?php endif; ?>
                            </div>
                            <a href="/board-in/pages/listing.php?id=<?php echo $row['id']; ?>" class="btn btn-primary w-100">
                                <i class="bi bi-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($pages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $pages; $i++):
                    $qp = $_GET;
                    $qp['page'] = $i;
                    $qs = http_build_query($qp);
                ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?<?php echo $qs; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>