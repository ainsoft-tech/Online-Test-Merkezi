<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?error=Yetkisiz erişim');
    exit;
}

$db = get_db_connection();
$konu_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$konu_id) {
    header('Location: konu_yonetimi.php?error=Geçersiz konu ID.');
    exit;
}

// Konu bilgilerini çek
$stmt = $db->prepare("SELECT * FROM konular WHERE id = :id");
$stmt->execute(['id' => $konu_id]);
$konu = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$konu) {
    header('Location: konu_yonetimi.php?error=Konu bulunamadı.');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $konu_adi = trim($_POST['konu_adi']);

    if (empty($konu_adi)) {
        $error = "Konu adı boş olamaz.";
    } else {
        try {
            $stmt = $db->prepare("UPDATE konular SET konu_adi = :konu_adi WHERE id = :id");
            $stmt->execute(['konu_adi' => $konu_adi, 'id' => $konu_id]);

            header('Location: konu_yonetimi.php?ders_id=' . $konu['ders_id'] . '&success=Konu başarıyla güncellendi.');
            exit;
        } catch (PDOException $e) {
            $error = "Veritabanı hatası: " . $e->getMessage();
        }
    }
}

require_once '../includes/header.php';
?>
<main>
<div class="container">
    <div class="row" style="margin-top: 20px;">
        <div class="col s12 m8 offset-m2">
            <div class="card">
                <div class="card-content">
                    <span class="card-title">Konu Düzenle</span>
                    <?php if (isset($error)): ?>
                        <div class="card-panel red lighten-2 white-text"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <form method="POST" action="konu_duzenle.php?id=<?php echo $konu_id; ?>">
                        <div class="input-field">
                            <input type="text" name="konu_adi" id="konu_adi" value="<?php echo htmlspecialchars($konu['konu_adi']); ?>" required>
                            <label for="konu_adi">Konu Adı</label>
                        </div>
                        <button type="submit" class="btn waves-effect waves-light">Güncelle</button>
                        <a href="konu_yonetimi.php?ders_id=<?php echo $konu['ders_id']; ?>" class="btn waves-effect waves-light grey">İptal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</main>
<?php require_once '../includes/footer.php'; ?>