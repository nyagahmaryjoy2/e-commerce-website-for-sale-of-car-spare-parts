<?php

include 'connect.php';

if(session_status() === PHP_SESSION_NONE){ session_start(); }
session_unset();
session_destroy();

header('location:../home.php');

?>