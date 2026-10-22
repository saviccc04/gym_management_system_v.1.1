<?php

require_once 'config.php';
require_once 'auth.php';

redirect_if(!isset($_SESSION['admin_id']), 'index.php');

redirect_if($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['delete_id']), 'plans.php');

try {
    $delete_id = (int)$_POST['delete_id'];
    $sql = "DELETE FROM `training_plans` WHERE plan_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();

    $_SESSION['success_message'] = "The plan has been deleted.";
    
} catch(mysqli_sql_exception $e) {
    $_SESSION['error_message'] = "This plan is used by members and can't be deleted.";
}

header('location: plans.php');
exit();

