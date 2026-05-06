<?php
require_once 'config.php';
requireRole('buyer');

/* ============================================================
   PAYMENT SIMULATION
   Generates a transaction ID and provider-specific confirmation code.
   No real money is moved - this simulates payment gateway behaviour.
   ============================================================ */

function generateTransactionId() {
    $date = date('Ymd');
    $rand = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    return "TXN-LVS-{$date}-{$rand}";
}

function generateConfirmationCode($method) {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $rand = function($n) use ($chars) {
        $s = '';
        for ($i = 0; $i < $n; $i++) {
            $s .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $s;
    };
    switch ($method) {
        case 'mpesa':       return $rand(10);                                         // QGH7K2MNX9
        case 'tigopesa':    return 'TG' . $rand(8);                                   // TGAB12CD34
        case 'airtelmoney': return 'AM' . $rand(8);                                   // AMXY99ZP01
        case 'bank':        return 'TZB' . substr((string)(time() * 1000), -9);       // TZB123456789
        case 'card':        return 'AUTH-' . $rand(6);                                // AUTH-XY98ZK
        default:            return $rand(10);
    }
}

$input = getInput();
$landId    = intval($input['land_id'] ?? 0);
$method    = $input['method']    ?? '';
$reference = trim($input['reference'] ?? '');

if (!$landId || !$method || !$reference) {
    jsonResponse(['success' => false, 'message' => 'Land, payment method and reference are required.']);
}
if (!in_array($method, ['mpesa','tigopesa','airtelmoney','bank','card'])) {
    jsonResponse(['success' => false, 'message' => 'Invalid payment method.']);
}

// Fetch land
$stmt = $conn->prepare("SELECT * FROM lands WHERE id = ?");
$stmt->bind_param("i", $landId);
$stmt->execute();
$land = $stmt->get_result()->fetch_assoc();
if (!$land)                          jsonResponse(['success' => false, 'message' => 'Land not found.']);
if ($land['status'] !== 'valued')    jsonResponse(['success' => false, 'message' => 'Land is not available for purchase.']);
if (!$land['valuation_amount'])      jsonResponse(['success' => false, 'message' => 'Land has no valuation.']);

$buyerId = $_SESSION['user_id'];
$sellerId = $land['seller_id'];
$amount = $land['valuation_amount'];
$txnId = generateTransactionId();
$confirmationCode = generateConfirmationCode($method);

// Simulate brief processing delay
usleep(500000); // 0.5 seconds

// Begin transaction so payment + land update happen together
$conn->begin_transaction();
try {
    $stmt = $conn->prepare("INSERT INTO payments
        (land_id, buyer_id, seller_id, amount, method, reference, transaction_id, confirmation_code, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'successful')");
    $stmt->bind_param("iiidssss",
        $landId, $buyerId, $sellerId, $amount, $method, $reference, $txnId, $confirmationCode);
    $stmt->execute();
    $paymentId = $conn->insert_id;

    $stmt2 = $conn->prepare("UPDATE lands SET status = 'sold', buyer_id = ?, payment_status = 'paid' WHERE id = ?");
    $stmt2->bind_param("ii", $buyerId, $landId);
    $stmt2->execute();

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    jsonResponse(['success' => false, 'message' => 'Payment failed: ' . $e->getMessage()]);
}

jsonResponse([
    'success' => true,
    'payment' => [
        'id'                => $paymentId,
        'transaction_id'    => $txnId,
        'confirmation_code' => $confirmationCode,
        'amount'            => $amount,
        'method'            => $method,
        'reference'         => $reference,
        'date_time'         => date('Y-m-d H:i:s')
    ]
]);
?>
