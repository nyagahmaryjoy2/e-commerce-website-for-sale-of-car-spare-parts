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

if(isset($_POST['save_permissions'])){
   if(!verify_admin_csrf_token($_POST['csrf_token'] ?? null)){
      $message[] = 'Your session expired. Reload the page and try again.';
   }else{
      $target_admin_id = filter_var($_POST['target_admin_id'] ?? null, FILTER_VALIDATE_INT);
      $select_target = $conn->prepare("SELECT `id`, `is_super_admin` FROM `admins` WHERE `id` = ? LIMIT 1");
      $select_target->execute([$target_admin_id ?: 0]);
      $target = $select_target->fetch(PDO::FETCH_ASSOC);

      if(!$target || !empty($target['is_super_admin'])){
         $message[] = 'Permissions can only be changed for standard admin accounts.';
      }else{
         $can_view = isset($_POST['can_view_user_data']) ? 1 : 0;
         $can_upload = isset($_POST['can_upload_user_documents']) ? 1 : 0;
         $can_register = isset($_POST['can_register_admins']) ? 1 : 0;
         $update_permissions = $conn->prepare("UPDATE `admins` SET `can_view_user_data` = ?, `can_upload_user_documents` = ?, `can_register_admins` = ? WHERE `id` = ? AND `is_super_admin` = 0");
         $update_permissions->execute([$can_view, $can_upload, $can_register, $target['id']]);
         $message[] = 'Admin permissions updated.';
      }
   }
}

$select_admins = $conn->query("SELECT `id`, `name`, `can_view_user_data`, `can_upload_user_documents`, `can_register_admins`, `is_super_admin` FROM `admins` ORDER BY `id` ASC");
$admins = $select_admins->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Rights</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="accounts">
   <h1 class="heading">Admin Rights</h1>
   <p class="permission-intro">Grant access only when required. User details and uploaded documents are hidden unless the matching permission is enabled.</p>

   <div class="box-container">
      <?php foreach($admins as $account){ ?>
         <div class="box permission-card">
            <h2><?= htmlspecialchars($account['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <p>Admin ID: <span><?= (int)$account['id']; ?></span></p>
            <?php if(!empty($account['is_super_admin'])){ ?>
               <p class="role-label">Super admin</p>
               <p>This account can manage permissions and access all user data.</p>
            <?php }else{ ?>
               <form method="post" action="">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                  <input type="hidden" name="target_admin_id" value="<?= (int)$account['id']; ?>">
                  <label class="permission-option">
                     <input type="checkbox" name="can_view_user_data" value="1" <?= !empty($account['can_view_user_data']) ? 'checked' : ''; ?>>
                     <span>View user data and documents</span>
                  </label>
                  <label class="permission-option">
                     <input type="checkbox" name="can_upload_user_documents" value="1" <?= !empty($account['can_upload_user_documents']) ? 'checked' : ''; ?>>
                     <span>Upload user documents</span>
                  </label>
                  <label class="permission-option">
                     <input type="checkbox" name="can_register_admins" value="1" <?= !empty($account['can_register_admins']) ? 'checked' : ''; ?>>
                     <span>Register new admins</span>
                  </label>
                  <button type="submit" name="save_permissions" class="option-btn">Save rights</button>
               </form>
            <?php } ?>
         </div>
      <?php } ?>
   </div>
</section>

<script src="../js/admin_script.js"></script>
</body>
</html>
