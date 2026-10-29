<?php

require_once 'config.php';
require_once 'auth.php';

redirect_if(!isset($_SESSION['admin_id']), 'index.php');

redirect_if($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['delete_id']), 'members.php');

try {
    $delete_id = (int)$_POST['delete_id'];

    $photo_sql = "SELECT photo_path FROM members WHERE member_id = ?";
    $photo_stmt = $conn->prepare($photo_sql);
    $photo_stmt->bind_param('i', $delete_id);
    $photo_stmt->execute();

    $result = $photo_stmt->get_result();
    $row = $result->fetch_row();
    $photo_path = $row[0] ?? NULL;

    $sql = "DELETE FROM members WHERE member_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        $_SESSION['error_message'] = "Member not found.";
    } else {
        if(isset($photo_path) && file_exists($photo_path)) {
            unlink($photo_path);
        }
        $_SESSION['success_message'] = "Member deleted.";
    }
} catch(mysqli_sql_exception $e) {
    $_SESSION['error_message'] = "Something went wrong.";
}

header('location: members.php');
exit();