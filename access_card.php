<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'helpers.php';
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;

redirect_if(!isset($_SESSION['admin_id']), 'index.php');

$member_id = $_GET['id'] ?? '';

$member_id = filter_var($member_id, FILTER_VALIDATE_INT);

redirect_if($member_id === false, 'members.php');

$sql = "SELECT m.*,  tp.name as plan_name,  CONCAT(t.first_name,' ',  t.last_name) as trainer_name
FROM members as m
LEFT JOIN training_plans as tp
ON m.plan_id = tp.plan_id
LEFT JOIN trainers as t 
on m.trainer_id = t.trainer_id
WHERE m.member_id = ?
ORDER BY m.last_name, m.first_name";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $member_id);
$stmt->execute();

$result = $stmt->get_result();
$member = $result->fetch_assoc();

$html = render_access_card($member);

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper([0, 0, 243, 153]);
$dompdf->render();
$dompdf->stream('access_card_' . $member['member_id'] . '.pdf', ['Attachment' => false]);
exit();