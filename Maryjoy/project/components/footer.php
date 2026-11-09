<footer class="footer">

   <section class="grid footer-grid">

      <div class="footer-brand">
         <a href="home.php" class="footer-logo"><i class="fas fa-car-side" aria-hidden="true"></i><span>Speedo</span><strong>PARTS</strong></a>
         <p>Parts for the road ahead.</p>
         <div class="footer-socials" aria-label="Social media">
            <a href="https://www.facebook.com/maryjoynjeri" target="_blank" rel="noopener noreferrer" aria-label="Facebook" title="Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
            <a href="https://twitter.com/maryjoynjeri" target="_blank" rel="noopener noreferrer" aria-label="Twitter" title="Twitter"><i class="fab fa-twitter" aria-hidden="true"></i></a>
            <a href="https://www.instagram.com/_classic.mj_/" target="_blank" rel="noopener noreferrer" aria-label="Instagram" title="Instagram"><i class="fab fa-instagram" aria-hidden="true"></i></a>
            <a href="https://www.linkedin.com/in/maryjoy-nyagah-3b4685305?utm_source=share&utm_campaign=share_via&utm_content=profile&utm_medium=android_app" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn" title="LinkedIn"><i class="fab fa-linkedin-in" aria-hidden="true"></i></a>
            <a href="https://github.com/nyagahmaryjoy2" target="_blank" rel="noopener noreferrer" aria-label="GitHub" title="GitHub"><i class="fab fa-github" aria-hidden="true"></i></a>
         </div>
      </div>

      <div class="box">
         <h3>Explore</h3>
         <a href="home.php"> <i class="fas fa-angle-right"></i> Home</a>
         <a href="about.php"> <i class="fas fa-angle-right"></i> About</a>
         <a href="shop.php"> <i class="fas fa-angle-right"></i> products</a>
         <a href="contact.php"> <i class="fas fa-angle-right"></i> Contact</a>
      </div>

      <div class="box">
         <h3>Your account</h3>
         <a href="user_login.php"> <i class="fas fa-angle-right"></i> Login</a>
         <a href="user_register.php"> <i class="fas fa-angle-right"></i> Register</a>
         <a href="cart.php"> <i class="fas fa-angle-right"></i> Cart</a>
         <a href="orders.php"> <i class="fas fa-angle-right"></i> Orders</a>
      </div>

      <div class="box">
         <h3>Contact Us.</h3>
         <span class="demo-contact"><i class="fas fa-phone"></i> +254 7XX XXX XXX (demo)</span>
         <a href="mailto:support@speedo.example"><i class="fas fa-envelope"></i> support@speedo.example</a>
         <a href="mailto:sales@speedo.example"><i class="fas fa-envelope"></i> sales@speedo.example</a>
         <a href="https://www.google.com/myplace"><i class="fas fa-map-marker-alt"></i> Naivasha </a>
      </div>

   </section>

   <div class="credit">
      <span>&copy; <?= date('Y'); ?> Speedo Parts. All rights reserved.</span>
      <span>Demo storefront</span>
   </div>

</footer>

<?php if(!empty($_SESSION['user_id'])){ ?>
   <script src="js/session_timeout.js" data-login-url="user_login.php" data-heartbeat-url="components/session_heartbeat.php"></script>
<?php } ?>