<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/header.php';

$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 6;
$offset = ($page - 1) * $perPage;

// sorting
$sort = ($_GET['sort'] ?? 'newest') === 'oldest' ? 'oldest' : 'newest';

// read checkbox inputs (keep for form state even if DB column missing)
$wifiFilter = isset($_GET['wifi']);
$laundryFilter = isset($_GET['laundry']);
$kitchenFilter = isset($_GET['kitchen']);
$bipsuFilter = isset($_GET['bipsu']);

// helper to detect column existence to avoid SQL errors on schemas without feature columns
function column_exists($conn, $table, $column) {
	$col = $conn->real_escape_string($column);
	$res = $conn->query("SHOW COLUMNS FROM {$table} LIKE '{$col}'");
	return $res && $res->num_rows > 0;
}

$baseSelect = 'SELECT bh.*, img.filename FROM boarding_houses bh LEFT JOIN images img ON img.listing_id = bh.id';
$where = [];
$bind_types = '';
$bind_values = [];

if ($q !== '') {
	$where[] = '(bh.title LIKE ? OR bh.description LIKE ?)';
	$like = '%' . $q . '%';
	$bind_types .= 'ss';
	$bind_values[] = $like;
	$bind_values[] = $like;
}

// add feature filters only if the columns actually exist in the DB
if ($wifiFilter && column_exists($conn, 'boarding_houses', 'wifi')) {
	$where[] = 'bh.wifi = ?';
	$bind_types .= 'i'; $bind_values[] = 1;
}
if ($laundryFilter && column_exists($conn, 'boarding_houses', 'laundry')) {
	$where[] = 'bh.laundry = ?';
	$bind_types .= 'i'; $bind_values[] = 1;
}
if ($kitchenFilter && column_exists($conn, 'boarding_houses', 'kitchen')) {
	$where[] = 'bh.kitchen = ?';
	$bind_types .= 'i'; $bind_values[] = 1;
}
if ($bipsuFilter && column_exists($conn, 'boarding_houses', 'close_to_bipsu')) {
	$where[] = 'bh.close_to_bipsu = ?';
	$bind_types .= 'i'; $bind_values[] = 1;
}

$sql = $baseSelect;
$countSql = 'SELECT COUNT(DISTINCT bh.id) AS total FROM boarding_houses bh';
if (!empty($where)) {
	$clause = ' WHERE ' . implode(' AND ', $where);
	$sql .= $clause . ' GROUP BY bh.id';
	$countSql .= $clause;
} else {
	$sql .= ' GROUP BY bh.id';
}

// ordering
if ($sort === 'oldest') {
	$sql .= ' ORDER BY bh.created_at ASC';
} else {
	$sql .= ' ORDER BY bh.created_at DESC';
}

// count total
$stmtc = $conn->prepare($countSql);
if ($stmtc === false) {
	// fallback: no rows
	$total = 0;
} else {
	if ($bind_types !== '') {
		$refs = [];
		foreach ($bind_values as $k => $v) $refs[$k] = &$bind_values[$k];
		array_unshift($refs, $bind_types);
		call_user_func_array([$stmtc, 'bind_param'], $refs);
	}
	$stmtc->execute();
	$tr = $stmtc->get_result()->fetch_assoc();
	$total = $tr['total'] ?? 0;
}

// prepare main statement with limit
$stmt = $conn->prepare($sql . ' LIMIT ?,?');
if ($stmt === false) {
	die('Query prepare failed: ' . $conn->error);
}

// add limit params
$bind_types_with_limits = $bind_types . 'ii';
$bind_values_with_limits = $bind_values;
$bind_values_with_limits[] = $offset;
$bind_values_with_limits[] = $perPage;

// bind dynamically
if ($bind_types_with_limits !== '') {
	$refs = [];
	foreach ($bind_values_with_limits as $k => $v) $refs[$k] = &$bind_values_with_limits[$k];
	array_unshift($refs, $bind_types_with_limits);
	call_user_func_array([$stmt, 'bind_param'], $refs);
}

$stmt->execute();
$res = $stmt->get_result();
$pages = max(1, ceil($total / $perPage));

?>

<style>
.listing-thumb {
	width: 100%;
	height: 250px;
	object-fit: cover;
	object-position: center;
}

.card {
	height: 100%;
	display: flex;
	flex-direction: column;
}

.card-body {
	flex: 1;
	display: flex;
	flex-direction: column;
}

.card-body > :last-child {
	margin-top: auto;
}
</style>

<h2>Search Listings</h2>
<form class="mb-4" method="get">
	<div class="row g-2 align-items-center">
		<div class="col-md-6">
			<div class="input-group">
				<input name="q" value="<?php echo esc_attr($q); ?>" class="form-control" placeholder="Search by title or description">
				<button class="btn btn-primary">Search</button>
			</div>
		</div>
		<div class="col-md-3">
			<select name="sort" class="form-select">
				<option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
				<option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest</option>
			</select>
		</div>
		<div class="col-md-3">
			<div class="d-flex gap-2 flex-wrap">
				<label class="form-check mb-0">
					<input class="form-check-input" type="checkbox" name="wifi" <?php echo $wifiFilter? 'checked':''; ?>>
					<span class="form-check-label">WiFi</span>
				</label>
				<label class="form-check mb-0">
					<input class="form-check-input" type="checkbox" name="laundry" <?php echo $laundryFilter? 'checked':''; ?>>
					<span class="form-check-label">Laundry</span>
				</label>
				<label class="form-check mb-0">
					<input class="form-check-input" type="checkbox" name="kitchen" <?php echo $kitchenFilter? 'checked':''; ?>>
					<span class="form-check-label">Kitchen</span>
				</label>
				<label class="form-check mb-0">
					<input class="form-check-input" type="checkbox" name="bipsu" <?php echo $bipsuFilter? 'checked':''; ?>>
					<span class="form-check-label">Close to BIPSU</span>
				</label>
			</div>
		</div>
	</div>
</form>

 <?php if ($res->num_rows === 0): ?>
	<p>No results found.</p>
<?php else: ?>
	<div class="row">
	<?php while ($row = $res->fetch_assoc()): ?>
		<div class="col-md-4">
			<div class="card mb-3">
					<?php
					// Determine which image to use
					if (!empty($row['image'])) {
    				// If a full image path is stored on the boarding_houses table
    					$localImg = $row['image'];
					} elseif (!empty($row['filename'])) {
    				// If filename is stored in the images table
    					$localImg = '/board-in/uploads/listings/' . $row['filename'];
					} else {
    				// Fallback to Unsplash
    					$localImg = unsplash_url('boarding-house', 600, 400);
				}
				?>
				<img src="<?php echo htmlspecialchars($localImg); ?>" class="card-img-top listing-thumb" alt="Boarding House Image">

				<div class="card-body">
					<h5 class="card-title"><?php echo esc_attr($row['title']); ?></h5>
					
					<!-- Location/Address -->
					<?php if (!empty($row['address']) || !empty($row['location'])): ?>
						<p class="text-muted small mb-2">
							<i class="bi bi-geo-alt"></i> 
							<?php echo htmlspecialchars($row['address'] ?? $row['location']); ?>
						</p>
					<?php endif; ?>
					
					<!-- Description -->
					<p class="card-text"><?php echo htmlspecialchars(strlen($row['description'])>120?substr($row['description'],0,120).'...':$row['description']); ?></p>
					
					<!-- Room Type & Gender -->
					<div class="mb-2 small">
						<?php if (!empty($row['room_type'])): ?>
							<span class="badge bg-secondary me-1">
								<i class="bi bi-door-open"></i> <?php echo ucfirst($row['room_type']); ?>
							</span>
						<?php endif; ?>
						<?php if (!empty($row['gender_allowed'])): ?>
							<span class="badge bg-info">
								<?php 
								$gender_icon = $row['gender_allowed'] === 'male' ? 'bi-gender-male' : 
											   ($row['gender_allowed'] === 'female' ? 'bi-gender-female' : 'bi-gender-ambiguous');
								?>
								<i class="bi <?php echo $gender_icon; ?>"></i> <?php echo ucfirst($row['gender_allowed']); ?>
							</span>
						<?php endif; ?>
					</div>
					
					<!-- Amenities -->
					<div class="mb-2 small text-muted d-flex gap-2 flex-wrap align-items-center">
						<?php if (isset($row['wifi']) && $row['wifi']): ?>
							<span title="WiFi" class="badge bg-light text-dark border"><i class="bi bi-wifi"></i> WiFi</span>
						<?php endif; ?>
						<?php if (isset($row['laundry']) && $row['laundry']): ?>
							<span title="Laundry" class="badge bg-light text-dark border"><i class="bi bi-droplet"></i> Laundry</span>
						<?php endif; ?>
						<?php if (isset($row['kitchen']) && $row['kitchen']): ?>
							<span title="Kitchen" class="badge bg-light text-dark border"><i class="bi bi-egg-fried"></i> Kitchen</span>
						<?php endif; ?>
						<?php if (isset($row['parking']) && $row['parking']): ?>
							<span title="Parking" class="badge bg-light text-dark border"><i class="bi bi-car-front"></i> Parking</span>
						<?php endif; ?>
						<?php if (isset($row['close_to_bipsu']) && $row['close_to_bipsu']): ?>
							<span title="Close to BIPSU" class="badge bg-primary"><i class="bi bi-mortarboard"></i> Near BIPSU</span>
						<?php endif; ?>
					</div>
					
					<!-- Availability -->
					<?php if (isset($row['available_rooms']) && isset($row['total_rooms'])): ?>
						<p class="small text-muted mb-2">
							<i class="bi bi-door-closed"></i> 
							<?php echo $row['available_rooms']; ?> / <?php echo $row['total_rooms']; ?> rooms available
						</p>
					<?php endif; ?>
					
					<!-- Price and View Button -->
					<div class="d-flex justify-content-between align-items-center mt-3">
						<h4 class="text-primary mb-0">
							₱<?php echo number_format($row['price'] ?? $row['monthly_rent'], 2); ?><small class="text-muted fw-normal">/month</small>
						</h4>
						<a href="/board-in/pages/listing.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">View Details</a>
					</div>
				</div>
			</div>
		</div>
	<?php endwhile; ?>
	</div>
	<?php endif; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>