<?php
require_once 'config.php';
requireRole('authority');

/* ============================================================
   VALUATION FORMULA
   Value = ZoneRate × Area
         × TypeMultiplier
         × RoadAccessMultiplier
         × ContextMultiplier (distance OR soil×water)
         + UtilityBonus (5% each)
   ============================================================ */

const ZONE_RATE  = ['urban' => 50000, 'suburban' => 25000, 'rural' => 10000];
const TYPE_MULT  = ['commercial' => 1.5, 'residential' => 1.2, 'agricultural' => 0.8];
const ROAD_MULT  = ['main' => 1.4, 'side' => 1.0, 'interior' => 0.7];
const DIST_MULT  = ['near' => 1.2, 'moderate' => 1.0, 'far' => 0.85];
const SOIL_MULT  = ['fertile' => 1.3, 'moderate' => 1.0, 'poor' => 0.7];
const WATER_MULT = ['river' => 1.2, 'well' => 1.1, 'none' => 1.0];

function computeValuation($land) {
    $zoneRate = ZONE_RATE[$land['zone']];
    $typeMult = TYPE_MULT[$land['type']];
    $roadMult = ROAD_MULT[$land['road_access']];

    $base      = $zoneRate * $land['area'];
    $afterType = $base * $typeMult;
    $afterRoad = $afterType * $roadMult;

    $afterLoc = $afterRoad;
    $factors = [];

    if ($land['type'] === 'agricultural') {
        $soil  = SOIL_MULT[$land['soil_quality']]  ?? 1.0;
        $water = WATER_MULT[$land['water_source']] ?? 1.0;
        $afterLoc = $afterRoad * $soil * $water;
        $factors[] = ['label' => "Soil quality ({$land['soil_quality']})", 'mult' => $soil];
        $factors[] = ['label' => "Water source ({$land['water_source']})", 'mult' => $water];
    } else {
        $dist = DIST_MULT[$land['distance_to_center']] ?? 1.0;
        $afterLoc = $afterRoad * $dist;
        $factors[] = ['label' => "Distance to center ({$land['distance_to_center']})", 'mult' => $dist];
    }

    $utilCount = ($land['has_electricity'] + $land['has_water']
                + $land['has_road']        + $land['has_sewage']);
    $utilBonus = $utilCount * 0.05;
    $utilAdd   = $afterLoc * $utilBonus;
    $total     = $afterLoc + $utilAdd;

    return [
        'zoneRate'         => $zoneRate,
        'area'             => $land['area'],
        'base'             => $base,
        'typeMultiplier'   => $typeMult,
        'afterType'        => $afterType,
        'roadMultiplier'   => $roadMult,
        'afterRoad'        => $afterRoad,
        'locationFactors'  => $factors,
        'afterLoc'         => $afterLoc,
        'utilityCount'     => $utilCount,
        'utilityBonus'     => $utilBonus,
        'utilityAmount'    => $utilAdd,
        'total'            => round($total, 2)
    ];
}

$input = getInput();
$action = $input['action'] ?? 'preview';   // 'preview' or 'confirm'
$landId = intval($input['land_id'] ?? 0);

if (!$landId) {
    jsonResponse(['success' => false, 'message' => 'Land ID is required.']);
}

// Fetch land
$stmt = $conn->prepare("SELECT * FROM lands WHERE id = ?");
$stmt->bind_param("i", $landId);
$stmt->execute();
$land = $stmt->get_result()->fetch_assoc();
if (!$land) {
    jsonResponse(['success' => false, 'message' => 'Land not found.']);
}

$valuation = computeValuation($land);

if ($action === 'preview') {
    jsonResponse(['success' => true, 'valuation' => $valuation]);
}

if ($action === 'confirm') {
    $valuedBy = $_SESSION['name'];
    $today    = date('Y-m-d');
    $breakdownJson = json_encode($valuation);
    $totalAmount = $valuation['total'];

    $stmt = $conn->prepare("UPDATE lands SET valuation_amount = ?, valuation_breakdown = ?,
                            valued_by = ?, valued_on = ?, status = 'valued' WHERE id = ?");
    $stmt->bind_param("dsssi", $totalAmount, $breakdownJson, $valuedBy, $today, $landId);

    if ($stmt->execute()) {
        jsonResponse(['success' => true, 'valuation' => $valuation]);
    } else {
        jsonResponse(['success' => false, 'message' => 'Failed to save valuation: ' . $stmt->error]);
    }
}

jsonResponse(['success' => false, 'message' => 'Invalid action.']);
?>
