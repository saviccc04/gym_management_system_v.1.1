<?php
    session_start();

    $hostname    = "localhost";
    $db_user     = "root";
    $db_password = "";
    $db_name     = "gym_management_system";

    $conn = new mysqli($hostname, $db_user, $db_password, $db_name);
    $conn->set_charset("utf8mb4");