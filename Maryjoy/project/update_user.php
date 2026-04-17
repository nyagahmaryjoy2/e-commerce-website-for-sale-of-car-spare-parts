<?php

include 'components/connect.php';
include 'components/config.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

$select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
$select_profile->execute([$user_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

if(isset($_POST['submit'])){

   $first_name = $_POST['first_name'];
   $first_name = filter_var($first_name, FILTER_SANITIZE_STRING);
   $last_name = $_POST['last_name'];
   $last_name = filter_var($last_name, FILTER_SANITIZE_STRING);
   $name = $first_name . ' ' . $last_name;
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $phone = $_POST['phone'];
   $phone = filter_var($phone, FILTER_SANITIZE_STRING);
   $address = $_POST['address'];
   $address = filter_var($address, FILTER_SANITIZE_STRING);
   $location = $_POST['location'];
   $location = filter_var($location, FILTER_SANITIZE_STRING);

   // Validate Kenyan phone number
   if (!preg_match('/^(?:\+254|0)[17]\d{8}$/', $phone)) {
      $message[] = 'Please enter a valid Kenyan phone number (e.g., +254712345678 or 0712345678)';
   } else {

      $location_lat = $_POST['location_lat'];
      $location_lat = filter_var($location_lat, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
      $location_lng = $_POST['location_lng'];
      $location_lng = filter_var($location_lng, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
      $update_profile = $conn->prepare("UPDATE `users` SET name = ?, first_name = ?, last_name = ?, email = ?, phone = ?, address = ?, location = ?, location_lat = ?, location_lng = ? WHERE id = ?");
      $update_profile->execute([$name, $first_name, $last_name, $email, $phone, $address, $location, $location_lat, $location_lng, $user_id]);

      $empty_pass = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';
      $prev_pass = $_POST['prev_pass'];
      $old_pass = sha1($_POST['old_pass']);
      $old_pass = filter_var($old_pass, FILTER_SANITIZE_STRING);
      $new_pass = sha1($_POST['new_pass']);
      $new_pass = filter_var($new_pass, FILTER_SANITIZE_STRING);
      $cpass = sha1($_POST['cpass']);
      $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

      $password_updated = false;

      if($old_pass != $empty_pass){
         if($old_pass != $prev_pass){
            $message[] = 'old password not matched!';
         }elseif($new_pass != $cpass){
            $message[] = 'confirm password not matched!';
         }else{
            if($new_pass != $empty_pass){
               $update_admin_pass = $conn->prepare("UPDATE `users` SET password = ? WHERE id = ?");
               $update_admin_pass->execute([$cpass, $user_id]);
               $password_updated = true;
            }else{
               $message[] = 'please enter a new password!';
            }
         }
      }

      if(empty($message)){
         $message[] = 'Profile updated successfully!';
         if($password_updated){
            $message[] = 'Password updated successfully!';
         }
         header('location:view_profile.php');
         exit;
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
   <title>Register</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="form-container">

   <form action="" method="post">
      <h3>Update Profile</h3>
      <input type="hidden" name="prev_pass" value="<?= $fetch_profile["password"]; ?>">
      <input type="text" name="first_name" required placeholder="enter your first name" maxlength="20"  class="box" value="<?= $fetch_profile["first_name"]; ?>">
      <input type="text" name="last_name" required placeholder="enter your last name" maxlength="20"  class="box" value="<?= $fetch_profile["last_name"]; ?>">
      <input type="email" name="email" required placeholder="enter your email" maxlength="50"  class="box" oninput="this.value = this.value.replace(/\s/g, '')" value="<?= $fetch_profile["email"]; ?>">
      <input type="text" name="phone" placeholder="enter your phone number (e.g., +254712345678 or 0712345678)" maxlength="15" class="box" value="<?= $fetch_profile["phone"]; ?>">
      <input type="text" id="locationInput" name="location" placeholder="select your delivery location" maxlength="255" class="box" value="<?= $fetch_profile["location"]; ?>" autocomplete="off">
      <input type="hidden" id="locationLat" name="location_lat" value="<?= $fetch_profile["location_lat"]; ?>">
      <input type="hidden" id="locationLng" name="location_lng" value="<?= $fetch_profile["location_lng"]; ?>">
      <div id="locationMap" style="height:300px; width:100%; border-radius:10px; overflow:hidden; margin-bottom:1rem;"></div>
      <textarea name="address" placeholder="enter your address" class="box" maxlength="500"><?= $fetch_profile["address"]; ?></textarea>
      <h4>Change Password (Optional)</h4>
      <input type="password" name="old_pass" placeholder="enter your old password" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="new_pass" placeholder="enter your new password" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="cpass" placeholder="confirm your new password" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="submit" value="update profile" class="btn" name="submit">
   </form>

</section>













<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>
<script>
const GOOGLE_MAPS_API_KEY = '<?php echo $GOOGLE_MAPS_API_KEY; ?>';
let map;
let marker;
let autocomplete;

function showMapError(message) {
   const mapContainer = document.getElementById('locationMap');
   mapContainer.innerHTML = `<div style="padding:20px; color:#900; background:#ffe6e6; text-align:center;">${message}</div>`;
}

function initMap() {
   const defaultPosition = { lat: -1.286389, lng: 36.817223 }; // Nairobi center
   const initialPosition = { lat: parseFloat(document.getElementById('locationLat').value) || defaultPosition.lat, lng: parseFloat(document.getElementById('locationLng').value) || defaultPosition.lng };

   map = new google.maps.Map(document.getElementById('locationMap'), {
      center: initialPosition,
      zoom: 12,
   });

   marker = new google.maps.Marker({
      position: initialPosition,
      map: map,
      draggable: true,
   });

   autocomplete = new google.maps.places.Autocomplete(document.getElementById('locationInput'));
   autocomplete.setFields(['formatted_address', 'geometry']);
   autocomplete.addListener('place_changed', function() {
      const place = autocomplete.getPlace();
      if (!place.geometry) return;
      map.panTo(place.geometry.location);
      map.setZoom(15);
      marker.setPosition(place.geometry.location);
      document.getElementById('locationInput').value = place.formatted_address;
      document.getElementById('locationLat').value = place.geometry.location.lat();
      document.getElementById('locationLng').value = place.geometry.location.lng();
   });

   google.maps.event.addListener(marker, 'dragend', function() {
      const position = marker.getPosition();
      document.getElementById('locationLat').value = position.lat();
      document.getElementById('locationLng').value = position.lng();
      const geocoder = new google.maps.Geocoder();
      geocoder.geocode({ location: position }, function(results, status) {
         if (status === 'OK' && results[0]) {
            document.getElementById('locationInput').value = results[0].formatted_address;
         }
      });
   });
}

function loadGoogleMaps() {
   if (!GOOGLE_MAPS_API_KEY || GOOGLE_MAPS_API_KEY === 'YOUR_GOOGLE_MAPS_API_KEY') {
      showMapError('Google Maps API key is not configured. Add a valid API key in update_user.php.');
      return;
   }
   const script = document.createElement('script');
   script.src = `https://maps.googleapis.com/maps/api/js?key=${GOOGLE_MAPS_API_KEY}&libraries=places&callback=initMap`;
   script.async = true;
   script.defer = true;
   script.onerror = function() {
      showMapError('Failed to load Google Maps. Check your API key and network connection.');
   };
   document.head.appendChild(script);
}

loadGoogleMaps();
</script>

</body>
</html>