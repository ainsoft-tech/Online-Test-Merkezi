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

// Fetch all dersler (categories) for filtering questions
try {
    $stmt_dersler = $db->query("SELECT id, ders_adi, ders_grubu FROM dersler ORDER BY ders_adi");
    $dersler = $stmt_dersler->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Dersler alınamadı: " . $e->getMessage();
    $dersler = [];
}

// Handle form submission (updating the test)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $test_name = trim($_POST['test_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $selected_questions = $_POST['selected_questions'] ?? [];

    if (empty($test_name) || empty($selected_questions)) {
        $error = "Test adı ve en az bir soru seçimi zorunludur.";
    } else {
        $db->beginTransaction();
        try {
            // Update exams table
            $stmt_exam = $db->prepare("UPDATE exams SET title = :title, description = :description WHERE id = :id");
            $stmt_exam->execute([
                ':title' => $test_name,
                ':description' => $description,
                ':id' => $exam_id
            ]);

            // Delete existing exam questions
            $stmt_del_eq = $db->prepare("DELETE FROM exam_questions WHERE exam_id = :exam_id");
            $stmt_del_eq->bindParam(':exam_id', $exam_id, PDO::PARAM_INT);
            $stmt_del_eq->execute();

            // Insert new exam questions
            $question_order = 1;
            foreach ($selected_questions as $question_id_val) {
                $stmt_eq = $db->prepare("INSERT INTO exam_questions (exam_id, question_id, question_order) VALUES (:exam_id, :question_id, :question_order)");
                $stmt_eq->execute([
                    ':exam_id' => $exam_id,
                    ':question_id' => $question_id_val,
                    ':question_order' => $question_order
                ]);
                $question_order++;
            }

            $db->commit();
            header("Location: test_yonetimi.php?success=Test başarıyla güncellendi.");
            exit;

        } catch (PDOException $e) {
            $db->rollBack();
            $error = "Veritabanı hatası: " . $e->getMessage();
        } catch (Exception $e) {
            $db->rollBack();
            $error = "Bir hata oluştu: " . $e->getMessage();
        }
    }
}

// Fetch the exam data to populate the form
try {
    $stmt_exam = $db->prepare("SELECT id, title, description FROM exams WHERE id = :id");
    $stmt_exam->bindParam(':id', $exam_id, PDO::PARAM_INT);
    $stmt_exam->execute();
    $exam = $stmt_exam->fetch(PDO::FETCH_ASSOC);

    if (!$exam) {
        header('Location: test_yonetimi.php?error=Test bulunamadı.');
        exit;
    }

    // Fetch current questions in this exam
    $stmt_current_questions = $db->prepare("SELECT question_id FROM exam_questions WHERE exam_id = :exam_id");
    $stmt_current_questions->bindParam(':exam_id', $exam_id, PDO::PARAM_INT);
    $stmt_current_questions->execute();
    $current_question_ids = array_column($stmt_current_questions->fetchAll(PDO::FETCH_ASSOC), 'question_id');

} catch (PDOException $e) {
    $error = "Veritabanı hatası: " . $e->getMessage();
    $exam = [];
    $current_question_ids = [];
}

// Fetch questions based on filters (same as test_olustur.php)
$filtered_questions = [];
$filter_ders_id = $_GET['filter_ders_id'] ?? null;
$filter_ders_grubu = $_GET['filter_ders_grubu'] ?? null;

$sql_questions = "SELECT q.id, q.image_path, d.ders_adi, d.ders_grubu FROM questions q JOIN dersler d ON q.ders_id = d.id WHERE 1=1 ";
$params = [];

if ($filter_ders_id && is_numeric($filter_ders_id)) {
    $sql_questions .= " AND q.ders_id = :ders_id";
    $params[':ders_id'] = $filter_ders_id;
}
if ($filter_ders_grubu && !empty($filter_ders_grubu)) {
    $sql_questions .= " AND d.ders_grubu = :ders_grubu";
    $params[':ders_grubu'] = $filter_ders_grubu;
}

$sql_questions .= " ORDER BY d.ders_adi, q.id DESC";

try {
    $stmt_questions = $db->prepare($sql_questions);
    $stmt_questions->execute($params);
    $filtered_questions = $stmt_questions->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Sorular alınırken bir hata oluştu: " . $e->getMessage();
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
                                <span class="card-title">Testi Düzenle: <?php echo htmlspecialchars($exam['title'] ?? ''); ?></span>
                                <?php if (isset($error)): ?>
                                    <div class="card-panel red lighten-2 white-text"><?php echo htmlspecialchars($error); ?></div>
                                <?php endif; ?>
                                <form action="test_duzenle.php?id=<?php echo $exam['id']; ?>" method="POST">
                                    <div class="input-field">
                                        <input type="text" id="test_name" name="test_name" required value="<?php echo htmlspecialchars($exam['title'] ?? ''); ?>">
                                        <label for="test_name" class="active">Test Adı</label>
                                    </div>
                                    <div class="input-field">
                                        <textarea id="description" name="description" class="materialize-textarea"><?php echo htmlspecialchars($exam['description'] ?? ''); ?></textarea>
                                        <label for="description" class="active">Açıklama</label>
                                    </div>

                                    <h6 class="mt-3">Soruları Seçin:</h6>
                                    <div class="row">
                                        <div class="input-field col s12 m6">
                                            <select id="filter_ders_id" name="filter_ders_id">
                                                <option value="" selected>Tüm Dersler</option>
                                                <?php foreach ($dersler as $ders): ?>
                                                    <option value="<?php echo $ders['id']; ?>" <?php echo ($filter_ders_id == $ders['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($ders['ders_adi']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label>Derslere Göre Filtrele</label>
                                        </div>
                                        <div class="input-field col s12 m6">
                                            <select id="filter_ders_grubu" name="filter_ders_grubu">
                                                <option value="" selected>Tüm Ders Grupları</option>
                                                <?php 
                                                $ders_gruplari = array_unique(array_column($dersler, 'ders_grubu'));
                                                sort($ders_gruplari);
                                                foreach ($ders_gruplari as $grup): 
                                                ?>
                                                    <option value="<?php echo htmlspecialchars($grup); ?>" <?php echo ($filter_ders_grubu == $grup) ? 'selected' : ''; ?>><?php echo htmlspecialchars($grup); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label>Ders Grubuna Göre Filtrele</label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col s12 right-align">
                                            <button type="submit" name="apply_filters" class="btn waves-effect waves-light blue">Filtrele</button>
                                        </div>
                                    </div>

                                    <div class="questions-list" style="max-height: 400px; overflow-y: auto; border: 1px solid #eee; padding: 10px;">
                                        <?php if (empty($filtered_questions)): ?>
                                            <p class="center-align">Filtrelere uygun soru bulunamadı.</p>
                                        <?php else: ?>
                                            <?php foreach ($filtered_questions as $question): ?>
                                                <p>
                                                    <label>
                                                        <input type="checkbox" name="selected_questions[]" value="<?php echo $question['id']; ?>" <?php echo in_array($question['id'], $current_question_ids) ? 'checked' : ''; ?> />
                                                        <span>
                                                            <img src="../uploads/questions/<?php echo htmlspecialchars($question['image_path']); ?>" alt="Soru Görseli" width="100" class="materialboxed">
                                                            (Ders: <?php echo htmlspecialchars($question['ders_adi']); ?> - Grup: <?php echo htmlspecialchars($question['ders_grubu']); ?>)
                                                        </span>
                                                    </label>
                                                </p>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>

                                    <div class="input-field right-align" style="margin-top: 20px;">
                                        <a href="test_yonetimi.php" class="btn waves-effect waves-light grey">İptal</a>
                                        <button type="submit" class="btn waves-effect waves-light teal">Güncelle</button>
                                    </div>
                                </form>
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
    var elems = document.querySelectorAll('select');
    M.FormSelect.init(elems);

    var materialboxedElems = document.querySelectorAll('.materialboxed');
    M.Materialbox.init(materialboxedElems);

    // Handle filter form submission via GET to keep filters in URL
    document.querySelector('button[name="apply_filters"]').addEventListener('click', function(e) {
        e.preventDefault();
        const dersId = document.getElementById('filter_ders_id').value;
        const dersGrubu = document.getElementById('filter_ders_grubu').value;
        let url = 'test_duzenle.php?id=<?php echo $exam_id; ?>';
        if (dersId) url += `&filter_ders_id=${dersId}`;
        if (dersGrubu) url += `&filter_ders_grubu=${dersGrubu}`;
        window.location.href = url;
    });
});
</script>
<?php require_once '../includes/footer.php'; ?>
