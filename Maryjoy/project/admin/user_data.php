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
$can_view = admin_can_view_user_data($admin_access);
$can_upload = admin_can_upload_user_documents($admin_access);
if(!$can_view && !$can_upload){
   http_response_code(403);
   exit('User data access required.');
}

if(isset($_POST['upload_document'])){
   if(!$can_upload){
      http_response_code(403);
      exit('Document upload permission required.');
   }

   if(!verify_admin_csrf_token($_POST['csrf_token'] ?? null)){
      $message[] = 'Your session expired. Reload the page and try again.';
   }elseif(!isset($_FILES['user_document']) || $_FILES['user_document']['error'] !== UPLOAD_ERR_OK){
      $message[] = 'Choose a document to upload.';
   }elseif($_FILES['user_document']['size'] > 5242880){
      $message[] = 'Documents must be 5 MB or smaller.';
   }else{
      $user_id = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
      $file = $_FILES['user_document'];
      $original_name = basename($file['name']);
      $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
      $file_data = file_get_contents($file['tmp_name']);
      $signature = $file_data === false ? '' : substr($file_data, 0, 8);
      if(substr($signature, 0, 5) === '%PDF-'){
         $mime_type = 'application/pdf';
      }elseif(substr($signature, 0, 3) === "\xFF\xD8\xFF"){
         $mime_type = 'image/jpeg';
      }elseif($signature === "\x89PNG\r\n\x1A\n"){
         $mime_type = 'image/png';
      }else{
         $mime_type = '';
      }
      $allowed_types = ['application/pdf' => ['pdf'], 'image/jpeg' => ['jpg', 'jpeg'], 'image/png' => ['png']];

      $user_check = $conn->prepare("SELECT `id` FROM `users` WHERE `id` = ? LIMIT 1");
      $user_check->execute([$user_id ?: 0]);
      if(!$user_check->fetch(PDO::FETCH_ASSOC)){
         $message[] = 'Select a valid user account.';
      }elseif($file_data === false){
         $message[] = 'The uploaded document could not be read.';
      }elseif(strlen($file_data) > 5242880 || strlen($original_name) > 255 || !isset($allowed_types[$mime_type]) || !in_array($extension, $allowed_types[$mime_type], true)){
         $message[] = 'Only PDF, JPG, and PNG documents are allowed.';
      }else{
         $insert_document = $conn->prepare("INSERT INTO `user_documents` (`user_id`, `uploaded_by`, `original_name`, `mime_type`, `file_data`) VALUES (?, ?, ?, ?, ?)");
         $insert_document->bindValue(1, (int)$user_id, PDO::PARAM_INT);
         $insert_document->bindValue(2, $admin_id, PDO::PARAM_INT);
         $insert_document->bindValue(3, $original_name, PDO::PARAM_STR);
         $insert_document->bindValue(4, $mime_type, PDO::PARAM_STR);
         $insert_document->bindValue(5, $file_data, PDO::PARAM_LOB);
         $insert_document->execute();
         $message[] = 'Document uploaded securely.';
      }
   }
}

$users = $conn->query("SELECT `id`, `name`, `email` FROM `users` ORDER BY `id` DESC")->fetchAll(PDO::FETCH_ASSOC);
$documents = [];
if($can_view){
   $documents = $conn->query("SELECT `user_documents`.`id`, `user_documents`.`user_id`, `user_documents`.`original_name`, `user_documents`.`mime_type`, `user_documents`.`uploaded_at`, `users`.`name`, `users`.`email` FROM `user_documents` LEFT JOIN `users` ON `users`.`id` = `user_documents`.`user_id` ORDER BY `user_documents`.`uploaded_at` DESC")->fetchAll(PDO::FETCH_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>User Data</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="user-data">
   <h1 class="heading">User Data</h1>

   <?php if($can_view){ ?>
      <h2 class="subheading">User accounts</h2>
      <div class="user-data-list">
         <?php if(empty($users)){ ?>
            <p class="empty">No user accounts available.</p>
         <?php }else{ foreach($users as $user){ ?>
            <div class="user-data-row">
               <span>User #<?= (int)$user['id']; ?></span>
               <span><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?></span>
               <span><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
         <?php }} ?>
      </div>
   <?php }else{ ?>
      <p class="permission-notice">Customer names, emails, and documents are hidden. Request “View user data and documents” from the super admin to access them.</p>
   <?php } ?>

   <?php if($can_upload){ ?>
      <div class="user-data-section">
         <h2 class="subheading">Upload a user document</h2>
         <form class="user-document-form" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
            <label for="user_id">User account</label>
            <select id="user_id" name="user_id" class="box" required>
               <option value="">Select a user</option>
               <?php foreach($users as $user){ ?>
                  <option value="<?= (int)$user['id']; ?>">
                     <?php if($can_view){ ?>
                        <?= 'User #' . (int)$user['id'] . ' - ' . htmlspecialchars($user['name'] . ' (' . $user['email'] . ')', ENT_QUOTES, 'UTF-8'); ?>
                     <?php }else{ ?>
                        <?= 'User #' . (int)$user['id']; ?>
                     <?php } ?>
                  </option>
               <?php } ?>
            </select>
            <label for="user_document">Document (PDF, JPG, or PNG; max 5 MB)</label>
            <input id="user_document" type="file" name="user_document" accept=".pdf,.jpg,.jpeg,.png" class="box" required>
            <button type="submit" name="upload_document" class="btn">Upload document</button>
         </form>
      </div>
   <?php } ?>

   <?php if($can_view){ ?>
      <div class="user-data-section">
         <h2 class="subheading">Uploaded documents</h2>
         <?php if(empty($documents)){ ?>
            <p class="empty">No user documents uploaded.</p>
         <?php }else{ ?>
            <div class="user-data-list">
               <?php foreach($documents as $document){ ?>
                  <div class="user-data-row">
                     <span><?= htmlspecialchars($document['name'] ?: 'Deleted user', ENT_QUOTES, 'UTF-8'); ?> (User #<?= (int)$document['user_id']; ?>)</span>
                     <span><?= htmlspecialchars($document['original_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                     <span><?= htmlspecialchars($document['uploaded_at'], ENT_QUOTES, 'UTF-8'); ?></span>
                     <a class="option-btn" href="download_user_document.php?id=<?= (int)$document['id']; ?>">Download</a>
                  </div>
               <?php } ?>
            </div>
         <?php } ?>
      </div>
   <?php } ?>
</section>

<script src="../js/admin_script.js"></script>
</body>
</html>
