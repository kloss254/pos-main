<?php
/**
 * Callback URL for M-Pesa STK Push
 * Logs request, validates JSON, saves successful transactions only
 */

header("Content-Type: application/json");
date_default_timezone_set('Africa/Nairobi');

// Include your DB connection
include 'dbconnection.php';

// 1️⃣ Capture raw POST from Safaricom
$stkCallbackResponse = file_get_contents('php://input');

// 2️⃣ Log raw payload for audit/troubleshooting
file_put_contents('Mpesastkresponse.json', $stkCallbackResponse . PHP_EOL, FILE_APPEND);

// 3️⃣ Decode JSON safely
$data = json_decode($stkCallbackResponse, true);

if (!isset($data['Body']['stkCallback'])) {
    // Invalid structure received
    file_put_contents('Mpesastkresponse.json', "Invalid Payload Structure" . PHP_EOL, FILE_APPEND);

    http_response_code(400);
    echo json_encode([
        "ResultCode" => 1,
        "ResultDesc" => "Invalid Payload"
    ]);
    exit;
}

// 4️⃣ Extract core details
$stkCallback = $data['Body']['stkCallback'];
$MerchantRequestID = $stkCallback['MerchantRequestID'] ?? '';
$CheckoutRequestID = $stkCallback['CheckoutRequestID'] ?? '';
$ResultCode = $stkCallback['ResultCode'] ?? -1;
$ResultDesc = $stkCallback['ResultDesc'] ?? '';

// 5️⃣ Extract metadata (if present)
$Amount = '';
$MpesaReceiptNumber = '';
$PhoneNumber = '';

if (isset($stkCallback['CallbackMetadata']['Item'])) {
    foreach ($stkCallback['CallbackMetadata']['Item'] as $item) {
        if ($item['Name'] === 'Amount') {
            $Amount = $item['Value'];
        } elseif ($item['Name'] === 'MpesaReceiptNumber') {
            $MpesaReceiptNumber = $item['Value'];
        } elseif ($item['Name'] === 'PhoneNumber') {
            $PhoneNumber = $item['Value'];
        }
    }
}

// 6️⃣ Only store *successful* payments
if ($ResultCode == 0) {
    // Insert into your existing 'transactions' table
    $stmt = $db->prepare("
        INSERT INTO transactions (
            MerchantRequestID,
            CheckoutRequestID,
            ResultCode,
            Amount,
            MpesaReceiptNumber,
            PhoneNumber
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");

    if ($stmt) {
        $stmt->bind_param(
            "ssisss",
            $MerchantRequestID,
            $CheckoutRequestID,
            $ResultCode,
            $Amount,
            $MpesaReceiptNumber,
            $PhoneNumber
        );

        if ($stmt->execute()) {
            file_put_contents('Mpesastkresponse.json', "DB Insert: SUCCESS" . PHP_EOL, FILE_APPEND);
        } else {
            file_put_contents('Mpesastkresponse.json', "DB Insert Error: " . $stmt->error . PHP_EOL, FILE_APPEND);
        }

        $stmt->close();
    } else {
        file_put_contents('Mpesastkresponse.json', "DB Prepare Error: " . $db->error . PHP_EOL, FILE_APPEND);
    }
} else {
    // Log unsuccessful payment attempt
    file_put_contents('Mpesastkresponse.json', "Transaction NOT successful. ResultCode: $ResultCode" . PHP_EOL, FILE_APPEND);
}

// 7️⃣ Always respond to Safaricom to acknowledge receipt
$response = [
    "ResultCode" => 0,
    "ResultDesc" => "Confirmation Received Successfully"
];
echo json_encode($response);
?>
