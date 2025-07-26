<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?error=Yetkisiz erişim.');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: test_yonetimi.php?error=Geçersiz test ID.');
    exit;
}

$exam_id = $_GET['id'];

$db = get_db_connection();
$db->beginTransaction();

try {
    // Delete exam questions first
    $stmt_eq_del = $db->prepare("DELETE FROM exam_questions WHERE exam_id = :exam_id");
    $stmt_eq_del->bindParam(':exam_id', $exam_id, PDO::PARAM_INT);
    $stmt_eq_del->execute();

    // Delete the exam itself
    $stmt_exam_del = $db->prepare("DELETE FROM exams WHERE id = :id");
    $stmt_exam_del->bindParam(':id', $exam_id, PDO::PARAM_INT);
    $stmt_exam_del->execute();

    $db->commit();
    header('Location: test_yonetimi.php?success=Test başarıyla silindi.');
    exit;

} catch (PDOException $e) {
    $db->rollBack();
    header('Location: test_yonetimi.php?error=Veritabanı hatası: ' . $e->getMessage());
    exit;
}
?>