<?php
$consumerKey = '1n1TXMzll0HUSEEV1ItqgZvAQph1DGJMIyrSHrxSVI3Njml6';
$consumerSecret = 'F1qt3ip2O8lQU5c7Sh4IvJrJoihoxkPd31WDnnpzl4JG8UKHfPUIqZQIRW0iuQ0z';

$credentials = base64_encode($consumerKey . ':' . $consumerSecret);

$url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($curl);
curl_close($curl);

$result = json_decode($response);
$access_token = $result->access_token;
echo $access_token;
?>
