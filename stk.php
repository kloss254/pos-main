<?php
// Show PHP errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Africa/Nairobi');
header("Content-Type: application/json");

// ✅ Step 1: Read input
$data = json_decode(file_get_contents("php://input"), true);
if (!$data || empty($data['phone']) || empty($data['amount'])) {
    echo json_encode(["error" => "Phone and amount are required"]);
    exit;
}

// ✅ Step 2: Set credentials
$phone = $data['phone']; // e.g. 254712345678
$amount = $data['amount']; // e.g. 100

$consumerKey = "1n1TXMzll0HUSEEV1ItqgZvAQph1DGJMIyrSHrxSVI3Njml6";
$consumerSecret = "F1qt3ip2O8lQU5c7Sh4IvJrJoihoxkPd31WDnnpzl4JG8UKHfPUIqZQIRW0iuQ0z";
$BusinessShortCode = "174379";
$Passkey = "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919";
$callbackurl = "https://y3hdqnnih6.sharedwithexpose.com/api/mpesa/callback.php";

// ✅ Step 3: Generate Access Token
$credentials = base64_encode($consumerKey . ':' . $consumerSecret);
$ch = curl_init('https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$token_response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(["error" => "Access token curl failed", "details" => curl_error($ch)]);
    exit;
}

$token_data = json_decode($token_response);
curl_close($ch);

if (!isset($token_data->access_token)) {
    echo json_encode(["error" => "Access token failed", "details" => $token_response]);
    exit;
}

$access_token = $token_data->access_token;

// ✅ Step 4: Build STK Push Payload
$Timestamp = date('YmdHis');
$Password = base64_encode($BusinessShortCode . $Passkey . $Timestamp);

$stkHeader = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $access_token
];

$stkPayload = [
    'BusinessShortCode' => $BusinessShortCode,
    'Password' => $Password,
    'Timestamp' => $Timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => $amount,
    'PartyA' => $phone,
    'PartyB' => $BusinessShortCode,
    'PhoneNumber' => $phone,
    'CallBackURL' => $callbackurl,
    'AccountReference' => 'KAYPAY',
    'TransactionDesc' => 'POS Payment'
];

// ✅ Step 5: Send STK Push Request
$ch = curl_init('https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest');
curl_setopt($ch, CURLOPT_HTTPHEADER, $stkHeader);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($stkPayload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(["error" => "STK curl failed", "details" => curl_error($ch)]);
    exit;
}

curl_close($ch);

// ✅ Step 6: Return Safaricom response
echo $response;
?>
