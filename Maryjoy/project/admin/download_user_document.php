<?php

include '../components/connect.php';
require_once '../components/admin_access.php';

if(session_status() === PHP_SESSION_NONE){ session_start(); }
$admin_id = (int)($_SESSION['admin_id'] ?? 0);
if($admin_id === 0){
   http_response_code(403);
   exit('Admin login required.');
}

ensure_admin_access_schema($conn);
$admin_access = get_admin_access($conn, $admin_id);
if(!admin_can_view_user_data($admin_access)){
   http_response_code(403);
   exit('User document access required.');
}

$document_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$select_document = $conn->prepare("SELECT `original_name`, `mime_type`, `file_data` FROM `user_documents` WHERE `id` = ? LIMIT 1");
$select_document->execute([$document_id ?: 0]);
$document = $select_document->fetch(PDO::FETCH_ASSOC);
if(!$document){
   http_response_code(404);
   exit('Document not found.');
}

$allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
$mime_type = in_array($document['mime_type'], $allowed_types, true) ? $document['mime_type'] : 'application/octet-stream';
$file_name = str_replace(['"', '\\'], '', basename($document['original_name']));
$file_name = preg_replace('/[\x00-\x1F\x7F]/', '', $file_name);
$safe_file_name = preg_replace('/[^A-Za-z0-9._-]/', '_', $file_name);
header('Content-Type: ' . $mime_type);
header('Content-Disposition: attachment; filename="' . $safe_file_name . '"; filename*=UTF-8\'\'' . rawurlencode($file_name));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
header('Content-Length: ' . strlen($document['file_data']));
echo $document['file_data'];
