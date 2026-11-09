<?php
   require_once 'admin_access.php';
   ensure_admin_access_schema($conn);
   $header_admin_access = get_admin_access($conn, (int)$admin_id);

   if(isset($message)){
      foreach($message as $message){
         echo '
         <div class="message">
            <span>'.$message.'</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
      }
   }
?>

<header class="header">

   <section class="flex">
      <a href="../admin/dashboard.php" class="logo"><i class="fas fa-car"></i> Admin<span>Panel</span></a>

      <nav class="navbar">
         <a href="../admin/dashboard.php">Home</a>
         <a href="../admin/products.php">Products</a>
         <a href="../admin/placed_orders.php">Orders</a>
         <a href="../admin/mpesa_settings.php">M-Pesa Settings</a>
         <?php if(!empty($header_admin_access['is_super_admin']) || admin_can_register_admins($header_admin_access)){ ?>
            <details class="admin-nav-dropdown">
               <summary>Admin</summary>
               <div class="admin-nav-menu">
                  <?php if(!empty($header_admin_access['is_super_admin'])){ ?>
                     <a href="../admin/admin_accounts.php">Admin Accounts</a>
                  <?php } ?>
                  <?php if(admin_can_register_admins($header_admin_access)){ ?>
                     <a href="../admin/register_admin.php">Admin Creation</a>
                  <?php } ?>
                  <?php if(!empty($header_admin_access['is_super_admin'])){ ?>
                     <a href="../admin/admin_permissions.php">Admin Rights</a>
                  <?php } ?>
               </div>
            </details>
         <?php } ?>
         <details class="admin-nav-dropdown">
            <summary>Users</summary>
            <div class="admin-nav-menu">
               <a href="../admin/users_accounts.php">Users</a>
               <?php if(admin_can_view_user_data($header_admin_access) || admin_can_upload_user_documents($header_admin_access)){ ?>
                  <a href="../admin/user_data.php">User Data</a>
               <?php } ?>
               <a href="../admin/messages.php">Messages</a>
            </div>
         </details>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
      </div>

      <div class="profile">
         <?php
            $select_profile = $conn->prepare("SELECT * FROM `admins` WHERE id = ?");
            $select_profile->execute([$admin_id]);
            $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <p><?= $fetch_profile['name']; ?></p>
         <a href="../admin/update_profile.php" class="btn">Update Profile</a>
         <?php if(admin_can_register_admins($header_admin_access)){ ?>
            <a href="../admin/register_admin.php" class="option-btn">Register New Admin</a>
         <?php } ?>
         <a href="../components/admin_logout.php" class="delete-btn" onclick="return confirm('logout from the website?');">Log Out</a>
      </div>
         

   </section>

</header>

<button class="admin-menu-backdrop" type="button" aria-label="Close admin menu" hidden></button>

<?php if(!empty($admin_id)){ ?>
   <script src="../js/session_timeout.js" data-login-url="admin_login.php" data-heartbeat-url="../components/session_heartbeat.php"></script>
<?php } ?>