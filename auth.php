<?php 

    if(!isset($_SESSION['admin_id'])) {
        header('location: index.php');
        exit();
    }