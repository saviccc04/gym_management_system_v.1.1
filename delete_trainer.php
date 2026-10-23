<?php

require_once 'config.php';
require_once 'auth.php';

redirect_if(!isset($_SESSION['admin_id']), 'index.php');

redirect_if($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['delete_id']), 'trainers.php');

try {
    $delete_id = (int)$_POST['delete_id'];
    $sql = "DELETE FROM trainers WHERE trainer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        $_SESSION['error_message'] = "Trainer not found.";
    } else {
        $_SESSION['success_message'] = "Trainer deleted. Their members no longer have a trainer.";
    }
} catch(mysqli_sql_exception $e) {
    $_SESSION['error_message'] = "Something went wrong.";
}

header('location: trainers.php');
exit();