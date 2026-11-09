<?php

include 'connect.php';

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['expire'])){
   if(!empty($_SESSION['admin_id']) || !empty($_SESSION['user_id'])){
      $expired_session_type = !empty($_SESSION['admin_id']) ? 'admin' : 'user';
      $_SESSION = [];
      session_regenerate_id(true);
      $_SESSION['session_expired_for'] = $expired_session_type;
   }
   http_response_code(204);
   exit;
}

if(empty($_SESSION['admin_id']) && empty($_SESSION['user_id'])){
   http_response_code(401);
   exit;
}

$_SESSION['last_activity'] = time();
http_response_code(204);
