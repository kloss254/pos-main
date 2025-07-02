<?php
//MPESA API KEYS
$consumerKey = '73Gyes0mN3JeopMXCnAH4u3Mu1tcw0FVs6it1d5lsuTLF4SD';
$consumerSecret='D8fAq3c0Fh8iM8gfdmXbEdNbKc5MtW1zY55rA4CrOmzaNbNuSOygoCXLNRIepKDt';

//ACCESS TOKEN URL
$access_token_url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
$headers = ['Content-Type: application/json; Charset=utf8'];
$curl = curl_init($access_token_url);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($curl, CURLOPT_HEADER, FALSE);
curl_setopt($curl, CURLOPT_USERPWD, $consumerKey . ':' . $consumerSecret);
$result = curl_exec($curl);
$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);


$result= json_decode($result);
echo $access_token = $result->access_token;
curl_close($curl);
?>