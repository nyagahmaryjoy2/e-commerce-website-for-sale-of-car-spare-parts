<?php

include '../components/connect.php';
require_once '../components/admin_access.php';

if(session_status() === PHP_SESSION_NONE){ session_start(); }

$admin_id = (int)($_SESSION['admin_id'] ?? 0);

if($admin_id === 0){
   header('location:admin_login.php');
   exit;
}

ensure_admin_access_schema($conn);
$admin_access = get_admin_access($conn, $admin_id);
$can_view_user_data = admin_can_view_user_data($admin_access);

if(isset($_POST['update_payment'])){
   $order_id = $_POST['order_id'];
   $payment_status = trim($_POST['payment_status']);
   $payment_status = htmlspecialchars($payment_status, ENT_QUOTES, 'UTF-8');
   $update_payment = $conn->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?");
   $update_payment->execute([$payment_status, $order_id]);
   $message[] = 'payment status updated!';
}

if(isset($_GET['delete'])){
   if(empty($admin_access['is_super_admin'])){
      http_response_code(403);
      exit('Super admin access required to delete orders.');
   }
   $delete_id = $_GET['delete'];
   $delete_order = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
   $delete_order->execute([$delete_id]);
   header('location:placed_orders.php');
   exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Placed Orders.</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="orders">

<h1 class="heading">Placed Orders.</h1>

<div class="box-container">

   <?php
      $select_orders = $conn->prepare("SELECT * FROM `orders`");
      $select_orders->execute();
      if($select_orders->rowCount() > 0){
         while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){
   ?>
   <div class="box">
      <p> Placed On : <span><?= $fetch_orders['placed_on']; ?></span> </p>
      <p> Name : <span><?= htmlspecialchars($can_view_user_data ? $fetch_orders['name'] : mask_customer_name($fetch_orders['name']), ENT_QUOTES, 'UTF-8'); ?></span> </p>
      <p> Number : <span><?= htmlspecialchars($can_view_user_data ? $fetch_orders['number'] : mask_customer_phone($fetch_orders['number']), ENT_QUOTES, 'UTF-8'); ?></span> </p>
      <p> Address : <span><?= $can_view_user_data ? htmlspecialchars($fetch_orders['address'], ENT_QUOTES, 'UTF-8') : 'Hidden - User Data permission required'; ?></span> </p>
      <p> Total products : <span><?= $can_view_user_data ? htmlspecialchars($fetch_orders['total_products'], ENT_QUOTES, 'UTF-8') : 'Hidden'; ?></span> </p>
      <p> Total items : <span><?= $can_view_user_data ? (int)$fetch_orders['total_items'] : 'Hidden'; ?></span> </p>
      <p> Total price : <span><?= $can_view_user_data ? 'KSH.' . (int)$fetch_orders['total_price'] . '/-' : 'Hidden'; ?></span> </p>
      <p> Payment method : <span><?= $can_view_user_data ? htmlspecialchars($fetch_orders['method'], ENT_QUOTES, 'UTF-8') : 'Hidden'; ?></span> </p>
      <form action="" method="post">
         <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
         <select name="payment_status" class="select">
            <option selected disabled><?= $fetch_orders['payment_status']; ?></option>
            <option value="pending">Pending</option>
            <option value="completed">Completed</option>
         </select>
        <div class="flex-btn">
         <input type="submit" value="update" class="option-btn" name="update_payment">
            <?php if(!empty($admin_access['is_super_admin'])){ ?>
               <a href="placed_orders.php?delete=<?= (int)$fetch_orders['id']; ?>" class="delete-btn" onclick="return confirm('delete this order?');">delete</a>
            <?php } ?>
        </div>
      </form>
   </div>
   <?php
         }
      }else{
         echo '<p class="empty">no orders placed yet!</p>';
      }
   ?>

</div>

</section>

</section>












<script src="../js/admin_script.js"></script>
   
</body>
</html>