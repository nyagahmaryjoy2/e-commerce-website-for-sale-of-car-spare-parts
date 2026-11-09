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
if(empty($admin_access['is_super_admin'])){
   http_response_code(403);
   exit('Super admin access required.');
}

if(isset($_POST['delete_admin'])){
   if(!verify_admin_csrf_token($_POST['csrf_token'] ?? null)){
      $message[] = 'Your session expired. Reload the page and try again.';
   }else{
      $delete_id = filter_var($_POST['delete_admin_id'] ?? null, FILTER_VALIDATE_INT);
      if(!$delete_id || (int)$delete_id === $admin_id){
         $message[] = 'You cannot delete this admin account.';
      }else{
         $delete_admin = $conn->prepare("DELETE FROM `admins` WHERE `id` = ? AND `is_super_admin` = 0");
         $delete_admin->execute([(int)$delete_id]);
         $message[] = $delete_admin->rowCount() ? 'Admin account deleted.' : 'The super admin account cannot be deleted.';
      }
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Accounts</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="accounts">

   <h1 class="heading">Admin Accounts</h1>

   <div class="box-container">

   <div class="box">
      <p>Add New Admin</p>
      <a href="register_admin.php" class="option-btn">Register Admin</a>
      <a href="admin_permissions.php" class="btn">Manage Admin Rights</a>
   </div>

   <?php
      $select_accounts = $conn->prepare("SELECT * FROM `admins`");
      $select_accounts->execute();
      if($select_accounts->rowCount() > 0){
         while($fetch_accounts = $select_accounts->fetch(PDO::FETCH_ASSOC)){   
   ?>
   <div class="box">
      <p> Admin Id : <span><?= $fetch_accounts['id']; ?></span> </p>
      <p> Admin name : <span><?= htmlspecialchars($fetch_accounts['name'], ENT_QUOTES, 'UTF-8'); ?></span> </p>
      <p> Role : <span><?= !empty($fetch_accounts['is_super_admin']) ? 'Super admin' : 'Standard admin'; ?></span> </p>
      <div class="flex-btn">
         <?php if(empty($fetch_accounts['is_super_admin'])){ ?>
            <form method="post" action="" onsubmit="return confirm('Delete this admin account?');">
               <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
               <input type="hidden" name="delete_admin_id" value="<?= (int)$fetch_accounts['id']; ?>">
               <button type="submit" name="delete_admin" class="delete-btn">Delete</button>
            </form>
         <?php } ?>
         <?php
            if($fetch_accounts['id'] == $admin_id){
               echo '<a href="update_profile.php" class="option-btn">update</a>';
            }
         ?>
      </div>
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