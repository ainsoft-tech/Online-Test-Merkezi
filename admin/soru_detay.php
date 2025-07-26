<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?error=Yetkisiz erişim.');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: soru_yonetimi.php?error=Geçersiz soru ID.');
    exit;
}

$question_id = $_GET['id'];

try {
    $db = get_db_connection();

    // Fetch question details
    $stmt_q = $db->prepare(
        "SELECT q.id, q.image_path, q.option_count, q.correct_answer, d.ders_adi as ders_name, q.created_at, q.created_by " .
        "FROM questions q JOIN dersler d ON q.ders_id = d.id WHERE q.id = :id"
    );
    $stmt_q->bindParam(':id', $question_id, PDO::PARAM_INT);
    $stmt_q->execute();
    $question = $stmt_q->fetch(PDO::FETCH_ASSOC);

    if (!$question) {
        header('Location: soru_yonetimi.php?error=Soru bulunamadı.');
        exit;
    }

    // Fetch options for the question
    $stmt_o = $db->prepare("SELECT option_number, option_text FROM options WHERE question_id = :question_id ORDER BY option_number");
    $stmt_o->bindParam(':question_id', $question_id, PDO::PARAM_INT);
    $stmt_o->execute();
    $options = $stmt_o->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    header('Location: soru_yonetimi.php?error=Veritabanı hatası: ' . $e->getMessage());
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
                                <span class="card-title">Soru Detayları</span>
                                <div class="row">
                                    <div class="col s12 m6">
                                        <p><strong>Ders:</strong> <?php echo htmlspecialchars($question['ders_name']); ?></p>
                                        <p><strong>Seçenek Sayısı:</strong> <?php echo htmlspecialchars($question['option_count']); ?></p>
                                        <p><strong>Doğru Cevap:</strong> <?php echo htmlspecialchars($question['correct_answer']); ?></p>
                                        <p><strong>Eklenme Tarihi:</strong> <?php echo date('d.m.Y H:i', strtotime($question['created_at'])); ?></p>
                                    </div>
                                    <div class="col s12 m6 center-align">
                                        <h6>Soru Görseli</h6>
                                        <img src="../uploads/questions/<?php echo htmlspecialchars($question['image_path']); ?>" alt="Soru Görseli" class="responsive-img materialboxed" style="max-height: 300px;">
                                    </div>
                                </div>

                                <h6 class="mt-3">Seçenekler:</h6>
                                <ul class="collection">
                                    <?php foreach ($options as $option): ?>
                                        <li class="collection-item">
                                            <strong><?php echo htmlspecialchars($option['option_text']); ?>)</strong>
                                            <?php if ($option['option_number'] == $question['correct_answer']): ?>
                                                <span class="new badge green" data-badge-caption="Doğru Cevap"></span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="card-action">
                                <a href="soru_yonetimi.php" class="btn waves-effect waves-light grey">Geri Dön</a>
                                <a href="soru_duzenle.php?id=<?php echo $question['id']; ?>" class="btn waves-effect waves-light orange">Düzenle</a>
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
