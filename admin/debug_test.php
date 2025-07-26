<?php
echo "<h1>Debug: 'sorular' Tablosu İçeriği</h1>";
echo "<pre>";

require_once '../config/database.php';
require_once '../config/exam_vt.php';

$exam = new Exam();
$db = $exam->db;

try {
    $query = "SELECT * FROM sorular";
    $stmt = $db->query($query);
    $sorular = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($sorular)) {
        echo "UYARI: 'sorular' tablosunda hiç kayıt bulunamadı.";
    } else {
        var_dump($sorular);
    }

} catch (PDOException $e) {
    echo "HATA: " . $e->getMessage();
}

echo "</pre>";
?>
