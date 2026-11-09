<?php

include '../components/connect.php';

if(session_status() === PHP_SESSION_NONE){ session_start(); }

$admin_id = (int)($_SESSION['admin_id'] ?? 0);

if($admin_id === 0){
   header('location:admin_login.php');
   exit;
}

$conn->exec("CREATE TABLE IF NOT EXISTS `mpesa_settings` (
   `id` int(100) NOT NULL AUTO_INCREMENT,
   `paybill` varchar(50) NOT NULL,
   `passkey` varchar(255) NOT NULL,
   `consumer_key` varchar(255) NOT NULL,
   `consumer_secret` varchar(255) NOT NULL,
   `callback_url` varchar(255) NOT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$select_settings = $conn->prepare("SELECT * FROM `mpesa_settings` ORDER BY id DESC LIMIT 1");
$select_settings->execute();
$fetch_settings = $select_settings->fetch(PDO::FETCH_ASSOC);

if(isset($_POST['submit'])){
   $paybill = trim($_POST['paybill']);
   $passkey = trim($_POST['passkey']);
   $consumer_key = trim($_POST['consumer_key']);
   $consumer_secret = trim($_POST['consumer_secret']);
   $callback_url = filter_var(trim($_POST['callback_url']), FILTER_SANITIZE_URL);

   if($fetch_settings){
      $update_settings = $conn->prepare("UPDATE `mpesa_settings` SET paybill = ?, passkey = ?, consumer_key = ?, consumer_secret = ?, callback_url = ? WHERE id = ?");
      $update_settings->execute([$paybill, $passkey, $consumer_key, $consumer_secret, $callback_url, $fetch_settings['id']]);
   }else{
      $insert_settings = $conn->prepare("INSERT INTO `mpesa_settings`(paybill, passkey, consumer_key, consumer_secret, callback_url) VALUES(?,?,?,?,?)");
      $insert_settings->execute([$paybill, $passkey, $consumer_key, $consumer_secret, $callback_url]);
   }

   header('location:mpesa_settings.php');
   exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>M-Pesa Settings</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="form-container">

   <form action="" method="post">
      <h3>M-Pesa Payment Number Settings</h3>
      <input type="text" name="paybill" required placeholder="Enter Paybill / Till / Business Shortcode / Phone number" maxlength="50" class="box" value="<?= $fetch_settings['paybill'] ?? ''; ?>">
      <p style="margin:0 0 1rem; color:#666; font-size:0.95rem;">Use your Paybill, Till number, or merchant phone number for STK Push payment requests.</p>
      <input type="text" name="passkey" required placeholder="Enter M-Pesa Passkey" maxlength="255" class="box" value="<?= $fetch_settings['passkey'] ?? ''; ?>">
      <input type="text" name="consumer_key" required placeholder="Enter Consumer Key" maxlength="255" class="box" value="<?= $fetch_settings['consumer_key'] ?? ''; ?>">
      <input type="text" name="consumer_secret" required placeholder="Enter Consumer Secret" maxlength="255" class="box" value="<?= $fetch_settings['consumer_secret'] ?? ''; ?>">
      <input type="text" name="callback_url" required placeholder="Enter Callback URL" maxlength="255" class="box" value="<?= $fetch_settings['callback_url'] ?? 'http://localhost:8000/mpesa_callback.php'; ?>">
      <input type="submit" value="Save Settings" class="btn" name="submit">
   </form>

</section>

<script src="../js/admin_script.js"></script>

</body>
</html>