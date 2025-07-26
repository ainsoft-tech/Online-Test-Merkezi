<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?error=Yetkisiz erişim.');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ders_yonetimi.php?error=Geçersiz ID.');
    exit;
}

$id = $_GET['id'];

try {
    $db = get_db_connection();
    $stmt = $db->prepare("SELECT * FROM dersler WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $lesson = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lesson) {
        header('Location: ders_yonetimi.php?error=Ders bulunamadı.');
        exit;
    }
} catch (PDOException $e) {
    header('Location: ders_yonetimi.php?error=Veritabanı hatası: ' . $e->getMessage());
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
                                <span class="card-title">Ders Detayları</span>
                                <ul class="collection">
                                    <li class="collection-item"><strong>ID:</strong> <?php echo htmlspecialchars($lesson['id']); ?></li>
                                    <li class="collection-item"><strong>Ders Adı:</strong> <?php echo htmlspecialchars($lesson['ders_adi']); ?></li>
                                    <li class="collection-item"><strong>Ders Grubu:</strong> <?php echo htmlspecialchars($lesson['ders_grubu']); ?></li>
                                    <li class="collection-item"><strong>Ders Kodu:</strong> <?php echo htmlspecialchars($lesson['ders_kodu']); ?></li>
                                    <li class="collection-item"><strong>Oluşturulma Tarihi:</strong> <?php echo date('d.m.Y H:i', strtotime($lesson['created_at'])); ?></li>
                                    <li class="collection-item"><strong>Son Güncelleme:</strong> <?php echo date('d.m.Y H:i', strtotime($lesson['updated_at'])); ?></li>
                                </ul>
                            </div>
                            <div class="card-action">
                                <a href="ders_yonetimi.php" class="btn waves-effect waves-light grey">Geri Dön</a>
                                <a href="ders_duzenle.php?id=<?php echo $lesson['id']; ?>" class="btn waves-effect waves-light orange">Düzenle</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</main>
<?php require_once '../includes/footer.php'; ?>
