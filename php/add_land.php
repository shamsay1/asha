<?php
require_once 'config.php';
requireRole('seller');

$input = getInput();

$title       = trim($input['title']        ?? '');
$plotNumber  = trim($input['plotNumber']   ?? '');
$location    = trim($input['location']     ?? '');
$zone        = $input['zone']              ?? '';
$image       = $input['image']              ?? '';
$type        = $input['type']              ?? '';
$area        = floatval($input['area']     ?? 0);
$roadAccess  = $input['roadAccess']        ?? '';
$description = trim($input['description']  ?? '');

$distance = $input['distanceToCenter'] ?? null;
$soil     = $input['soilQuality']      ?? null;
$water    = $input['waterSource']      ?? null;

$utilities = $input['utilities'] ?? [];
$elec   = !empty($utilities['electricity']) ? 1 : 0;
$wat    = !empty($utilities['water'])       ? 1 : 0;
$road   = !empty($utilities['road'])        ? 1 : 0;
$sewage = !empty($utilities['sewage'])      ? 1 : 0;

// Basic validation
if (!$title || !$plotNumber || !$location || !$zone || !$type || !$area || !$roadAccess) {
    jsonResponse(['success' => false, 'message' => 'All required fields must be filled.']);
}
if (!in_array($zone, ['urban','suburban','rural'])) {
    jsonResponse(['success' => false, 'message' => 'Invalid zone.']);
}
if (!in_array($type, ['residential','commercial','agricultural'])) {
    jsonResponse(['success' => false, 'message' => 'Invalid type.']);
}
if (!in_array($roadAccess, ['main','side','interior'])) {
    jsonResponse(['success' => false, 'message' => 'Invalid road access.']);
}

// Type-specific validation
if ($type === 'agricultural') {
    if (!in_array($soil, ['fertile','moderate','poor']) ||
        !in_array($water, ['river','well','none'])) {
        jsonResponse(['success' => false, 'message' => 'Soil quality and water source are required for agricultural land.']);
    }
    $distance = null;
} else {
    if (!in_array($distance, ['near','moderate','far'])) {
        jsonResponse(['success' => false, 'message' => 'Distance to center is required for residential/commercial land.']);
    }
    $soil = null;
    $water = null;
}

$sellerId = $_SESSION['user_id'];

$imagePath = null;

// HANDLE IMAGE UPLOAD
if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $imgName = time() . "_" . basename($_FILES['image']['name']);
    $targetDir = "../uploads/";
    $targetFile = $targetDir . $imgName;

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);
    $imagePath = "uploads/" . $imgName;
}

$stmt = $conn->prepare("INSERT INTO lands
    (seller_id, title, plot_number, location, zone, type, area, road_access,
     distance_to_center, soil_quality, water_source, description,
     has_electricity, has_water, has_road, has_sewage, status,image_path)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending',?)");

$stmt->bind_param("isssssdsssssiiiis",
    $sellerId, $title, $plotNumber, $location, $zone, $type, $area, $roadAccess,
    $distance, $soil, $water, $description,
    $elec, $wat, $road, $sewage,$imagePath);

if ($stmt->execute()) {
    jsonResponse(['success' => true, 'land_id' => $conn->insert_id]);
} else {
    jsonResponse(['success' => false, 'message' => 'Failed to add land: ' . $stmt->error]);
}
?>
