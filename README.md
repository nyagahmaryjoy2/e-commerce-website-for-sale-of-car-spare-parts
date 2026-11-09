# Speedo Parts

Speedo Parts is a PHP and MySQL e-commerce website for browsing and purchasing car spare parts. Customers can search products, manage a cart or wishlist, place orders, and choose cash-on-delivery or M-Pesa sandbox payments. The project includes an administration panel for managing products, orders, customer messages, and administrator access.

## Features

### Customer storefront
- Browse products and categories, search the catalog, and view product details
- Register, sign in, and update a customer profile
- Add products to a wishlist or shopping cart
- Place and review orders
- Send messages through the contact form
- Choose cash-on-delivery or initiate an M-Pesa sandbox payment

### Administration
- Add, edit, and remove products
- View orders and update payment status
- Review customer accounts and messages
- Grant administrator permissions through a super-admin account
- Mask customer names and personal details for admins without the relevant permission
- Upload user documents as PDF, JPG, or PNG files up to 5 MB
- Enforce a five-minute inactivity timeout for customer and admin sessions

## Technology

- PHP 8+
- MySQL or MariaDB
- PDO for database access
- HTML, CSS, and JavaScript
- Swiper for sliders
- Font Awesome for icons
- XAMPP and phpMyAdmin for local development

## Local Setup

1. Install XAMPP with PHP 8 or later and MySQL/MariaDB.
2. Place the project under XAMPP’s `htdocs` directory.
3. Start Apache and MySQL from the XAMPP Control Panel.
4. Open phpMyAdmin and create or select a database named `shop_db`.
5. Import `Maryjoy/project/shop_db.sql` into `shop_db`.
6. Check the database settings in `Maryjoy/project/components/connect.php`. The default configuration expects MySQL at `localhost`, user `root`, and an empty password.
7. Open the storefront in your browser at `http://localhost/Maryjoy/project/home.php`.

The first admin account in the included database is designated as the super admin. Use the local demo login shown on the admin login page, then change the default credentials before sharing or deploying the project. The super admin can manage admin permissions from the Admin Rights page.

## M-Pesa

The M-Pesa integration is configured for Safaricom’s sandbox environment. Testing it requires valid sandbox credentials and a callback URL accessible to the payment service. Do not use production credentials in a public repository.

## Security and Production Notes

This project is intended for learning and local demonstration. Before using it with real customers, upgrade the legacy SHA-1 password hashing, review authorization and form protection across all actions, configure secure database credentials and HTTPS, and publish an appropriate privacy policy. Do not commit real M-Pesa credentials, passwords, customer records, or uploaded documents to GitHub.
