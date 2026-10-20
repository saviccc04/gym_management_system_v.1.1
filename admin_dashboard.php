<?php
    require_once 'config.php';
    require_once 'auth.php';
    
    redirect_if(!isset($_SESSION['admin_id']), 'index.php');
?>