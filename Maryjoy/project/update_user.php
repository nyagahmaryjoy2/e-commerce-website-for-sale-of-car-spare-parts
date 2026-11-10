<?php

include 'components/connect.php';
include 'components/config.php';

if(session_status() === PHP_SESSION_NONE){ session_start(); }

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

$select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
$select_profile->execute([$user_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC) ?: [];

$profile_name = trim((string)($fetch_profile['name'] ?? ''));
$name_parts = preg_split('/\s+/', $profile_name, 2);
$first_name_value = trim((string)($fetch_profile['first_name'] ?? ($name_parts[0] ?? '')));
$last_name_value = trim((string)($fetch_profile['last_name'] ?? ($name_parts[1] ?? '')));
$email_value = trim((string)($fetch_profile['email'] ?? ''));
$phone_value = trim((string)($fetch_profile['phone'] ?? ''));
$location_value = trim((string)($fetch_profile['location'] ?? ''));
$address_value = trim((string)($fetch_profile['address'] ?? ''));
$location_lat_value = (float)($fetch_profile['location_lat'] ?? 0);
$location_lng_value = (float)($fetch_profile['location_lng'] ?? 0);

if(isset($_POST['submit'])){

   $first_name = trim($_POST['first_name'] ?? '');
   $first_name = htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8');
   $last_name = trim($_POST['last_name'] ?? '');
   $last_name = htmlspecialchars($last_name, ENT_QUOTES, 'UTF-8');
   $name = $first_name . ' ' . $last_name;
   $email = trim($_POST['email'] ?? '');
   $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
   $phone = trim($_POST['phone'] ?? '');
   $phone = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
   $address = trim($_POST['address'] ?? '');
   $address = htmlspecialchars($address, ENT_QUOTES, 'UTF-8');
   $location = trim($_POST['location'] ?? '');
   $location = htmlspecialchars($location, ENT_QUOTES, 'UTF-8');

   // Validate phone number with country code support
   if (!preg_match('/^\+(?:[1-9]\d{0,2})\d{6,14}$/', $phone) && !preg_match('/^(?:0)[17]\d{8}$/', $phone)) {
      $message[] = 'Please enter a valid phone number with a country code (e.g., +254712345678 or 0712345678)';
   } else {

      $location_lat = (float)($_POST['location_lat'] ?? 0);
      $location_lng = (float)($_POST['location_lng'] ?? 0);
      $update_profile = $conn->prepare("UPDATE `users` SET name = ?, first_name = ?, last_name = ?, email = ?, phone = ?, address = ?, location = ?, location_lat = ?, location_lng = ? WHERE id = ?");
      $update_profile->execute([$name, $first_name, $last_name, $email, $phone, $address, $location, $location_lat, $location_lng, $user_id]);

      $empty_pass = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';
      $prev_pass = $_POST['prev_pass'];
      $old_pass = sha1($_POST['old_pass']);
      $new_pass = sha1($_POST['new_pass']);
      $cpass = sha1($_POST['cpass']);

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
      <input type="hidden" name="prev_pass" value="<?= htmlspecialchars((string)($fetch_profile['password'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
      <input type="text" name="first_name" required placeholder="enter your first name" maxlength="20"  class="box" value="<?= htmlspecialchars($first_name_value, ENT_QUOTES, 'UTF-8'); ?>">
      <input type="text" name="last_name" required placeholder="enter your last name" maxlength="20"  class="box" value="<?= htmlspecialchars($last_name_value, ENT_QUOTES, 'UTF-8'); ?>">
      <input type="email" name="email" required placeholder="enter your email" maxlength="50"  class="box" oninput="this.value = this.value.replace(/\s/g, '')" value="<?= htmlspecialchars($email_value, ENT_QUOTES, 'UTF-8'); ?>">
      <div style="display:flex; gap:0.8rem; align-items:center; margin-bottom:1rem;">
         <select id="countryCodeSelect" class="box" style="max-width:180px;">
            <option value="+254">🇰🇪 Kenya (+254)</option>
            <option value="+256">🇺🇬 Uganda (+256)</option>
            <option value="+255">🇹🇿 Tanzania (+255)</option>
            <option value="+233">🇬🇭 Ghana (+233)</option>
            <option value="+1">🇺🇸 United States (+1)</option>
            <option value="+44">🇬🇧 United Kingdom (+44)</option>
            <option value="+61">🇦🇺 Australia (+61)</option>
            <option value="+27">🇿🇦 South Africa (+27)</option>
         </select>
         <input type="text" id="phoneInput" name="phone" placeholder="enter your phone number" maxlength="20" class="box" value="<?= htmlspecialchars($phone_value, ENT_QUOTES, 'UTF-8'); ?>" style="flex:1;">
      </div>
      <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:0.75rem;">
         <label style="font-weight:600; color:#333;">Delivery location</label>
         <button type="button" id="toggleLocationMode" class="option-btn" style="padding:0.75rem 1rem; font-size:0.82rem; border-radius:8px; min-width:160px;">Choose on map</button>
      </div>
      <input type="text" id="locationInput" name="location" placeholder="select your delivery location" maxlength="255" class="box" value="<?= htmlspecialchars($location_value, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off">
      <input type="hidden" id="locationLat" name="location_lat" value="<?= htmlspecialchars((string)$location_lat_value, ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" id="locationLng" name="location_lng" value="<?= htmlspecialchars((string)$location_lng_value, ENT_QUOTES, 'UTF-8'); ?>">
      <div id="locationMap" style="height:300px; width:100%; border-radius:10px; overflow:hidden; margin-bottom:1rem;"></div>
      <textarea name="address" placeholder="enter your address" class="box" maxlength="500"><?= htmlspecialchars($address_value, ENT_QUOTES, 'UTF-8'); ?></textarea>
      <div style="margin:1rem 0;">
         <button type="button" id="togglePasswordFields" class="option-btn" style="width:100%; padding:0.9rem 1rem; border-radius:8px;">Change Password</button>
      </div>
      <div id="passwordFields" style="display:none;">
         <input type="password" name="old_pass" placeholder="enter your old password" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="password" name="new_pass" placeholder="enter your new password" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="password" name="cpass" placeholder="confirm your new password" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      </div>
      <input type="submit" value="update profile" class="btn" name="submit">
   </form>

</section>













<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>
<script>
const GOOGLE_MAPS_API_KEY = <?php echo json_encode((string)$GOOGLE_MAPS_API_KEY, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
const countryCodeSelect = document.getElementById('countryCodeSelect');
const phoneInput = document.getElementById('phoneInput');
const locationInput = document.getElementById('locationInput');
const toggleLocationMode = document.getElementById('toggleLocationMode');
const locationMap = document.getElementById('locationMap');
const togglePasswordFields = document.getElementById('togglePasswordFields');
const passwordFields = document.getElementById('passwordFields');
const hasMapApiKey = !!GOOGLE_MAPS_API_KEY && GOOGLE_MAPS_API_KEY !== 'YOUR_API_KEY_HERE';

function setLocationMode(useMap) {
   const canUseMap = useMap && hasMapApiKey;
   locationMap.style.display = canUseMap ? 'block' : 'none';
   locationInput.placeholder = canUseMap ? 'select your delivery location' : 'enter your delivery location manually';
   toggleLocationMode.textContent = canUseMap ? 'Type manually' : 'Choose on map';
}

if (toggleLocationMode) {
   toggleLocationMode.addEventListener('click', function() {
      const useMap = locationMap.style.display === 'none';
      setLocationMode(useMap);
      if (!useMap) {
         locationInput.focus();
      }
   });
}

if (togglePasswordFields && passwordFields) {
   togglePasswordFields.addEventListener('click', function() {
      const isHidden = passwordFields.style.display === 'none';
      passwordFields.style.display = isHidden ? 'block' : 'none';
      togglePasswordFields.textContent = isHidden ? 'Hide Password Fields' : 'Change Password';
      if (isHidden) {
         passwordFields.querySelector('input[name="old_pass"]').focus();
      }
   });
}

setLocationMode(false);

function formatPhoneWithCountryCode(value, code) {
   const digits = (value || '').replace(/\D/g, '');
   if (!digits) return code;
   if (value.startsWith('+')) {
      return code + digits.replace(new RegExp('^' + code.replace(/\+/g, '').replace(/\D/g, ''), 'g'), '');
   }
   return code + digits;
}

function syncCountryCode() {
   const code = countryCodeSelect.value;
   const currentValue = phoneInput.value || '';
   const digits = currentValue.replace(/\D/g, '');
   if (!digits) {
      phoneInput.value = code;
      return;
   }
   const existingCode = currentValue.match(/^\+\d{1,4}/);
   if (existingCode && existingCode[0] !== code) {
      phoneInput.value = code + digits;
      return;
   }
   if (currentValue.startsWith('0')) {
      phoneInput.value = code + digits.substring(1);
      return;
   }
   if (!currentValue.startsWith(code)) {
      phoneInput.value = code + digits;
   }
}

countryCodeSelect.addEventListener('change', syncCountryCode);
phoneInput.addEventListener('input', function() {
   const selectedCode = countryCodeSelect.value;
   let value = phoneInput.value.replace(/\D/g, '');
   if (!value) {
      phoneInput.value = selectedCode;
      return;
   }
   if (phoneInput.value.startsWith('+')) {
      value = value.replace(/^\d+/, '');
      phoneInput.value = selectedCode + value;
      return;
   }
   if (phoneInput.value.startsWith('0')) {
      value = value.substring(1);
      phoneInput.value = selectedCode + value;
      return;
   }
   phoneInput.value = selectedCode + value;
});

if (phoneInput.value) {
   const currentValue = phoneInput.value.trim();
   const matchedCode = Array.from(countryCodeSelect.options).find(option => currentValue.startsWith(option.value));
   if (matchedCode) {
      countryCodeSelect.value = matchedCode.value;
   }
   syncCountryCode();
}

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
   if (!hasMapApiKey) {
      showMapError('Google Maps is not configured. You can still type your delivery location manually.');
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

if (hasMapApiKey) {
   loadGoogleMaps();
} else {
   showMapError('Google Maps is not configured. You can still type your delivery location manually.');
}
</script>

</body>
</html>