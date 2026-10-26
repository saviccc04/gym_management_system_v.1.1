<?php
require_once 'config.php';
require_once 'auth.php';

redirect_if(!isset($_SESSION['admin_id']), 'index.php');

function count_table(mysqli $conn, string $table) {
    $sql = "SELECT COUNT(*) as number FROM $table";
    $result = $conn->query($sql);

    return (int)$result->fetch_row()[0];
}