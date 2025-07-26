<?php
require_once '../config/database.php';

$sinav_id = $_GET['id'] ?? null;

if ($sinav_id) {
    $db = get_db_connection();
    $stmt = $db->prepare("DELETE FROM sinavlar WHERE id = ?");
    $stmt->execute([$sinav_id]);
}

header("Location: sinav_yonetimi.php");
exit();
?>
