<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?error=Yetkisiz erişim');
    exit;
}

$db = get_db_connection();
$konu_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$konu_id) {
    header('Location: konu_yonetimi.php?error=Geçersiz konu ID.');
    exit;
}

// Konunun ders ID'sini al
$stmt = $db->prepare("SELECT ders_id FROM konular WHERE id = :id");
$stmt->execute(['id' => $konu_id]);
$konu = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$konu) {
    header('Location: konu_yonetimi.php?error=Konu bulunamadı.');
    exit;
}

try {
    $stmt = $db->prepare("DELETE FROM konular WHERE id = :id");
    $stmt->execute(['id' => $konu_id]);

    header('Location: konu_yonetimi.php?ders_id=' . $konu['ders_id'] . '&success=Konu başarıyla silindi.');
    exit;
} catch (PDOException $e) {
    header('Location: konu_yonetimi.php?ders_id=' . $konu['ders_id'] . '&error=Veritabanı hatası: ' . $e->getMessage());
    exit;
}
?>