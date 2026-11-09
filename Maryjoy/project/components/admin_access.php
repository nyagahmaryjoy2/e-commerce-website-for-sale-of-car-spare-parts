<?php

function ensure_admin_access_schema(PDO $conn): void
{
   $columns = $conn->query("SHOW COLUMNS FROM `admins`")->fetchAll(PDO::FETCH_COLUMN, 0);
   $required_columns = [
      'is_super_admin' => 'TINYINT(1) NOT NULL DEFAULT 0',
      'can_view_user_data' => 'TINYINT(1) NOT NULL DEFAULT 0',
      'can_upload_user_documents' => 'TINYINT(1) NOT NULL DEFAULT 0',
      'can_register_admins' => 'TINYINT(1) NOT NULL DEFAULT 0',
   ];

   foreach($required_columns as $column => $definition){
      if(!in_array($column, $columns, true)){
         $conn->exec("ALTER TABLE `admins` ADD COLUMN `$column` $definition");
      }
   }

   $conn->exec("CREATE TABLE IF NOT EXISTS `user_documents` (
      `id` int(100) NOT NULL AUTO_INCREMENT,
      `user_id` int(100) NOT NULL,
      `uploaded_by` int(100) NOT NULL,
      `original_name` varchar(255) NOT NULL,
      `mime_type` varchar(100) NOT NULL,
      `file_data` mediumblob NOT NULL,
      `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `user_id` (`user_id`)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

   $super_admin_exists = (int)$conn->query("SELECT COUNT(*) FROM `admins` WHERE `is_super_admin` = 1")->fetchColumn();
   if($super_admin_exists === 0){
      $first_admin_id = $conn->query("SELECT `id` FROM `admins` ORDER BY `id` ASC LIMIT 1")->fetchColumn();
      if($first_admin_id !== false){
         $promote_admin = $conn->prepare("UPDATE `admins` SET `is_super_admin` = 1 WHERE `id` = ?");
         $promote_admin->execute([(int)$first_admin_id]);
      }
   }
}

function get_admin_access(PDO $conn, int $admin_id): array
{
   $select_admin = $conn->prepare("SELECT `id`, `name`, `is_super_admin`, `can_view_user_data`, `can_upload_user_documents`, `can_register_admins` FROM `admins` WHERE `id` = ? LIMIT 1");
   $select_admin->execute([$admin_id]);
   return $select_admin->fetch(PDO::FETCH_ASSOC) ?: [];
}

function admin_can_view_user_data(array $access): bool
{
   return !empty($access['is_super_admin']) || !empty($access['can_view_user_data']);
}

function admin_can_upload_user_documents(array $access): bool
{
   return !empty($access['is_super_admin']) || !empty($access['can_upload_user_documents']);
}

function admin_can_register_admins(array $access): bool
{
   return !empty($access['is_super_admin']) || !empty($access['can_register_admins']);
}

function mask_customer_name(string $name): string
{
   $name = trim($name);
   return $name === '' ? 'Hidden' : substr($name, 0, 1) . '***';
}

function mask_customer_email(string $email): string
{
   $parts = explode('@', $email, 2);
   if(count($parts) !== 2 || $parts[0] === '' || $parts[1] === ''){
      return 'Hidden';
   }

   $domain_parts = explode('.', $parts[1]);
   $domain = substr($domain_parts[0], 0, 1) . '***';
   if(count($domain_parts) > 1){
      $domain .= '.' . end($domain_parts);
   }

   return substr($parts[0], 0, 1) . '***@' . $domain;
}

function mask_customer_phone(string $phone): string
{
   $digits = preg_replace('/\D/', '', $phone);
   if(strlen($digits) < 3){
      return 'Hidden';
   }

   return str_repeat('*', max(4, strlen($digits) - 2)) . substr($digits, -2);
}

function admin_csrf_token(): string
{
   if(empty($_SESSION['admin_csrf_token'])){
      $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
   }

   return $_SESSION['admin_csrf_token'];
}

function verify_admin_csrf_token($token): bool
{
   return is_string($token)
      && isset($_SESSION['admin_csrf_token'])
      && hash_equals($_SESSION['admin_csrf_token'], $token);
}
