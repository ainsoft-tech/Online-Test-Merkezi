<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?error=Yetkisiz erişim');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $konu_adi = trim($_POST['konu_adi']);
    $ders_id = (int)$_POST['ders_id'];

    if (empty($konu_adi) || empty($ders_id)) {
        header('Location: konu_yonetimi.php?ders_id=' . $ders_id . '&error=Konu adı boş olamaz.');
        exit;
    }

    try {
        $db = get_db_connection();
        $stmt = $db->prepare("INSERT INTO konular (konu_adi, ders_id) VALUES (:konu_adi, :ders_id)");
        $stmt->execute(['konu_adi' => $konu_adi, 'ders_id' => $ders_id]);

        header('Location: konu_yonetimi.php?ders_id=' . $ders_id . '&success=Konu başarıyla eklendi.');
        exit;
    } catch (PDOException $e) {
        header('Location: konu_yonetimi.php?ders_id=' . $ders_id . '&error=Veritabanı hatası: ' . $e->getMessage());
        exit;
    }
} else {
    header('Location: konu_yonetimi.php');
    exit;
}
?>