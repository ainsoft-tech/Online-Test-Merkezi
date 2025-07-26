<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['ders_id'])) {
    echo json_encode([]);
    exit;
}

$ders_id = (int)$_GET['ders_id'];

try {
    $db = get_db_connection();
    $stmt = $db->prepare("SELECT id, konu_adi FROM konular WHERE ders_id = :ders_id ORDER BY konu_adi");
    $stmt->execute(['ders_id' => $ders_id]);
    $konular = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($konular);
} catch (PDOException $e) {
    // In a real app, you would log this error.
    echo json_encode([]);
}
?>