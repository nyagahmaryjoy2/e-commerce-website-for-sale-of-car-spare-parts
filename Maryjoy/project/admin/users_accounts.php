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

if(isset($_GET['delete'])){
   if(empty($admin_access['is_super_admin'])){
      http_response_code(403);
      exit('Super admin access required to delete user accounts.');
   }
   $delete_id = filter_var($_GET['delete'], FILTER_VALIDATE_INT);
   if(!$delete_id){
      header('location:users_accounts.php');
      exit;
   }
   $delete_user = $conn->prepare("DELETE FROM `users` WHERE `id` = ?");
   $delete_user->execute([(int)$delete_id]);
   $delete_orders = $conn->prepare("DELETE FROM `orders` WHERE `user_id` = ?");
   $delete_orders->execute([(int)$delete_id]);
   $delete_messages = $conn->prepare("DELETE FROM `messages` WHERE `user_id` = ?");
   $delete_messages->execute([(int)$delete_id]);
   $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE `user_id` = ?");
   $delete_cart->execute([(int)$delete_id]);
   $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE `user_id` = ?");
   $delete_wishlist->execute([(int)$delete_id]);
   $delete_documents = $conn->prepare("DELETE FROM `user_documents` WHERE `user_id` = ?");
   $delete_documents->execute([(int)$delete_id]);
   header('location:users_accounts.php');
   exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Users accounts</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="accounts">

   <h1 class="heading">User accounts</h1>

   <div class="box-container">

   <?php
      $select_accounts = $conn->prepare("SELECT * FROM `users`");
      $select_accounts->execute();
      if($select_accounts->rowCount() > 0){
         while($fetch_accounts = $select_accounts->fetch(PDO::FETCH_ASSOC)){   
   ?>
   <div class="box">
      <p> User id : <span><?= $fetch_accounts['id']; ?></span> </p>
      <p> Username : <span><?= htmlspecialchars($can_view_user_data ? $fetch_accounts['name'] : mask_customer_name($fetch_accounts['name']), ENT_QUOTES, 'UTF-8'); ?></span> </p>
      <p> Email : <span><?= htmlspecialchars($can_view_user_data ? $fetch_accounts['email'] : mask_customer_email($fetch_accounts['email']), ENT_QUOTES, 'UTF-8'); ?></span> </p>
      <?php if(!empty($admin_access['is_super_admin'])){ ?>
         <a href="users_accounts.php?delete=<?= (int)$fetch_accounts['id']; ?>" onclick="return confirm('delete this account? the user related information will also be deleted!')" class="delete-btn">delete</a>
      <?php } ?>
   </div>
   <?php
         }
      }else{
         echo '<p class="empty">no accounts available!</p>';
      }
   ?>

   </div>

</section>












<script src="../js/admin_script.js"></script>
   
</body>
</html>