<?php 

require_once 'config.php';
require_once 'auth.php';

redirect_if(!isset($_SESSION['admin_id']), 'index.php');

$sql = "SELECT m.first_name, m.last_name, m.email, m.phone_number,  tp.name as plan_name,  CONCAT(t.first_name,' ',  t.last_name) as trainer_name
FROM members as m
LEFT JOIN training_plans as tp
ON m.plan_id = tp.plan_id
LEFT JOIN trainers as t 
on m.trainer_id = t.trainer_id
ORDER BY m.last_name, m.first_name";
$result = $conn->query($sql);

$members = $result->fetch_all(MYSQLI_ASSOC);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="members.csv"');

$f = fopen('php://output', 'w');

fwrite($f, "\xEF\xBB\xBF");

fputcsv($f, ['First name', 'Last name', 'Email', 'Phone number', 'Plan', 'Trainer']);

foreach($members as $member) {
    fputcsv($f, [
        $member['first_name'],
        $member['last_name'],
        $member['email'],
        $member['phone_number'] ?? '',
        $member['plan_name'],
        $member['trainer_name'] ?? ''
    ]);
}

fclose($f);

exit();