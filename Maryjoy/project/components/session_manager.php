<?php

if(session_status() === PHP_SESSION_NONE){
   session_start();
}

$session_timeout_seconds = 300;
$has_authenticated_session = !empty($_SESSION['admin_id']) || !empty($_SESSION['user_id']);
$last_activity = (int)($_SESSION['last_activity'] ?? time());

if($has_authenticated_session && time() - $last_activity >= $session_timeout_seconds){
   $expired_session_type = !empty($_SESSION['admin_id']) ? 'admin' : 'user';
   $_SESSION = [];
   session_regenerate_id(true);
   $_SESSION['session_expired_for'] = $expired_session_type;
}elseif($has_authenticated_session){
   $_SESSION['last_activity'] = time();
}
