<?php
require_once '../config.php';

$title = $_POST['title'];
$desc  = $_POST['description'];
$date  = $_POST['date'];
$originalTitle = $_POST['original_title'];

$stmt = $conn->prepare("UPDATE events SET title=?, description=? WHERE date=? AND title=?");
$stmt->bind_param("ssss", $title, $desc, $date, $originalTitle);
$stmt->execute();
$stmt->close();
