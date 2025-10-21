<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Read params from GET
$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 8;
$offset = ($page - 1) * $perPage;

$sort = in_array($_GET['sort'] ?? 'newest', ['newest','oldest','price_low','price_high','rating']) ? $_GET['sort'] : 'newest';

$minPrice = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? floatval($_GET['min_price']) : null;
$maxPrice = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? floatval($_GET['max_price']) : null;
$gender = in_array($_GET['gender'] ?? '', ['male','female','both']) ? $_GET['gender'] : null;
$availableMin = isset($_GET['available_min']) && $_GET['available_min'] !== '' ? intval($_GET['available_min']) : null;

$amenity_filters = ['wifi','own_cr','shared_kitchen','laundry_area','parking','study_area','air_conditioning','water_heater','bipsu'];

// Distance (same BIPSU coords)
// Geo features removed: distance filtering and calculations no longer performed

// Build SQL parts
$baseSelect = 'SELECT bh.id, bh.title, bh.monthly_rent, bh.available_rooms, COALESCE(p.photo_url, CONCAT("/board-in/uploads/", bh.id, "/", img.filename)) AS photo_url, IFNULL(AVG(r.rating),0) AS avg_rating';
$baseFrom = ' FROM boarding_houses bh LEFT JOIN amenities a ON a.boarding_house_id = bh.id LEFT JOIN photos p ON p.boarding_house_id = bh.id AND p.is_primary = 1 LEFT JOIN images img ON img.listing_id = bh.id LEFT JOIN reviews r ON r.listing_id = bh.id';

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
if (!is_null($minPrice)) { $where[] = 'bh.monthly_rent >= ?'; $bind_types .= 'd'; $bind_values[] = $minPrice; }
if (!is_null($maxPrice)) { $where[] = 'bh.monthly_rent <= ?'; $bind_types .= 'd'; $bind_values[] = $maxPrice; }
if ($gender) { $where[] = 'bh.gender_allowed = ?'; $bind_types .= 's'; $bind_values[] = $gender; }
if (!is_null($availableMin)) { $where[] = 'bh.available_rooms >= ?'; $bind_types .= 'i'; $bind_values[] = $availableMin; }

foreach ($amenity_filters as $af) {
    if (isset($_GET[$af]) && column_exists($conn, 'amenities', $af)) {
        $where[] = "a.{$af} = ?";
        $bind_types .= 'i'; $bind_values[] = 1;
    }
}

$having = [];

$sql = $baseSelect . $baseFrom;
$countSql = 'SELECT COUNT(DISTINCT bh.id) AS total FROM boarding_houses bh LEFT JOIN amenities a ON a.boarding_house_id = bh.id';
if (!empty($where)) { $clause = ' WHERE ' . implode(' AND ', $where); $sql .= $clause; $countSql .= $clause; }
$sql .= ' GROUP BY bh.id';
// distance column removed

if ($sort === 'oldest') { $sql .= ' ORDER BY bh.created_at ASC'; } elseif ($sort === 'price_low') { $sql .= ' ORDER BY bh.monthly_rent ASC'; } elseif ($sort === 'price_high') { $sql .= ' ORDER BY bh.monthly_rent DESC'; } elseif ($sort === 'rating') { $sql .= ' ORDER BY avg_rating DESC'; } else { $sql .= ' ORDER BY bh.created_at DESC'; }

// count total
$stmtc = $conn->prepare($countSql);
if ($stmtc === false) {
    echo json_encode(['ok'=>false,'error'=>'count prepare failed','sql'=>$countSql]); exit;
}
if ($bind_types !== '') {
    $refs = [];
    foreach ($bind_values as $k => $v) $refs[$k] = &$bind_values[$k];
    array_unshift($refs, $bind_types);
    call_user_func_array([$stmtc, 'bind_param'], $refs);
}
$stmtc->execute();
$tr = $stmtc->get_result()->fetch_assoc();
$total = $tr['total'] ?? 0;

$stmt = $conn->prepare($sql . ' LIMIT ?,?');
if ($stmt === false) { echo json_encode(['ok'=>false,'error'=>'prepare failed','sql'=>$sql]); exit; }

$bind_types_with_limits = $bind_types . 'ii';
$bind_values_with_limits = $bind_values;
$bind_values_with_limits[] = $offset;
$bind_values_with_limits[] = $perPage;
if ($bind_types_with_limits !== '') {
    $refs = [];
    foreach ($bind_values_with_limits as $k => $v) $refs[$k] = &$bind_values_with_limits[$k];
    array_unshift($refs, $bind_types_with_limits);
    call_user_func_array([$stmt, 'bind_param'], $refs);
}

$stmt->execute();
$res = $stmt->get_result();

$items = [];
while ($row = $res->fetch_assoc()) {
    $items[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'photo_url' => $row['photo_url'],
        'monthly_rent' => floatval($row['monthly_rent']),
        'available_rooms' => intval($row['available_rooms']),
        'avg_rating' => floatval($row['avg_rating']),
        // distance_km removed
    ];
}

$pages = max(1, ceil($total / $perPage));

echo json_encode(['ok' => true, 'page' => $page, 'perPage' => $perPage, 'total' => $total, 'pages' => $pages, 'items' => $items]);
exit;
