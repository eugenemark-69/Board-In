<?php
// Simple search proxy: accept POST/GET from forms, sanitize and forward to pages/search.php with query string
// Supports ajax=1 to return JSON with the target URL instead of redirecting.

// Collect input from both GET and POST (POST is common for forms)
$input = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
require_once __DIR__ . '/../includes/functions.php';

// If this is an AJAX POST request, validate CSRF token
if ((isset($input['ajax']) && $input['ajax'] === '1') && $_SERVER['REQUEST_METHOD'] === 'POST') {
	$token = $input['csrf_token'] ?? '';
	if (!csrf_check($token)) {
		header('Content-Type: application/json', true, 403);
		echo json_encode(['ok' => false, 'error' => 'Invalid CSRF token']);
		exit;
	}
}

// Whitelist of allowed parameters and simple sanitizers
$allowed_strings = ['q', 'sort', 'view', 'distance', 'curfew'];
$allowed_ints = ['page', 'available_min'];
$allowed_floats = ['min_price', 'max_price'];
$allowed_enums = [
	'sort' => ['newest','oldest','price_low','price_high','rating'],
	'view' => ['grid','list'],
	'gender' => ['male','female','both'],
	'distance' => ['walkable','1','2','5',''],
	'curfew' => ['with','without','any','']
];

$amenity_filters = ['wifi','own_cr','shared_kitchen','laundry_area','parking','study_area','air_conditioning','water_heater','bipsu'];

$params = [];

// String params
if (!empty($input['q'])) {
	$params['q'] = trim((string)$input['q']);
}

// Numeric params
if (isset($input['min_price']) && $input['min_price'] !== '') {
	$params['min_price'] = (float)$input['min_price'];
}
if (isset($input['max_price']) && $input['max_price'] !== '') {
	$params['max_price'] = (float)$input['max_price'];
}
if (isset($input['available_min']) && $input['available_min'] !== '') {
	$params['available_min'] = (int)$input['available_min'];
}
if (isset($input['page']) && is_numeric($input['page'])) {
	$params['page'] = max(1, (int)$input['page']);
}

// Gender
if (isset($input['gender']) && in_array($input['gender'], $allowed_enums['gender'], true)) {
	$params['gender'] = $input['gender'];
}

// Sort and view
if (isset($input['sort']) && in_array($input['sort'], $allowed_enums['sort'], true)) {
	$params['sort'] = $input['sort'];
}
if (isset($input['view']) && in_array($input['view'], $allowed_enums['view'], true)) {
	$params['view'] = $input['view'];
}

// Distance and curfew
if (isset($input['distance']) && in_array((string)$input['distance'], $allowed_enums['distance'], true)) {
	if ((string)$input['distance'] !== '') $params['distance'] = (string)$input['distance'];
}
if (isset($input['curfew']) && in_array((string)$input['curfew'], $allowed_enums['curfew'], true)) {
	if ((string)$input['curfew'] !== 'any') $params['curfew'] = (string)$input['curfew'];
}

// Map toggle
if (isset($input['map']) && ($input['map'] === '1' || $input['map'] === 1)) {
	$params['map'] = 1;
}

// Amenity checkboxes
foreach ($amenity_filters as $af) {
	if (isset($input[$af]) && ($input[$af] === '1' || $input[$af] === 'on' || $input[$af] === 'true' || $input[$af] === 1)) {
		$params[$af] = 1;
	}
}

// Gender allowed could be provided via inputs
if (isset($input['gender']) && in_array($input['gender'], ['male','female','both'], true)) {
	$params['gender'] = $input['gender'];
}

// Build query string
$qs = http_build_query($params);
$target = '/board-in/pages/search.php' . ($qs !== '' ? ('?' . $qs) : '');

// If ajax, return JSON
if ((isset($input['ajax']) && $input['ajax'] === '1') || (isset($_GET['ajax']) && $_GET['ajax'] === '1')) {
	header('Content-Type: application/json');
	echo json_encode(['ok' => true, 'url' => $target, 'params' => $params]);
	exit;
}

// Redirect to search page with parameters
header('Location: ' . $target);
exit;
