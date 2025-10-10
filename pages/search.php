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
					// image src: try local upload first, fall back to Unsplash when missing
					$localImg = '/board-in/uploads/' . $row['id'] . '/' . ($row['filename'] ?? '');
					$fallback = unsplash_url('boarding-house', 600, 400);
					?>
					<img src="<?php echo $localImg; ?>" onerror="this.onerror=null;this.src='<?php echo $fallback; ?>'" class="card-img-top listing-thumb">
				<div class="card-body">
					<h5 class="card-title"><?php echo esc_attr($row['title']); ?></h5>
					<p class="card-text"><?php echo htmlspecialchars(strlen($row['description'])>200?substr($row['description'],0,200).'...':$row['description']); ?></p>
					<div class="mb-2 small text-muted d-flex gap-2 align-items-center">
						<?php if (isset($row['wifi']) && $row['wifi']): ?>
							<span title="WiFi" class="badge bg-light text-dark border"><i class="bi bi-wifi"></i> WiFi</span>
						<?php endif; ?>
						<?php if (isset($row['laundry']) && $row['laundry']): ?>
							<span title="Laundry" class="badge bg-light text-dark border"><i class="bi bi-cloud-drizzle"></i> Laundry</span>
						<?php endif; ?>
						<?php if (isset($row['kitchen']) && $row['kitchen']): ?>
							<span title="Kitchen" class="badge bg-light text-dark border"><i class="bi bi-suit-heart"></i> Kitchen</span>
						<?php endif; ?>
						<?php if (isset($row['close_to_bipsu']) && $row['close_to_bipsu']): ?>
							<span title="Close to BIPSU" class="badge bg-light text-dark border"><i class="bi bi-geo-alt"></i> BIPSU</span>
						<?php endif; ?>
					</div>
					<a href="/board-in/pages/listing.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">View</a>
				</div>
			</div>
		</div>
	<?php endwhile; ?>
	</div>

	<nav>
		<ul class="pagination">
			<?php for ($i=1;$i<=$pages;$i++): 
				$qp = $_GET;
				$qp['page'] = $i;
				$qs = http_build_query($qp);
			?>
				<li class="page-item <?php echo $i== $page? 'active':''; ?>"><a class="page-link" href="?<?php echo $qs; ?>"><?php echo $i; ?></a></li>
			<?php endfor; ?>
		</ul>
	</nav>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
