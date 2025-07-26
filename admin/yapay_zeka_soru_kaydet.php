<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';
require_once '../config/exam_vt.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['soru_kaydet'])) {
    header("Location: yapay_zeka_soru_olustur.php?error=invalid_request");
    exit();
}

$ders_id = $_POST['ders_id'] ?? null;
$konu_id = $_POST['konu_id'] ?? null;
$soru_metni = $_POST['soru_metni'] ?? null;
$secenekler = $_POST['secenek'] ?? [];
$dogru_cevap_key = $_POST['dogru_cevap'] ?? null;
$created_by = $_SESSION['user_id'];

if (empty($ders_id) || empty($konu_id) || empty($soru_metni) || empty($secenekler) || empty($dogru_cevap_key)) {
    header("Location: yapay_zeka_soru_olustur.php?error=missing_data");
    exit();
}

$exam = new Exam();
$db = $exam->db;

try {
    $db->beginTransaction();

    $stmt_soru = $db->prepare(
        "INSERT INTO sorular (ders_id, konu_id, soru_metni, dogru_cevap, created_by, created_at) 
         VALUES (:ders_id, :konu_id, :soru_metni, :dogru_cevap, :created_by, datetime('now'))"
    );
    
    $stmt_soru->execute([
        ':ders_id' => $ders_id,
        ':konu_id' => $konu_id,
        ':soru_metni' => $soru_metni,
        ':dogru_cevap' => $dogru_cevap_key,
        ':created_by' => $created_by
    ]);

    $soru_id = $db->lastInsertId();
    if (!$soru_id) {
        throw new Exception("Soru ID'si alınamadı. Soru eklenemedi.");
    }

    $stmt_secenek = $db->prepare(
        "INSERT INTO secenekler (soru_id, secenek_key, secenek_metni) 
         VALUES (:soru_id, :secenek_key, :secenek_metni)"
    );

    foreach ($secenekler as $key => $metin) {
        $stmt_secenek->execute([
            ':soru_id' => $soru_id,
            ':secenek_key' => $key,
            ':secenek_metni' => $metin
        ]);
    }

    $db->commit();
    header("Location: soru_yonetimi.php?success=Soru başarıyla kaydedildi.");
    exit();

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Soru kaydetme hatası: " . $e->getMessage());
    header("Location: yapay_zeka_soru_olustur.php?error=database_error&msg=" . urlencode($e->getMessage()));
    exit();
}
?>
