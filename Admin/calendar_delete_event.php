<?php
require_once '../config.php';

$title = $_POST['title'];
$date  = $_POST['date'];

$stmt = $conn->prepare("DELETE FROM events WHERE title=? AND date=?");
$stmt->bind_param("ss", $title, $date);
$stmt->execute();
$stmt->close();
