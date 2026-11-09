let navbar = document.querySelector('.header .flex .navbar');
let profile = document.querySelector('.header .flex .profile');
let menuBackdrop = document.querySelector('.admin-menu-backdrop');

const closeNavbar = () =>{
   navbar.classList.remove('active');
   document.body.classList.remove('admin-menu-open');
   if(menuBackdrop) menuBackdrop.hidden = true;
}

document.querySelectorAll('.admin-nav-dropdown').forEach(dropdown =>{
   dropdown.addEventListener('toggle', () =>{
      if(!dropdown.open) return;

      document.querySelectorAll('.admin-nav-dropdown[open]').forEach(openDropdown =>{
         if(openDropdown !== dropdown){
            openDropdown.open = false;
         }
      });
   });
});

document.querySelector('#menu-btn').onclick = () =>{
   const isOpen = navbar.classList.toggle('active');
   document.body.classList.toggle('admin-menu-open', isOpen);
   if(menuBackdrop) menuBackdrop.hidden = !isOpen;
   profile.classList.remove('active');
}

document.querySelector('#user-btn').onclick = () =>{
   profile.classList.toggle('active');
   closeNavbar();
}

window.onscroll = () =>{
   closeNavbar();
   profile.classList.remove('active');
}

if(menuBackdrop){
   menuBackdrop.onclick = closeNavbar;
}

let mainImage = document.querySelector('.update-product .image-container .main-image img');
let subImages = document.querySelectorAll('.update-product .image-container .sub-image img');

subImages.forEach(images =>{
   images.onclick = () =>{
      src = images.getAttribute('src');
      mainImage.src = src;
   }
});