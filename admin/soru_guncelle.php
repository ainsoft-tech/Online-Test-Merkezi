<?php
session_start();
require_once '../config/database.php';
require_once '../config/exam_vt.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['soru_guncelle'])) {
    header("Location: soru_yonetimi.php?error=invalid_request");
    exit();
}

$soru_id = $_POST['soru_id'] ?? null;
$soru_metni = $_POST['soru_metni'] ?? null;
$secenekler = $_POST['secenek'] ?? [];
$dogru_cevap_key = $_POST['dogru_cevap'] ?? null;

// Basit doğrulama
if (empty($soru_id) || empty($soru_metni) || empty($secenekler) || empty($dogru_cevap_key)) {
    header("Location: soru_yonetimi.php?error=missing_data");
    exit();
}

$exam = new Exam();
$db = $exam->db;

try {
    $db->beginTransaction();

    // 1. Soru metnini ve doğru cevabı 'sorular' tablosunda güncelle
    $stmt_soru = $db->prepare(
        "UPDATE sorular SET soru_metni = :soru_metni, dogru_cevap = :dogru_cevap WHERE id = :soru_id"
    );
    $stmt_soru->execute([
        ':soru_metni' => $soru_metni,
        ':dogru_cevap' => $dogru_cevap_key,
        ':soru_id' => $soru_id
    ]);

    // 2. Mevcut seçenekleri güncelle
    $stmt_update_secenek = $db->prepare(
        "UPDATE secenekler SET secenek_metni = :secenek_metni WHERE soru_id = :soru_id AND secenek_key = :secenek_key"
    );

    foreach ($secenekler as $key => $metin) {
        $stmt_update_secenek->execute([
            ':secenek_metni' => $metin,
            ':soru_id' => $soru_id,
            ':secenek_key' => $key
        ]);
    }

    $db->commit();

    header("Location: soru_yonetimi.php?success=Soru başarıyla güncellendi.");
    exit();

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Soru güncelleme hatası: " . $e->getMessage());
    header("Location: soru_yonetimi.php?error=database_error");
    exit();
}
?>
