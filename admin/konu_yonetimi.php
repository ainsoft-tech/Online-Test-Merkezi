<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?error=Bu sayfaya erişim yetkiniz yok.');
    exit;
}

$db = get_db_connection();

// Dersleri çek
$stmt = $db->query("SELECT id, ders_adi as name FROM dersler ORDER BY ders_adi");
$lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);

$selected_lesson_id = isset($_GET['ders_id']) ? (int)$_GET['ders_id'] : 0;
$konular = [];

if ($selected_lesson_id) {
    $stmt = $db->prepare("SELECT id, konu_adi FROM konular WHERE ders_id = :ders_id ORDER BY konu_adi");
    $stmt->execute(['ders_id' => $selected_lesson_id]);
    $konular = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

require_once '../includes/header.php';
?>
<main>
<div class="container-fluid">
    <div class="row">
        
        <?php include_once '../includes/sidebar.php'; ?>

        <div class="col s12 m9 l10">
            <div class="container">
                <div class="row" style="margin-top: 20px;">
                    <div class="col s12">
                        <div class="card">
                            <div class="card-content">
                                <span class="card-title">Konu Yönetimi</span>
                                
                                <form method="GET" action="konu_yonetimi.php">
                                    <div class="input-field">
                                        <select name="ders_id" onchange="this.form.submit()">
                                            <option value="0" disabled selected>Bir ders seçin</option>
                                            <?php foreach ($lessons as $lesson): ?>
                                                <option value="<?php echo $lesson['id']; ?>" <?php echo ($selected_lesson_id == $lesson['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($lesson['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label>Ders Seçimi</label>
                                    </div>
                                </form>

                                <?php if ($selected_lesson_id): ?>
                                    <hr>
                                    <h5>'<?php echo htmlspecialchars(array_values(array_filter($lessons, function($l) use ($selected_lesson_id) { return $l['id'] == $selected_lesson_id; }))[0]['name']); ?>' Dersinin Konuları</h5>
                                    
                                    <form method="POST" action="konu_ekle.php">
                                        <div class="input-field">
                                            <input type="text" name="konu_adi" id="konu_adi" required>
                                            <label for="konu_adi">Yeni Konu Adı</label>
                                        </div>
                                        <input type="hidden" name="ders_id" value="<?php echo $selected_lesson_id; ?>">
                                        <button type="submit" class="btn waves-effect waves-light">Konu Ekle</button>
                                    </form>

                                    <table class="striped highlight responsive-table" style="margin-top: 20px;">
                                        <thead>
                                            <tr>
                                                <th>Konu Adı</th>
                                                <th class="center-align">İşlemler</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($konular)): ?>
                                                <tr>
                                                    <td colspan="2" class="center-align">Bu derse ait konu bulunamadı.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($konular as $konu): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($konu['konu_adi']); ?></td>
                                                        <td class="center-align">
                                                            <a href="konu_duzenle.php?id=<?php echo $konu['id']; ?>" class="btn-small waves-effect waves-light orange">Düzenle</a>
                                                            <a href="konu_sil.php?id=<?php echo $konu['id']; ?>" class="btn-small waves-effect waves-light red" onclick="return confirm('Bu konuyu silmek istediğinizden emin misiniz?');">Sil</a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
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
    });
</script>
<?php require_once '../includes/footer.php'; ?>