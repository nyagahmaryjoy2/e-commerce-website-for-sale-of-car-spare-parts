<?php

include 'components/connect.php';

if(session_status() === PHP_SESSION_NONE){ session_start(); }

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:user_login.php');
};

$select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
$select_profile->execute([$user_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC) ?: [];

$profile_name = trim((string)($fetch_profile['name'] ?? ''));
$name_parts = preg_split('/\s+/', $profile_name, 2);
$first_name = trim((string)($fetch_profile['first_name'] ?? ($name_parts[0] ?? '')));
$last_name = trim((string)($fetch_profile['last_name'] ?? ($name_parts[1] ?? '')));
$email = trim((string)($fetch_profile['email'] ?? ''));
$phone = trim((string)($fetch_profile['phone'] ?? ''));
$location = trim((string)($fetch_profile['location'] ?? ''));
$address = trim((string)($fetch_profile['address'] ?? ''));

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>View Profile</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="form-container">

   <form>
      <h3>Your Profile Details</h3>
      <input type="text" name="first_name" placeholder="enter your first name" maxlength="20" class="box" value="<?= htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8'); ?>" readonly>
      <input type="text" name="last_name" placeholder="enter your last name" maxlength="20" class="box" value="<?= htmlspecialchars($last_name, ENT_QUOTES, 'UTF-8'); ?>" readonly>
      <input type="email" name="email" placeholder="enter your email" maxlength="50" class="box" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" readonly>
      <input type="text" name="phone" placeholder="enter your phone number" maxlength="15" class="box" value="<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?>" readonly>
      <input type="text" name="location" placeholder="enter your delivery location" maxlength="255" class="box" value="<?= htmlspecialchars($location, ENT_QUOTES, 'UTF-8'); ?>" readonly>
      <textarea name="address" placeholder="enter your address" class="box" maxlength="500" readonly><?= htmlspecialchars($address, ENT_QUOTES, 'UTF-8'); ?></textarea>
      <a href="update_user.php" class="btn">Update Profile</a>
   </form>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>