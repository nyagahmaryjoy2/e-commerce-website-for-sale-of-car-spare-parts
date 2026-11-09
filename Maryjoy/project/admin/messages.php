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
      exit('Super admin access required to delete messages.');
   }
   $delete_id = $_GET['delete'];
   $delete_message = $conn->prepare("DELETE FROM `messages` WHERE id = ?");
   $delete_message->execute([$delete_id]);
   header('location:messages.php');
   exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Messages</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="contacts">

<h1 class="heading">Messages</h1>

<div class="box-container">

   <?php
      $select_messages = $conn->prepare("SELECT * FROM `messages`");
      $select_messages->execute();
      if($select_messages->rowCount() > 0){
         while($fetch_message = $select_messages->fetch(PDO::FETCH_ASSOC)){
   ?>
   <div class="box">
   <p> User id : <span><?= $fetch_message['user_id']; ?></span></p>
   <p> Name : <span><?= htmlspecialchars($can_view_user_data ? $fetch_message['name'] : mask_customer_name($fetch_message['name']), ENT_QUOTES, 'UTF-8'); ?></span></p>
   <p> Email : <span><?= htmlspecialchars($can_view_user_data ? $fetch_message['email'] : mask_customer_email($fetch_message['email']), ENT_QUOTES, 'UTF-8'); ?></span></p>
   <p> Number : <span><?= htmlspecialchars($can_view_user_data ? $fetch_message['number'] : mask_customer_phone($fetch_message['number']), ENT_QUOTES, 'UTF-8'); ?></span></p>
   <p> Message : <span><?= $can_view_user_data ? htmlspecialchars($fetch_message['message'], ENT_QUOTES, 'UTF-8') : 'Hidden - User Data permission required'; ?></span></p>
   <?php if(!empty($admin_access['is_super_admin'])){ ?>
      <a href="messages.php?delete=<?= (int)$fetch_message['id']; ?>" onclick="return confirm('delete this message?');" class="delete-btn">Delete</a>
   <?php } ?>
   </div>
   <?php
         }
      }else{
         echo '<p class="empty">you have no messages</p>';
      }
   ?>

</div>

</section>












<script src="../js/admin_script.js"></script>
   
</body>
</html>