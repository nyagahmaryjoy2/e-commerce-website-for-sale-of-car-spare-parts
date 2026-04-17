<?php

// M-Pesa Configuration loaded from admin settings
$consumerKey = '';
$consumerSecret = '';
$shortcode = '';
$passkey = '';
$callbackUrl = '';

if(isset($conn)){
    $select_settings = $conn->prepare("SELECT * FROM `mpesa_settings` ORDER BY id DESC LIMIT 1");
    $select_settings->execute();
    if($select_settings->rowCount() > 0){
        $mpesa_settings = $select_settings->fetch(PDO::FETCH_ASSOC);
        $consumerKey = $mpesa_settings['consumer_key'] ?? '';
        $consumerSecret = $mpesa_settings['consumer_secret'] ?? '';
        $shortcode = $mpesa_settings['paybill'] ?? '';
        $passkey = $mpesa_settings['passkey'] ?? '';
        $callbackUrl = $mpesa_settings['callback_url'] ?? '';
    }
}

function getAccessToken() {
    global $consumerKey, $consumerSecret;
    $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    $credentials = base64_encode($consumerKey . ':' . $consumerSecret);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . $credentials));
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($curl);
    $curl_error = curl_error($curl);
    curl_close($curl);

    if ($result === false) {
        return ['error' => 'Token request failed: ' . $curl_error];
    }

    $decoded = json_decode($result, true);
    if (!is_array($decoded) || empty($decoded['access_token'])) {
        $errorMessage = $decoded['errorMessage'] ?? $decoded['error'] ?? 'Invalid token response';
        return ['error' => 'Unable to retrieve access token: ' . $errorMessage];
    }

    return $decoded['access_token'];
}

function initiateSTKPush($phone, $amount) {
    global $shortcode, $passkey, $callbackUrl;
    $accessToken = getAccessToken();
    if (is_array($accessToken) && isset($accessToken['error'])) {
        return ['success' => false, 'error' => $accessToken['error']];
    }
    $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    $timestamp = date('YmdHis');
    $password = base64_encode($shortcode . $passkey . $timestamp);
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $accessToken));
    $curl_post_data = array(
        'BusinessShortCode' => $shortcode,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => $amount,
        'PartyA' => $phone,
        'PartyB' => $shortcode,
        'PhoneNumber' => $phone,
        'CallBackURL' => $callbackUrl,
        'AccountReference' => 'Order Payment',
        'TransactionDesc' => 'Payment for order'
    );
    $data_string = json_encode($curl_post_data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($curl);
    $curl_error = curl_error($curl);
    curl_close($curl);

    if ($result === false) {
        return ['success' => false, 'error' => 'STK Push request failed: ' . $curl_error];
    }

    $decoded = json_decode($result, true);
    if (!is_array($decoded)) {
        return ['success' => false, 'error' => 'Invalid STK Push response'];
    }

    return $decoded;
}

function initiate_mpesa_payment($phone, $amount) {
    // Ensure phone is in 254 format
    if (substr($phone, 0, 1) == '0') {
        $phone = '254' . substr($phone, 1);
    }
    $result = initiateSTKPush($phone, $amount);
    if (isset($result['ResponseCode']) && $result['ResponseCode'] == '0') {
        return ['success' => true, 'checkoutRequestId' => $result['CheckoutRequestID']];
    } else {
        return ['success' => false, 'error' => $result['ResponseDescription'] ?? 'Unknown error'];
    }
}

?>