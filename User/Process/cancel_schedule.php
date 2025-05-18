<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['schedule_id'])) {
    header("Location: schedule_status.php");
    exit();
}

$schedule_id = $_POST['schedule_id'];
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("DELETE FROM schedules WHERE id = ? AND user_id = ? AND status = 'Pending'");
$stmt->execute([$schedule_id, $user_id]);

header("Location: ../home.php");
exit();
?>
