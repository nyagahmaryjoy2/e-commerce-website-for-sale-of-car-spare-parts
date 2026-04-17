<?php

include 'components/connect.php';

$callbackData = file_get_contents('php://input');
$logFile = "mpesa_callback.log";
$log = fopen($logFile, "a");
fwrite($log, $callbackData);
fclose($log);

$data = json_decode($callbackData);

if (isset($data->Body->stkCallback)) {
    $callback = $data->Body->stkCallback;
    $resultCode = $callback->ResultCode;
    $resultDesc = $callback->ResultDesc;
    $checkoutRequestId = $callback->CheckoutRequestID;

    if ($resultCode == 0) {
        // Payment successful
        $amount = $callback->CallbackMetadata->Item[0]->Value;
        $mpesaReceiptNumber = $callback->CallbackMetadata->Item[1]->Value;
        $transactionDate = $callback->CallbackMetadata->Item[3]->Value;
        $phoneNumber = $callback->CallbackMetadata->Item[4]->Value;

        // Update order status
        $update_order = $conn->prepare("UPDATE `orders` SET payment_status = 'paid', mpesa_receipt = ? WHERE checkout_request_id = ?");
        $update_order->execute([$mpesaReceiptNumber, $checkoutRequestId]);

        echo "Payment successful";
    } else {
        // Payment failed
        $update_order = $conn->prepare("UPDATE `orders` SET payment_status = 'failed' WHERE checkout_request_id = ?");
        $update_order->execute([$checkoutRequestId]);
        echo "Payment failed: " . $resultDesc;
    }
}

?>