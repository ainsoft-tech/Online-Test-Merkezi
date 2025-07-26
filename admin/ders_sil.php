<?php
session_start();
require_once '../config/database.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?error=Yetkisiz erişim.');
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ders_yonetimi.php?error=Geçersiz ID.');
    exit;
}

$id = $_GET['id'];

try {
    $db = get_db_connection();
    $stmt = $db->prepare("DELETE FROM dersler WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        header('Location: ders_yonetimi.php?success=Ders başarıyla silindi.');
    } else {
        header('Location: ders_yonetimi.php?error=Ders bulunamadı veya silinemedi.');
    }
    exit;
} catch (PDOException $e) {
    // You might want to check for foreign key constraints here in a real app
    header('Location: ders_yonetimi.php?error=Veritabanı hatası: ' . $e->getMessage());
    exit;
}
?>