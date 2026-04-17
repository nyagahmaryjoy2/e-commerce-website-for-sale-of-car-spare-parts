<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:user_login.php');
};

$select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
$select_profile->execute([$user_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

if(isset($_POST['order'])){

   $first_name = $_POST['first_name'];
   $first_name = filter_var($first_name, FILTER_SANITIZE_STRING);
   $last_name = $_POST['last_name'];
   $last_name = filter_var($last_name, FILTER_SANITIZE_STRING);
   $name = $first_name . ' ' . $last_name;
   $number = trim($_POST['number']);
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $email = $fetch_profile['email']; // from profile
   $method = $_POST['method'] ?? 'cash';
   $method = in_array($method, ['mpesa','cash']) ? $method : 'cash';
   $address = $fetch_profile['address'];
   $total_products = $_POST['total_products'];
   $total_price = $_POST['total_price'];

   $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
   $check_cart->execute([$user_id]);

   if($check_cart->rowCount() > 0){

      if(empty($address)){
         $message[] = 'Please add your delivery address in your profile before placing the order.';
      } elseif($method === 'mpesa' && empty($fetch_profile['phone'])){
         $message[] = 'Please add your phone number in your profile before paying with M-Pesa.';
      } elseif(empty($message)) {
         if($method === 'mpesa'){
            $number = $fetch_profile['phone'];
            include 'mpesa.php';
            $payment_result = initiate_mpesa_payment($number, $total_price);
            if($payment_result['success']){
               $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, total_items, payment_status) VALUES(?,?,?,?,?,?,?,?,?,?)");
               $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $total_price, $_POST['total_items'], 'pending']);
               $order_id = $conn->lastInsertId();
               $conn->prepare("UPDATE `orders` SET checkout_request_id = ? WHERE id = ?")->execute([$payment_result['checkoutRequestId'], $order_id]);
               $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
               $delete_cart->execute([$user_id]);
               $message[] = 'M-Pesa payment initiated. Please complete payment on your phone. Order will be confirmed upon successful payment.';
            } else {
               $message[] = 'Failed to initiate M-Pesa payment: ' . $payment_result['error'];
            }
         } else {
            $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, total_items, payment_status) VALUES(?,?,?,?,?,?,?,?,?,?)");
            $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $total_price, $_POST['total_items'], 'pending']);
            $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
            $delete_cart->execute([$user_id]);
            $message[] = 'Cash order placed successfully. Please prepare payment on delivery.';
         }
      }
   }else{
      $message[] = 'your cart is empty';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>checkout</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="checkout-orders">

   <form action="" method="POST">

   <h3>Your Orders</h3>

      <div class="display-orders">
      <?php
         $grand_total = 0;
         $total_items = 0;
         $cart_items[] = '';
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);
         if($select_cart->rowCount() > 0){
            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
               $cart_items[] = $fetch_cart['name'].' ('.$fetch_cart['price'].' x '. $fetch_cart['quantity'].') - ';
               $total_products = implode($cart_items);
               $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
               $total_items += $fetch_cart['quantity'];
      ?>
               <p> <?= $fetch_cart['name']; ?> <span>(<?= 'KSH '.$fetch_cart['price'].'/- x '. $fetch_cart['quantity']; ?>)</span> </p>
      <?php
            }
         }else{
            echo '<p class="empty">your cart is empty!</p>';
         }
      ?>
         <input type="hidden" name="total_products" value="<?= $total_products; ?>">
         <input type="hidden" name="total_price" value="<?= $grand_total; ?>" value="">
         <input type="hidden" name="total_items" value="<?= $total_items; ?>">
         <div class="grand-total">Grand Total : <span>KSH <?= $grand_total; ?>/-</span></div>
      </div>

      <h3>place your orders</h3>

      <div class="flex">
         <div class="inputBox">
            <span>First Name :</span>
            <input type="text" name="first_name" placeholder="enter your first name" class="box" maxlength="20" value="<?= $fetch_profile['first_name']; ?>" required>
         </div>
         <div class="inputBox">
            <span>Last Name :</span>
            <input type="text" name="last_name" placeholder="enter your last name" class="box" maxlength="20" value="<?= $fetch_profile['last_name']; ?>" required>
         </div>
         <div class="inputBox">
            <span>Your Number :</span>
            <input type="text" name="number" id="paymentNumber" placeholder="enter your number" class="box" value="<?= $fetch_profile['phone']; ?>" required>
         </div>
      </div>

      <div class="flex">
         <div class="inputBox">
            <span>Payment method :</span>
            <select name="method" id="paymentMethod" class="box">
               <option value="mpesa">M-Pesa</option>
               <option value="cash">Cash</option>
            </select>
         </div>
         <div class="inputBox">
            <span>Delivery address :</span>
            <input type="text" class="box" value="<?= $fetch_profile['address'] ?: 'Please update your profile address'; ?>" readonly>
         </div>
      </div>

      <input type="submit" name="order" class="btn <?= ($grand_total > 1)?'':'disabled'; ?>" value="place order">

   </form>

</section>













<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>
<script>
const paymentMethod = document.getElementById('paymentMethod');
const paymentNumber = document.getElementById('paymentNumber');
const profilePhone = '<?= addslashes($fetch_profile['phone']); ?>';

function updatePaymentNumber() {
   if(paymentMethod.value === 'mpesa'){
      paymentNumber.value = profilePhone;
      paymentNumber.readOnly = true;
      paymentNumber.placeholder = profilePhone ? '' : 'Please add your phone in profile';
      paymentNumber.style.backgroundColor = '#f0f0f0';
   } else {
      paymentNumber.readOnly = false;
      paymentNumber.placeholder = 'enter your number';
      paymentNumber.style.backgroundColor = '';
   }
}

paymentMethod.addEventListener('change', updatePaymentNumber);
updatePaymentNumber();
</script>
</body>
</html>