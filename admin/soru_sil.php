<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?error=Yetkisiz erişim.');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['tip'])) {
    header('Location: soru_yonetimi.php?error=Geçersiz parametreler.');
    exit;
}

$id = $_GET['id'];
$tip = $_GET['tip'];

$db = get_db_connection();
$db->beginTransaction();

try {
    if ($tip === 'yeni') {
        // Yeni sistem (metin tabanlı) soru silme
        // 1. İlişkili seçenekleri 'secenekler' tablosundan sil
        $stmt_del_secenekler = $db->prepare("DELETE FROM secenekler WHERE soru_id = :soru_id");
        $stmt_del_secenekler->execute([':soru_id' => $id]);

        // 2. Soruyu 'sorular' tablosundan sil
        $stmt_del_soru = $db->prepare("DELETE FROM sorular WHERE id = :id");
        $stmt_del_soru->execute([':id' => $id]);

    } elseif ($tip === 'eski') {
        // Eski sistem (resim tabanlı) soru silme
        // 1. Resim dosyasının yolunu al
        $stmt_q_path = $db->prepare("SELECT image_path FROM questions WHERE id = :id");
        $stmt_q_path->execute([':id' => $id]);
        $question_image_path = $stmt_q_path->fetchColumn();

        // 2. İlişkili seçenekleri 'options' tablosundan sil (Eski sistemde bu tablo kullanılıyorsa)
        // Not: Orijinal silme kodunuzda bu vardı, güvenli olması için ekliyoruz.
        $stmt_o_del = $db->prepare("DELETE FROM options WHERE question_id = :question_id");
        $stmt_o_del->execute([':question_id' => $id]);

        // 3. Soruyu 'questions' tablosundan sil
        $stmt_q_del = $db->prepare("DELETE FROM questions WHERE id = :id");
        $stmt_q_del->execute([':id' => $id]);

        // 4. Resim dosyasını sunucudan sil
        if ($question_image_path && file_exists('../uploads/questions/' . $question_image_path)) {
            unlink('../uploads/questions/' . $question_image_path);
        }
    } else {
        throw new Exception("Geçersiz soru tipi.");
    }

    $db->commit();
    header('Location: soru_yonetimi.php?success=Soru başarıyla silindi.');
    exit;

} catch (Exception $e) {
    $db->rollBack();
    header('Location: soru_yonetimi.php?error=' . urlencode($e->getMessage()));
    exit;
}
?>
