<?php
require_once 'config.php';
require_once 'auth.php';

redirect_if(!isset($_SESSION['admin_id']), 'index.php');

function count_table(mysqli $conn, string $table) {
    $sql = "SELECT COUNT(*) as number FROM $table";
    $result = $conn->query($sql);

    return (int)$result->fetch_row()[0];
}

function validate_member(array $input): array {
    $errors = [];
    $data = [];

    $data['first_name'] = trim($input['first_name'] ?? '');
    $data['last_name'] = trim($input['last_name'] ?? '');
    $data['email'] = trim($input['email'] ?? '');
    $data['phone_number'] = trim($input['phone_number'] ?? '');
    $data['trainer_id'] = (int)($input['trainer_id'] ?? '');
    $data['plan_id'] = trim( $input['plan_id'] ?? '');

    if($data['first_name'] === '') {
        $errors[] = 'First name is required.';
    }

    if($data['last_name'] === '') {
        $errors[] = 'Last name is required.';
    }

    if($data['email'] === '') {
        $errors[] = 'Email is required.';
    } else if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email.'; 
    }

    if($data['phone_number'] === '') {
        $data['phone_number'] = NULL;
    }

    if(!$data['trainer_id']) {
        $data['trainer_id'] = NULL;
    }

    if ($data['plan_id'] === '') {                         
        $errors[] = 'Training plan is required.';
    } else {
        $data['plan_id'] = filter_var($data['plan_id'], FILTER_VALIDATE_INT); 
        if ($data['plan_id'] === false) {
            $errors[] = 'Invalid training plan.';
        }
    }

    return ['data' => $data, 'errors' => $errors];
}