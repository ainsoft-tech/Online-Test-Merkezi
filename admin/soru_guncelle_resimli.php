<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?error=Yetkisiz erişim.');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: soru_yonetimi.php?error=Gecersiz istek.');
    exit;
}

$question_id = $_POST['question_id'] ?? null;
$ders_id = $_POST['ders_id'] ?? null;
$konu_id = $_POST['konu_id'] ?? null;
$option_count = $_POST['option_count'] ?? 0;
$correct_answer = $_POST['correct_answer'] ?? null;

if (empty($question_id) || empty($ders_id) || empty($konu_id) || empty($option_count) || empty($correct_answer)) {
    header("Location: soru_duzenle.php?id={$question_id}&tip=eski&error=Lütfen tüm zorunlu alanları doldurun.");
    exit;
}

$db = get_db_connection();
$db->beginTransaction();

try {
    $question_image_name = null;

    // Yeni bir resim yüklendiyse işle
    if (isset($_FILES['question_image']) && $_FILES['question_image']['error'] === UPLOAD_ERR_OK) {
        // Eski resmi sil
        $stmt_old_img = $db->prepare("SELECT image_path FROM questions WHERE id = :id");
        $stmt_old_img->execute([':id' => $question_id]);
        $old_image_path = $stmt_old_img->fetchColumn();
        if ($old_image_path && file_exists('../uploads/questions/' . $old_image_path)) {
            unlink('../uploads/questions/' . $old_image_path);
        }

        // Yeni resmi kaydet
        $target_dir_q = "../uploads/questions/";
        $question_image_name = time() . '_' . basename($_FILES["question_image"]["name"]);
        $target_file_q = $target_dir_q . $question_image_name;
        if (!move_uploaded_file($_FILES["question_image"]["tmp_name"], $target_file_q)) {
            throw new Exception("Yeni soru görseli yüklenemedi.");
        }
    }

    // 'questions' tablosunu güncelle
    $sql_update_q = "UPDATE questions SET option_count = :option_count, correct_answer = :correct_answer, ders_id = :ders_id, konu_id = :konu_id ";
    if ($question_image_name) {
        $sql_update_q .= ", image_path = :image_path ";
    }
    $sql_update_q .= "WHERE id = :id";

    $stmt_q = $db->prepare($sql_update_q);
    $stmt_q->bindParam(':option_count', $option_count, PDO::PARAM_INT);
    $stmt_q->bindParam(':correct_answer', $correct_answer, PDO::PARAM_INT);
    $stmt_q->bindParam(':ders_id', $ders_id, PDO::PARAM_INT);
    $stmt_q->bindParam(':konu_id', $konu_id, PDO::PARAM_INT);
    if ($question_image_name) {
        $stmt_q->bindParam(':image_path', $question_image_name, PDO::PARAM_STR);
    }
    $stmt_q->bindParam(':id', $question_id, PDO::PARAM_INT);
    $stmt_q->execute();

    $db->commit();
    header("Location: soru_yonetimi.php?success=Soru başarıyla güncellendi.");
    exit;

} catch (Exception $e) {
    $db->rollBack();
    header("Location: soru_duzenle.php?id={$question_id}&tip=eski&error=" . urlencode($e->getMessage()));
    exit;
}
?>
