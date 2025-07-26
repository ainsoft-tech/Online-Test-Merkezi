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

try {
    $db = get_db_connection();

    // Fetch exam details
    $stmt_exam = $db->prepare("SELECT id, title, description, created_at FROM exams WHERE id = :id");
    $stmt_exam->bindParam(':id', $exam_id, PDO::PARAM_INT);
    $stmt_exam->execute();
    $exam = $stmt_exam->fetch(PDO::FETCH_ASSOC);

    if (!$exam) {
        header('Location: test_yonetimi.php?error=Test bulunamadı.');
        exit;
    }

    // Fetch questions for this exam
    $stmt_questions = $db->prepare(
        "SELECT q.id, q.image_path, d.ders_adi, d.ders_grubu, k.konu_adi, eq.question_order " .
        "FROM exam_questions eq " .
        "JOIN questions q ON eq.question_id = q.id " .
        "JOIN dersler d ON q.ders_id = d.id " .
        "LEFT JOIN konular k ON q.konu_id = k.id " .
        "WHERE eq.exam_id = :exam_id ORDER BY eq.question_order"
    );
    $stmt_questions->bindParam(':exam_id', $exam_id, PDO::PARAM_INT);
    $stmt_questions->execute();
    $exam_questions = $stmt_questions->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    header('Location: test_yonetimi.php?error=Veritabanı hatası: ' . $e->getMessage());
    exit;
}

require_once '../includes/header.php';
?>
<main>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include_once '../includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="col s12 m9 l10">
            <div class="container">
                <div class="row" style="margin-top: 20px;">
                    <div class="col s12">
                        <div class="card">
                            <div class="card-content">
                                <span class="card-title">Test Detayları: <?php echo htmlspecialchars($exam['title']); ?></span>
                                <p><strong>Açıklama:</strong> <?php echo htmlspecialchars($exam['description']); ?></p>
                                <p><strong>Oluşturulma Tarihi:</strong> <?php echo date('d.m.Y H:i', strtotime($exam['created_at'])); ?></p>
                                <h6 class="mt-3">Testteki Sorular:</h6>
                                <?php if (empty($exam_questions)): ?>
                                    <p>Bu testte henüz soru bulunmamaktadır.</p>
                                <?php else: ?>
                                    <table class="striped highlight responsive-table">
                                        <thead>
                                            <tr>
                                                <th>Sıra</th>
                                                <th>Soru Görseli</th>
                                                <th>Ders</th>
                                                <th>Konu</th>
                                                <th>Ders Grubu</th>
                                                <th>İşlemler</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($exam_questions as $question): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($question['question_order']); ?></td>
                                                    <td><img src="../uploads/questions/<?php echo htmlspecialchars($question['image_path']); ?>" alt="Soru Görseli" width="100" class="materialboxed"></td>
                                                    <td><?php echo htmlspecialchars($question['ders_adi']); ?></td>
                                                    <td><?php echo htmlspecialchars($question['konu_adi'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($question['ders_grubu']); ?></td>
                                                    <td>
                                                        <a href="soru_detay.php?id=<?php echo $question['id']; ?>" class="btn-small waves-effect waves-light blue">Soru Detayı</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </div>
                            <div class="card-action">
                                <a href="test_yonetimi.php" class="btn waves-effect waves-light grey">Geri Dön</a>
                                <a href="test_duzenle.php?id=<?php echo $exam['id']; ?>" class="btn waves-effect waves-light orange">Düzenle</a>
                                <a href="test_pdf.php?id=<?php echo $exam['id']; ?>" class="btn waves-effect waves-light red" target="_blank">PDF İndir</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</main>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var elems = document.querySelectorAll('.materialboxed');
    M.Materialbox.init(elems);
});
</script>
<?php require_once '../includes/footer.php'; ?>
